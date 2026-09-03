<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GuardianProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(
            StudentProfile::class,
            'guardian_student',
        )
            ->withPivot([
                'relationship',
                'is_primary',
                'authorized_pickup',
                'receives_communications',
            ])
            ->withTimestamps();
    }
}