<?php

namespace App\Http\Middleware;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Division;
use App\Models\StudyPlan;
use App\Models\Subject;
use App\Models\Teaching;
use App\Services\AcademicStructureValidationService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        if ($user) {
            $user->loadMissing('person');
        }

        $academicSummary = null;

        if ($user && $user->can('academic.view')) {
            $activeYear = AcademicYear::query()
                ->where('is_active', true)
                ->value('year');

            $qualityIssues = $activeYear
                ? app(AcademicStructureValidationService::class)
                    ->qualityControls((int) $activeYear)['summary']['total_issues']
                : 0;

            $academicSummary = [
                'academic_years' => AcademicYear::query()->count(),
                'subjects' => Subject::query()->count(),
                'study_plans' => StudyPlan::query()->count(),
                'courses' => Course::query()->count(),
                'divisions' => Division::query()->count(),
                'teachings' => Teaching::query()
                    ->where('is_active', true)
                    ->count(),
                'quality_issues' => $qualityIssues,
            ];
        }

        return [
            ...parent::share($request),

            'academicSummary' => $academicSummary,

            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_active' => $user->is_active,

                    'person' => $user->person ? [
                        'id' => $user->person->id,
                        'dni' => $user->person->dni,
                        'first_name' => $user->person->first_name,
                        'last_name' => $user->person->last_name,
                        'full_name' => $user->person->full_name,
                    ] : null,

                    'roles' => $user
                        ->getRoleNames()
                        ->values()
                        ->all(),

                    'permissions' => $user
                        ->getAllPermissions()
                        ->pluck('name')
                        ->values()
                        ->all(),
                ] : null,
            ],
        ];
    }
}