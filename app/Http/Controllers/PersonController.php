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
            'studentProfile.guardians.person',
            'teacherProfile',
            'guardianProfile.students.person',
            'user.roles',
        ]);

        $availableStudents = StudentProfile::query()
            ->with('person')
            ->whereHas('person')
            ->orderBy(
                Person::select('last_name')
                    ->whereColumn('people.id', 'student_profiles.person_id'),
            )
            ->get()
            ->map(fn (StudentProfile $student) => [
                'id' => $student->id,
                'person_id' => $student->person_id,
                'dni' => $student->person->dni,
                'full_name' => $student->person->full_name,
            ])
            ->values();

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

                'student_profile' => $person->studentProfile ? [
                    'id' => $person->studentProfile->id,

                    'guardians' => $person->studentProfile
                        ->guardians
                        ->map(fn (GuardianProfile $guardian) => [
                            'id' => $guardian->id,
                            'person_id' => $guardian->person_id,
                            'full_name' => $guardian->person->full_name,
                            'dni' => $guardian->person->dni,
                            'relationship' => $guardian->pivot->relationship,
                            'is_primary' => (bool) $guardian->pivot->is_primary,
                            'authorized_pickup' => (bool) $guardian->pivot->authorized_pickup,
                            'receives_communications' => (bool) $guardian->pivot->receives_communications,
                        ])
                        ->values(),
                ] : null,

                'guardian_profile' => $person->guardianProfile ? [
                    'id' => $person->guardianProfile->id,

                    'students' => $person->guardianProfile
                        ->students
                        ->map(fn (StudentProfile $student) => [
                            'id' => $student->id,
                            'person_id' => $student->person_id,
                            'full_name' => $student->person->full_name,
                            'dni' => $student->person->dni,
                            'relationship' => $student->pivot->relationship,
                            'is_primary' => (bool) $student->pivot->is_primary,
                            'authorized_pickup' => (bool) $student->pivot->authorized_pickup,
                            'receives_communications' => (bool) $student->pivot->receives_communications,
                        ])
                        ->values(),
                ] : null,

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

            'availableStudents' => $availableStudents,
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

            if (! $user && ! $data['is_active']) {
                return;
            }

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

        /*
         * Cuando los perfiles tengan información histórica importante,
         * esta eliminación deberá reemplazarse por un estado activo/inactivo.
         */
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