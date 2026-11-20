<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommonEntranceCandidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'session_id',
        'registration_number',
        'score',
        'admitted_school',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function isRegistered(): bool
    {
        return filled($this->registration_number);
    }
}
