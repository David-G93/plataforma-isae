<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'name',
        'shift',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function studentEnrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function teachings(): HasMany
    {
        return $this->hasMany(Teaching::class);
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(
            TeacherProfile::class,
            'division_teacher',
        )->withTimestamps();
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(
            Person::class,
            'division_staff',
        )
            ->withPivot('assignment_role')
            ->withTimestamps();
    }
}
