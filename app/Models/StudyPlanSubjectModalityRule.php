<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudyPlanSubjectModalityRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'study_plan_subject_id',
        'modality_id',
        'counts_for_curricular_average',
        'counts_for_institutional_average',
    ];

    protected function casts(): array
    {
        return [
            'counts_for_curricular_average' => 'boolean',
            'counts_for_institutional_average' => 'boolean',
        ];
    }

    public function studyPlanSubject(): BelongsTo
    {
        return $this->belongsTo(StudyPlanSubject::class);
    }

    public function modality(): BelongsTo
    {
        return $this->belongsTo(Modality::class);
    }
}