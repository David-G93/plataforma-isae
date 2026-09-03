<?php

namespace App\Models;

use Database\Factories\TeacherProfileFactory;
use Illuminate\Database\Eloquent\Factories\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherProfile extends Model
{
    /** @use HasFactory<TeacherProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'person_id',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}