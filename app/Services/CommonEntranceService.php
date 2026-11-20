<?php

namespace App\Services;

use App\Models\CommonEntranceCandidate;
use App\Models\Promotion;
use App\Models\SchoolClass;

/**
 * Tracks final-year pupils through the Common Entrance (secondary school
 * entrance) exam: registration, score, secondary school placement.
 */
class CommonEntranceService
{
    /**
     * The class sitting Common Entrance: the one named like "Primary 6" /
     * "Basic 6" / "Primary Six", else the last class created.
     */
    public function finalYearClass(int $sessionId): ?SchoolClass
    {
        $classes = SchoolClass::where('session_id', $sessionId)->orderBy('id')->get();

        return $classes->first(fn (SchoolClass $schoolClass) => preg_match('/\b(6|six|vi)\b/i', $schoolClass->class_name) === 1)
            ?? $classes->last();
    }

    /**
     * @return array{class: SchoolClass, pupils: int, registered: int, scored: int, placed: int}|null
     */
    public function summary(int $sessionId, ?SchoolClass $schoolClass = null): ?array
    {
        $schoolClass ??= $this->finalYearClass($sessionId);

        if (! $schoolClass) {
            return null;
        }

        $studentIds = Promotion::where('session_id', $sessionId)->where('class_id', $schoolClass->id)->pluck('student_id');

        $candidates = CommonEntranceCandidate::where('session_id', $sessionId)->whereIn('student_id', $studentIds)->get();

        return [
            'class' => $schoolClass,
            'pupils' => $studentIds->count(),
            'registered' => $candidates->filter(fn (CommonEntranceCandidate $candidate) => $candidate->isRegistered())->count(),
            'scored' => $candidates->whereNotNull('score')->count(),
            'placed' => $candidates->filter(fn (CommonEntranceCandidate $candidate) => filled($candidate->admitted_school))->count(),
        ];
    }
}
