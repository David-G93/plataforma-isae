<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonRequest;
use App\Http\Requests\UpdatePersonInstitutionalAccessRequest;
use App\Http\Requests\UpdatePersonRequest;
use App\Models\GuardianProfile;
use App\Models\Person;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class PersonController extends Controller
{
    public function index(Request $request): Response
    {
        $this->ensureCanManagePeople($request);

        $search = trim((string) $request->input('search', ''));

        $people = Person::query()
            ->with([
                'studentProfile',
                'teacherProfile',
                'guardianProfile',
                'user.roles',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('dni', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhereRaw(
                            "CONCAT(first_name, ' ', last_name) LIKE ?",
                            ["%{$search}%"],
                        )
                        ->orWhereRaw(
                            "CONCAT(last_name, ' ', first_name) LIKE ?",
                            ["%{$search}%"],
                        );
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Person $person) => [
                'id' => $person->id,
                'dni' => $person->dni,
                'first_name' => $person->first_name,
                'last_name' => $person->last_name,
                'full_name' => $person->full_name,
                'email' => $person->email,
                'phone' => $person->phone,

                'profiles' => [
                    'student' => $person->studentProfile !== null,
                    'teacher' => $person->teacherProfile !== null,
                    'guardian' => $person->guardianProfile !== null,
                ],

                'user' => $person->user ? [
                    'id' => $person->user->id,
                    'is_active' => $person->user->is_active,

                    'roles' => $person->user->roles
                        ->pluck('name')
                        ->values()
                        ->all(),
                ] : null,
            ]);

        return Inertia::render('People/Index', [
            'people' => $people,

            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->ensureCanManagePeople($request);

        return Inertia::render('People/Create');
    }

    public function store(StorePersonRequest $request): RedirectResponse
    {
        $person = Person::create($request->validated());

        return redirect()
            ->route('people.show', $person)
            ->with('success', 'Persona creada correctamente.');
    }

    public function show(Request $request, Person $person): Response
    {
        $this->ensureCanManagePeople($request);

        $person->load([
            'studentProfile',
            'teacherProfile',
            'guardianProfile',
            'user.roles',
        ]);

        return Inertia::render('People/Show', [
            'person' => [
                'id' => $person->id,
                'dni' => $person->dni,
                'first_name' => $person->first_name,
                'last_name' => $person->last_name,
                'full_name' => $person->full_name,
                'birth_date' => $person->birth_date?->format('Y-m-d'),
                'email' => $person->email,
                'phone' => $person->phone,
                'address' => $person->address,

                'profiles' => [
                    'student' => $person->studentProfile !== null,
                    'teacher' => $person->teacherProfile !== null,
                    'guardian' => $person->guardianProfile !== null,
                ],

                'user' => $person->user ? [
                    'id' => $person->user->id,
                    'email' => $person->user->email,
                    'is_active' => $person->user->is_active,

                    'roles' => $person->user->roles
                        ->pluck('name')
                        ->values()
                        ->all(),
                ] : null,
            ],
        ]);
    }

    public function edit(Request $request, Person $person): Response
    {
        $this->ensureCanManagePeople($request);

        return Inertia::render('People/Edit', [
            'person' => [
                'id' => $person->id,
                'dni' => $person->dni,
                'first_name' => $person->first_name,
                'last_name' => $person->last_name,
                'birth_date' => $person->birth_date?->format('Y-m-d'),
                'email' => $person->email,
                'phone' => $person->phone,
                'address' => $person->address,
            ],
        ]);
    }

    public function update(
        UpdatePersonRequest $request,
        Person $person,
    ): RedirectResponse {
        $person->update($request->validated());

        if ($person->user) {
            $person->user->update([
                'name' => $person->full_name,
            ]);
        }

        return redirect()
            ->route('people.show', $person)
            ->with('success', 'Persona actualizada correctamente.');
    }

    public function updateInstitutionalAccess(
        UpdatePersonInstitutionalAccessRequest $request,
        Person $person,
    ): RedirectResponse {
        $data = $request->validated();

        DB::transaction(function () use ($person, $data) {
            $this->syncProfile(
                StudentProfile::class,
                $person,
                $data['student'],
            );

            $this->syncProfile(
                TeacherProfile::class,
                $person,
                $data['teacher'],
            );

            $this->syncProfile(
                GuardianProfile::class,
                $person,
                $data['guardian'],
            );

            $user = $person->user;

            /*
             * Si todavía no tiene cuenta y no se solicitó acceso,
             * solamente actualizamos los perfiles institucionales.
             */
            if (! $user && ! $data['is_active']) {
                return;
            }

            /*
             * Crear cuenta si todavía no existe.
             */
            if (! $user) {
                $user = User::create([
                    'person_id' => $person->id,
                    'name' => $person->full_name,
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'is_active' => true,
                ]);
            } else {
                $update = [
                    'name' => $person->full_name,
                    'email' => $data['email'],
                    'is_active' => $data['is_active'],
                ];

                if (! empty($data['password'])) {
                    $update['password'] = Hash::make($data['password']);
                }

                $user->update($update);
            }

            /*
             * Los roles vinculados a perfiles institucionales se
             * sincronizan automáticamente.
             *
             * Otros roles (admin, gestión, director, preceptor)
             * se conservan.
             */
            $institutionalRoles = [
                'alumno',
                'docente',
                'responsable',
            ];

            $preservedRoles = $user
                ->getRoleNames()
                ->reject(
                    fn (string $role) => in_array(
                        $role,
                        $institutionalRoles,
                        true,
                    ),
                )
                ->values()
                ->all();

            $profileRoles = [];

            if ($data['student']) {
                $profileRoles[] = 'alumno';
            }

            if ($data['teacher']) {
                $profileRoles[] = 'docente';
            }

            if ($data['guardian']) {
                $profileRoles[] = 'responsable';
            }

            $user->syncRoles([
                ...$preservedRoles,
                ...$profileRoles,
            ]);
        });

        return redirect()
            ->route('people.show', $person)
            ->with(
                'success',
                'Perfiles y acceso actualizados correctamente.',
            );
    }

    private function syncProfile(
        string $profileClass,
        Person $person,
        bool $enabled,
    ): void {
        if ($enabled) {
            $profileClass::query()->firstOrCreate([
                'person_id' => $person->id,
            ]);

            return;
        }

        $profileClass::query()
            ->where('person_id', $person->id)
            ->delete();
    }

    private function ensureCanManagePeople(Request $request): void
    {
        abort_unless(
            $request->user()?->hasAnyRole([
                'admin',
                'gestion',
                'director',
            ]),
            403,
        );
    }
}