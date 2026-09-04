<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Teaching extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'study_plan_subject_id',
        'division_id',
        'modality_id',
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function studyPlanSubject(): BelongsTo
    {
        return $this->belongsTo(StudyPlanSubject::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function modality(): BelongsTo
    {
        return $this->belongsTo(Modality::class);
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(
            TeacherProfile::class,
            'teaching_teacher',
        )->withTimestamps();
    }

    public function eligibleEnrollments(): Builder
    {
        return StudentEnrollment::query()
            ->where(
                'division_id',
                $this->division_id,
            )
            ->where(
                'status',
                'active',
            )
            ->when(
                $this->modality_id !== null,
                fn (Builder $query) => $query->where(
                    'modality_id',
                    $this->modality_id,
                ),
            );
    }
}