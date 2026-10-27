<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeachingAttendance extends Model
{
    use HasFactory;

    public const STATUSES = [
        'present',
        'absent',
        'late',
        'justified',
    ];

    protected $fillable = [
        'teaching_id',
        'student_enrollment_id',
        'attendance_date',
        'status',
        'notes',
        'recorded_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
        ];
    }

    public function teaching(): BelongsTo
    {
        return $this->belongsTo(Teaching::class);
    }

    public function studentEnrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
