<?php

namespace App\Services;

use App\Models\Promotion;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Enrolment figures in the shape the Annual School Census asks for:
 * pupils per class by sex and by age, and teachers by sex.
 */
class SchoolCensusService
{
    /**
     * Age columns, youngest first. Ages outside the range fold into the end columns.
     *
     * @var array<int, string>
     */
    public const AGE_COLUMNS = ['Under 5', '5', '6', '7', '8', '9', '10', '11', '12', '13+', 'Unknown'];

    /**
     * @return array{as_at: Carbon, classes: Collection<int, array{class_name: string, boys: int, girls: int, total: int, ages: array<string, int>}>, totals: array{boys: int, girls: int, total: int, ages: array<string, int>}, teachers: array{male: int, female: int, total: int}}
     */
    public function report(int $sessionId, ?Carbon $asAt = null): array
    {
        $asAt ??= Carbon::today();

        $pupilsByClass = Promotion::with('student')
            ->where('session_id', $sessionId)
            ->get()
            ->filter(fn (Promotion $promotion) => $promotion->student !== null)
            ->groupBy('class_id');

        $classes = SchoolClass::where('session_id', $sessionId)
            ->orderBy('id')
            ->get()
            ->map(function (SchoolClass $schoolClass) use ($pupilsByClass, $asAt) {
                $pupils = $pupilsByClass->get($schoolClass->id, collect())->pluck('student');

                return ['class_name' => $schoolClass->class_name] + $this->tally($pupils, $asAt);
            })
            ->filter(fn (array $row) => $row['total'] > 0)
            ->values();

        $teachers = User::where('role', 'teacher')->get(['gender']);

        return [
            'as_at' => $asAt,
            'classes' => $classes,
            'totals' => $this->tally($pupilsByClass->flatten(1)->pluck('student'), $asAt),
            'teachers' => [
                'male' => $teachers->filter(fn (User $teacher) => self::isMale($teacher))->count(),
                'female' => $teachers->filter(fn (User $teacher) => self::isFemale($teacher))->count(),
                'total' => $teachers->count(),
            ],
        ];
    }

    /**
     * @param  array{classes: Collection<int, array{class_name: string, boys: int, girls: int, total: int, ages: array<string, int>}>, totals: array{boys: int, girls: int, total: int, ages: array<string, int>}, teachers: array{male: int, female: int, total: int}, as_at: Carbon}  $report
     * @return array<int, array<int, string|int>>
     */
    public function csvRows(array $report): array
    {
        $rows = [
            ['Annual School Census - enrolment as at '.$report['as_at']->format('j F Y')],
            [],
            array_merge(['Class', 'Boys', 'Girls', 'Total'], array_map(fn (string $age) => 'Age '.$age, self::AGE_COLUMNS)),
        ];

        foreach ($report['classes'] as $row) {
            $rows[] = array_merge([$row['class_name'], $row['boys'], $row['girls'], $row['total']], array_values($row['ages']));
        }

        $totals = $report['totals'];
        $rows[] = array_merge(['All classes', $totals['boys'], $totals['girls'], $totals['total']], array_values($totals['ages']));
        $rows[] = [];
        $rows[] = ['Teachers', 'Male', 'Female', 'Total'];
        $rows[] = ['All teachers', $report['teachers']['male'], $report['teachers']['female'], $report['teachers']['total']];

        return $rows;
    }

    /**
     * @param  Collection<int, User>  $pupils
     * @return array{boys: int, girls: int, total: int, ages: array<string, int>}
     */
    private function tally(Collection $pupils, Carbon $asAt): array
    {
        $ages = array_fill_keys(self::AGE_COLUMNS, 0);

        foreach ($pupils as $pupil) {
            $ages[$this->ageColumn($pupil->birthday, $asAt)]++;
        }

        return [
            'boys' => $pupils->filter(fn (User $pupil) => self::isMale($pupil))->count(),
            'girls' => $pupils->filter(fn (User $pupil) => self::isFemale($pupil))->count(),
            'total' => $pupils->count(),
            'ages' => $ages,
        ];
    }

    private function ageColumn(?string $birthday, Carbon $asAt): string
    {
        if (blank($birthday)) {
            return 'Unknown';
        }

        try {
            $age = (int) Carbon::parse($birthday)->diffInYears($asAt);
        } catch (Throwable) {
            return 'Unknown';
        }

        return match (true) {
            $age < 5 => 'Under 5',
            $age >= 13 => '13+',
            default => (string) $age,
        };
    }

    private static function isMale(User $user): bool
    {
        return strtolower((string) $user->gender) === 'male';
    }

    private static function isFemale(User $user): bool
    {
        return strtolower((string) $user->gender) === 'female';
    }
}
