<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    /**
     * Sections are hidden from the UI; each class gets one default section
     * so that section-keyed records (promotions, attendance, marks…) still work.
     */
    public const DEFAULT_NAME = 'Main';

    protected $fillable = ['section_name', 'room_no', 'class_id', 'session_id'];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get (or lazily create) the section used for a class.
     */
    public static function defaultForClass(SchoolClass $schoolClass): self
    {
        return static::where('class_id', $schoolClass->id)->orderBy('id')->first()
            ?? static::create([
                'section_name' => self::DEFAULT_NAME,
                'room_no' => '',
                'class_id' => $schoolClass->id,
                'session_id' => $schoolClass->session_id,
            ]);
    }
}
