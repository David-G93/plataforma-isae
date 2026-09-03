<?php

namespace App\Models;

use Database\Factories\GuardianProfileFactory;
use Illuminate\Database\Eloquent\Factories\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuardianProfile extends Model
{
    /** @use HasFactory<GuardianProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'person_id',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}