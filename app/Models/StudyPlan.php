<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudyPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'level_id',
        'name',
        'code',
        'effective_from_year',
        'effective_to_year',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'effective_from_year' => 'integer',
            'effective_to_year' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(StudyPlanSubject::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}