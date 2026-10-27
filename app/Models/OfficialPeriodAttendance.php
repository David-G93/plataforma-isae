<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficialPeriodAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_enrollment_id',
        'school_period_id',
        'absences',
        'justified_absences',
        'observations',
        'recorded_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'absences' => 'decimal:2',
            'justified_absences' => 'decimal:2',
        ];
    }

    public function studentEnrollment(): BelongsTo
    {
        return $this->belongsTo(StudentEnrollment::class);
    }

    public function schoolPeriod(): BelongsTo
    {
        return $this->belongsTo(SchoolPeriod::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
