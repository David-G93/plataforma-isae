<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function teachings(): HasMany
    {
        return $this->hasMany(Teaching::class);
    }

    public function studyPlanSubjects(): HasMany
    {
        return $this->hasMany(StudyPlanSubject::class);
    }
}