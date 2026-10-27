<?php

namespace App\Services;

use App\Models\Division;
use App\Models\Teaching;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AttendanceAccessService
{
    private const PRIVILEGED_ROLES = [
        'admin',
        'gestion',
        'rector',
        'director',
        'vicedirector',
    ];

    public function canViewAll(User $user): bool
    {
        return $user->hasAnyRole(self::PRIVILEGED_ROLES);
    }

    public function teachingQueryFor(User $user): Builder
    {
        $query = Teaching::query()->where('is_active', true);

        if ($this->canViewAll($user)) {
            return $query;
        }

        $teacherProfileId = $user->person?->teacherProfile?->id;

        if ($teacherProfileId) {
            return $query->whereHas(
                'teachers',
                fn (Builder $teacherQuery) => $teacherQuery->where(
                    'teacher_profiles.id',
                    $teacherProfileId,
                ),
            );
        }

        return $query->whereRaw('1 = 0');
    }

    public function canManageTeaching(User $user, Teaching $teaching): bool
    {
        if (! $user->can('attendance.manage')) {
            return false;
        }

        if ($this->canViewAll($user)) {
            return true;
        }

        $teacherProfileId = $user->person?->teacherProfile?->id;

        return $teacherProfileId
            && $teaching->teachers()
                ->where('teacher_profiles.id', $teacherProfileId)
                ->exists();
    }

    public function officialDivisionQueryFor(User $user): Builder
    {
        $query = Division::query()->where('is_active', true);

        if ($this->canViewAll($user)) {
            return $query;
        }

        $personId = $user->person_id;

        if (! $personId) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('preceptor')) {
            return $query->whereHas(
                'staff',
                fn (Builder $staffQuery) => $staffQuery
                    ->where('people.id', $personId)
                    ->where('division_staff.assignment_role', 'preceptor'),
            );
        }

        if ($user->hasRole('docente')) {
            return $query->whereHas(
                'staff',
                fn (Builder $staffQuery) => $staffQuery
                    ->where('people.id', $personId)
                    ->where('division_staff.assignment_role', 'tutor'),
            );
        }

        return $query->whereRaw('1 = 0');
    }

    public function canManageOfficialDivision(User $user, Division $division): bool
    {
        if (! $user->can('attendance.manage')) {
            return false;
        }

        if ($this->canViewAll($user)) {
            return true;
        }

        return $this->officialDivisionQueryFor($user)
            ->whereKey($division->id)
            ->exists();
    }
}
