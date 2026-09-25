<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CbtQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'question',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'option_e',
        'correct_option',
        'image_path',
        'marks',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public const OPTION_LETTERS = ['a', 'b', 'c', 'd', 'e'];

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The option letters that actually have a value set, e.g. ['a', 'b', 'c']
     * if option_d/option_e are empty.
     */
    public function availableOptions(): array
    {
        $options = [];

        foreach (self::OPTION_LETTERS as $letter) {
            $value = $this->{"option_{$letter}"};
            if (filled($value)) {
                $options[$letter] = $value;
            }
        }

        return $options;
    }
}
