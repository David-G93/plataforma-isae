<?php

namespace App\Services;

use App\Models\Division;
use App\Models\Teaching;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GenerateTeachingsFromStudyPlan
{
    public function handle(Division $division): Collection
    {
        $division->load([
            'course.grade',
            'course.studyPlan.subjects',
        ]);

        $course = $division->course;
        $studyPlan = $course->studyPlan;

        if (! $studyPlan) {
            throw new RuntimeException(
                'El curso no tiene un plan de estudio asignado.',
            );
        }

        if (! $studyPlan->is_active) {
            throw new RuntimeException(
                'El plan de estudio asignado está inactivo.',
            );
        }

        if ($studyPlan->level_id !== $course->grade->level_id) {
            throw new RuntimeException(
                'El plan de estudio no corresponde al nivel del curso.',
            );
        }

        $planSubjects = $studyPlan
            ->subjects
            ->where('grade_id', $course->grade_id)
            ->where('is_active', true)
            ->sortBy('order')
            ->values();

        return DB::transaction(function () use (
            $division,
            $planSubjects,
        ) {
            return $planSubjects
                ->map(function ($planSubject) use ($division) {
                    return Teaching::query()->updateOrCreate(
                        [
                            'division_id' => $division->id,
                            'study_plan_subject_id' => $planSubject->id,
                        ],
                        [
                            'subject_id' => $planSubject->subject_id,
                            'modality_id' => $planSubject->modality_id,
                            'is_active' => true,
                        ],
                    );
                })
                ->values();
        });
    }
}