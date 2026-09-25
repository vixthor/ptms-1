<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'point',
        'grade',
        'remark',
        'start_at',
        'end_at',
        'grading_system_id',
        'session_id',
    ];

    public function gradingSystem()
    {
        return $this->belongsTo(GradingSystem::class, 'grading_system_id');
    }
}
