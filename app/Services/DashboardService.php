<?php

namespace App\Services;

use App\Models\AssignedTeacher;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Event;
use App\Models\FinalMark;
use App\Models\Payment;
use App\Models\Promotion;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds the action-oriented widgets on the dashboard: where we are in the
 * term, fee collection, today's registers and pupils missing school.
 */
class DashboardService
{
    /**
     * A pupil with this many absences inside the look-back window is flagged.
     */
    public const ABSENCE_THRESHOLD = 3;

    public const ABSENCE_WINDOW_DAYS = 14;

    /**
     * At-risk signals: attendance below this rate (once enough days are
     * marked) and a term average below the usual primary-school pass mark.
     */
    public const ATTENDANCE_RISK_RATE = 80;

    public const MIN_DAYS_FOR_ATTENDANCE_RATE = 5;

    public const PASS_MARK = 40;

    /**
     * The term running today, else the next one to start, else the last one to end.
     *
     * @return array{id: int, name: string, status: string, start: Carbon, end: Carbon, week: int, total_weeks: int, days_left: int}|null
     */
    public function currentTerm(int $sessionId): ?array
    {
        $today = Carbon::today();
        $terms = Semester::where('session_id', $sessionId)->get();

        $term = $terms->first(fn (Semester $semester) => Carbon::parse($semester->start_date)->lte($today) && Carbon::parse($semester->end_date)->gte($today));
        $status = 'in_progress';

        if (! $term) {
            $term = $terms->filter(fn (Semester $semester) => Carbon::parse($semester->start_date)->gt($today))->sortBy('start_date')->first();
            $status = 'upcoming';
        }

        if (! $term) {
            $term = $terms->sortByDesc('end_date')->first();
            $status = 'ended';
        }

        if (! $term) {
            return null;
        }

        $start = Carbon::parse($term->start_date)->startOfDay();
        $end = Carbon::parse($term->end_date)->startOfDay();
        $totalWeeks = max(1, (int) ceil(($start->diffInDays($end) + 1) / 7));

        return [
            'id' => $term->id,
            'name' => $term->semester_name,
            'status' => $status,
            'start' => $start,
            'end' => $end,
            'week' => $status === 'in_progress' ? min($totalWeeks, intdiv((int) $start->diffInDays($today), 7) + 1) : 0,
            'total_weeks' => $totalWeeks,
            'days_left' => $status === 'in_progress' ? (int) $today->diffInDays($end) : 0,
        ];
    }

    /**
     * Fee collection for the running term, or the whole session when no term is running.
     *
     * @param  array{status: string, start: Carbon, end: Carbon}|null  $term
     * @return array{period_label: string, collected: float, collected_today: float, payments_count: int, by_category: Collection<string, float>, pending_count: int, pending_amount: float, unpaid_tuition_count: int, unpaid_tuition: Collection<int, Promotion>, enrolled: int}
     */
    public function feeSummary(int $sessionId, ?array $term): array
    {
        $termIsRunning = $this->isRunning($term);

        $payments = $this->successfulPayments($sessionId, $term)->get(['student_id', 'category', 'amount', 'payment_date']);

        $pending = Payment::where('session_id', $sessionId)->where('status', Payment::STATUS_PENDING);

        $unpaidTuition = Promotion::with(['student', 'schoolClass'])
            ->where('session_id', $sessionId)
            ->whereNotIn('student_id', $this->tuitionPayerIds($sessionId, $term))
            ->orderBy('class_id')
            ->get()
            ->filter(fn (Promotion $promotion) => $promotion->student !== null);

        return [
            'period_label' => $termIsRunning ? $term['name'] : 'This session',
            'collected' => (float) $payments->sum('amount'),
            'collected_today' => (float) $payments->filter(fn (Payment $payment) => Carbon::parse($payment->payment_date)->isToday())->sum('amount'),
            'payments_count' => $payments->count(),
            'by_category' => $payments->groupBy('category')->map(fn (Collection $group) => (float) $group->sum('amount'))->sortDesc(),
            'pending_count' => (clone $pending)->count(),
            'pending_amount' => (float) (clone $pending)->sum('amount'),
            'unpaid_tuition_count' => $unpaidTuition->count(),
            'unpaid_tuition' => $unpaidTuition->take(6)->values(),
            'enrolled' => Promotion::where('session_id', $sessionId)->count(),
        ];
    }

    /**
     * Today's register status for every class that has pupils.
     *
     * @return array{classes: Collection<int, array{class_id: int, class_name: string, pupils: int, taken: bool, present: int, absent: int}>, pupils_marked: int, pupils_present: int, present_rate: int|null, classes_pending: int}
     */
    public function attendanceToday(int $sessionId): array
    {
        $classes = SchoolClass::where('session_id', $sessionId)->orderBy('id')->get();

        $rows = $this->classRegisterRows($sessionId, $classes)
            ->filter(fn (array $row) => $row['pupils'] > 0)
            ->values();

        $marked = $rows->sum(fn (array $row) => $row['present'] + $row['absent']);
        $present = $rows->sum('present');

        return [
            'classes' => $rows,
            'pupils_marked' => $marked,
            'pupils_present' => $present,
            'present_rate' => $marked > 0 ? (int) round($present / $marked * 100) : null,
            'classes_pending' => $rows->where('taken', false)->count(),
        ];
    }

    /**
     * Pupils absent at least ABSENCE_THRESHOLD times in the last ABSENCE_WINDOW_DAYS days.
     *
     * @return Collection<int, array{student: User, class_name: string, absences: int}>
     */
    public function frequentAbsentees(int $sessionId, ?int $classId = null, int $limit = 8): Collection
    {
        $absences = Attendance::where('session_id', $sessionId)
            ->where('status', 'off')
            ->where('created_at', '>=', Carbon::today()->subDays(self::ABSENCE_WINDOW_DAYS - 1))
            ->when($classId !== null, fn ($query) => $query->where('class_id', $classId))
            ->selectRaw('student_id, class_id, count(*) as absences')
            ->groupBy('student_id', 'class_id')
            ->havingRaw('count(*) >= ?', [self::ABSENCE_THRESHOLD])
            ->orderByDesc('absences')
            ->limit($limit)
            ->get();

        $students = User::whereIn('id', $absences->pluck('student_id'))->get()->keyBy('id');
        $classNames = SchoolClass::whereIn('id', $absences->pluck('class_id'))->pluck('class_name', 'id');

        return $absences
            ->filter(fn (Attendance $row) => $students->has($row->student_id))
            ->map(fn (Attendance $row) => [
                'student' => $students[$row->student_id],
                'class_name' => (string) ($classNames[$row->class_id] ?? ''),
                'absences' => (int) $row->absences,
            ])
            ->values();
    }

    /**
     * The classes a teacher is class teacher of, with today's register status.
     *
     * @return Collection<int, array{class_id: int, class_name: string, pupils: int, taken: bool, present: int, absent: int}>
     */
    public function teacherClasses(int $sessionId, int $teacherId): Collection
    {
        $classes = AssignedTeacher::with('schoolClass')
            ->where('session_id', $sessionId)
            ->where('teacher_id', $teacherId)
            ->get()
            ->pluck('schoolClass')
            ->filter()
            ->unique('id');

        return $this->classRegisterRows($sessionId, $classes)->values();
    }

    /**
     * @return Collection<int, Event>
     */
    public function upcomingEvents(int $sessionId, int $limit = 4): Collection
    {
        return Event::where('session_id', $sessionId)
            ->where('end', '>=', Carbon::today())
            ->orderBy('start')
            ->limit($limit)
            ->get();
    }

    /**
     * The term whose results are being processed: the running term, else the last one to start.
     */
    public function resultsTerm(int $sessionId): ?Semester
    {
        return Semester::where('session_id', $sessionId)
            ->whereDate('start_date', '<=', Carbon::today())
            ->orderByDesc('start_date')
            ->first();
    }

    /**
     * Where each class is in the end-of-term results cycle: subject scores
     * submitted, class teacher comments written, headteacher signature.
     *
     * @param  Collection<int, int>|null  $classIds  limit to these classes (a class teacher's own)
     * @return Collection<int, array{class_id: int, class_name: string, pupils: int, subjects: int, subjects_scored: int, commented: int, signed: int, complete: bool}>
     */
    public function resultProgress(int $sessionId, Semester $term, ?Collection $classIds = null): Collection
    {
        $classes = SchoolClass::where('session_id', $sessionId)
            ->when($classIds !== null, fn ($query) => $query->whereIn('id', $classIds))
            ->orderBy('id')
            ->get();

        $ids = $classes->pluck('id');

        $pupils = Promotion::where('session_id', $sessionId)->whereIn('class_id', $ids)
            ->selectRaw('class_id, count(*) as total')->groupBy('class_id')->pluck('total', 'class_id');

        $subjects = Course::where('session_id', $sessionId)->where('semester_id', $term->id)->whereIn('class_id', $ids)
            ->selectRaw('class_id, count(*) as total')->groupBy('class_id')->pluck('total', 'class_id');

        $subjectsScored = FinalMark::where('session_id', $sessionId)->where('semester_id', $term->id)->whereIn('class_id', $ids)
            ->selectRaw('class_id, count(distinct course_id) as total')->groupBy('class_id')->pluck('total', 'class_id');

        $reportCards = ReportCard::where('session_id', $sessionId)->where('semester_id', $term->id)->whereIn('class_id', $ids)
            ->selectRaw('class_id, sum(case when class_teacher_comment is not null then 1 else 0 end) as commented, sum(case when headteacher_signature is not null then 1 else 0 end) as signed')
            ->groupBy('class_id')
            ->get()
            ->keyBy('class_id');

        return $classes
            ->map(function (SchoolClass $schoolClass) use ($pupils, $subjects, $subjectsScored, $reportCards) {
                $row = [
                    'class_id' => $schoolClass->id,
                    'class_name' => $schoolClass->class_name,
                    'pupils' => (int) ($pupils[$schoolClass->id] ?? 0),
                    'subjects' => (int) ($subjects[$schoolClass->id] ?? 0),
                    'subjects_scored' => (int) ($subjectsScored[$schoolClass->id] ?? 0),
                    'commented' => (int) ($reportCards->get($schoolClass->id)->commented ?? 0),
                    'signed' => (int) ($reportCards->get($schoolClass->id)->signed ?? 0),
                ];

                $row['complete'] = $row['subjects'] > 0
                    && $row['subjects_scored'] >= $row['subjects']
                    && $row['signed'] >= $row['pupils'];

                return $row;
            })
            ->filter(fn (array $row) => $row['pupils'] > 0)
            ->values();
    }

    /**
     * Pupils showing two or more warning signs at once: poor attendance,
     * a failing average in the latest scored term, unpaid tuition.
     *
     * @param  array{status: string, start: Carbon, end: Carbon}|null  $term
     * @param  Collection<int, int>|null  $classIds
     * @return array{total: int, pupils: Collection<int, array{student: User, class_name: string, reasons: array<int, string>}>}
     */
    public function pupilsAtRisk(int $sessionId, ?array $term, bool $includeFees, ?Collection $classIds = null, int $limit = 10): array
    {
        $enrolled = Promotion::with(['student', 'schoolClass'])
            ->where('session_id', $sessionId)
            ->when($classIds !== null, fn ($query) => $query->whereIn('class_id', $classIds))
            ->get()
            ->filter(fn (Promotion $promotion) => $promotion->student !== null);

        $studentIds = $enrolled->pluck('student_id');

        $attendance = Attendance::where('session_id', $sessionId)
            ->whereIn('student_id', $studentIds)
            ->where('created_at', '>=', $this->isRunning($term) ? $term['start'] : Carbon::today()->subDays(30))
            ->selectRaw("student_id, count(*) as marked, sum(case when status = 'on' then 1 else 0 end) as present")
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        $averages = collect();
        $scoredTerm = $this->latestScoredTerm($sessionId);

        if ($scoredTerm) {
            $averages = FinalMark::where('session_id', $sessionId)
                ->where('semester_id', $scoredTerm->id)
                ->whereIn('student_id', $studentIds)
                ->selectRaw('student_id, avg(final_marks) as average')
                ->groupBy('student_id')
                ->pluck('average', 'student_id');
        }

        $tuitionPayers = $includeFees ? $this->tuitionPayerIds($sessionId, $term)->flip() : collect();

        $atRisk = $enrolled
            ->map(function (Promotion $promotion) use ($attendance, $averages, $includeFees, $tuitionPayers, $scoredTerm) {
                $reasons = [];
                $register = $attendance->get($promotion->student_id);

                if ($register && $register->marked >= self::MIN_DAYS_FOR_ATTENDANCE_RATE) {
                    $rate = (int) round($register->present / $register->marked * 100);

                    if ($rate < self::ATTENDANCE_RISK_RATE) {
                        $reasons[] = "Attendance {$rate}%";
                    }
                }

                if ($averages->has($promotion->student_id) && $averages[$promotion->student_id] < self::PASS_MARK) {
                    $reasons[] = $scoredTerm->semester_name.' average '.round($averages[$promotion->student_id]).'%';
                }

                if ($includeFees && ! $tuitionPayers->has($promotion->student_id)) {
                    $reasons[] = 'Tuition unpaid';
                }

                return [
                    'student' => $promotion->student,
                    'class_name' => (string) optional($promotion->schoolClass)->class_name,
                    'reasons' => $reasons,
                ];
            })
            ->filter(fn (array $pupil) => count($pupil['reasons']) >= 2)
            ->sortByDesc(fn (array $pupil) => count($pupil['reasons']))
            ->values();

        return [
            'total' => $atRisk->count(),
            'pupils' => $atRisk->take($limit),
        ];
    }

    /**
     * Everything a pupil (or the parent using their login) needs at a glance.
     *
     * @param  array{status: string, start: Carbon, end: Carbon}|null  $term
     * @return array{class_name: string|null, class_teacher: User|null, attendance_marked: int, attendance_present: int, attendance_rate: int|null, paid_this_session: float, pending_payments: int, recent_payments: Collection<int, Payment>, latest_result: array{semester_id: int, term_name: string, average: int, subjects: int, signed: bool}|null}
     */
    public function studentOverview(int $sessionId, User $student, ?array $term): array
    {
        $promotion = Promotion::with('schoolClass')
            ->where('session_id', $sessionId)
            ->where('student_id', $student->id)
            ->first();

        $classTeacher = $promotion
            ? optional(AssignedTeacher::with('teacher')->where('session_id', $sessionId)->where('class_id', $promotion->class_id)->first())->teacher
            : null;

        $register = Attendance::where('session_id', $sessionId)
            ->where('student_id', $student->id)
            ->when($this->isRunning($term), fn ($query) => $query->where('created_at', '>=', $term['start']))
            ->selectRaw("count(*) as marked, sum(case when status = 'on' then 1 else 0 end) as present")
            ->first();

        $marked = (int) ($register->marked ?? 0);
        $present = (int) ($register->present ?? 0);

        $payments = Payment::where('session_id', $sessionId)->where('student_id', $student->id);

        return [
            'class_name' => optional(optional($promotion)->schoolClass)->class_name,
            'class_teacher' => $classTeacher,
            'attendance_marked' => $marked,
            'attendance_present' => $present,
            'attendance_rate' => $marked > 0 ? (int) round($present / $marked * 100) : null,
            'paid_this_session' => (float) (clone $payments)->where('status', Payment::STATUS_SUCCESS)->sum('amount'),
            'pending_payments' => (clone $payments)->where('status', Payment::STATUS_PENDING)->count(),
            'recent_payments' => (clone $payments)->where('status', Payment::STATUS_SUCCESS)->orderByDesc('payment_date')->orderByDesc('id')->limit(3)->get(),
            'latest_result' => $this->latestResult($sessionId, $student),
        ];
    }

    /**
     * @return array{semester_id: int, term_name: string, average: int, subjects: int, signed: bool}|null
     */
    private function latestResult(int $sessionId, User $student): ?array
    {
        $scoredTermIds = FinalMark::where('session_id', $sessionId)->where('student_id', $student->id)->distinct()->pluck('semester_id');

        $semester = Semester::whereIn('id', $scoredTermIds)->orderByDesc('start_date')->first();

        if (! $semester) {
            return null;
        }

        $marks = FinalMark::where('session_id', $sessionId)->where('student_id', $student->id)->where('semester_id', $semester->id)->pluck('final_marks');

        return [
            'semester_id' => $semester->id,
            'term_name' => $semester->semester_name,
            'average' => (int) round($marks->avg()),
            'subjects' => $marks->count(),
            'signed' => ReportCard::where('session_id', $sessionId)->where('semester_id', $semester->id)->where('student_id', $student->id)->whereNotNull('headteacher_signature')->exists(),
        ];
    }

    private function latestScoredTerm(int $sessionId): ?Semester
    {
        $scoredTermIds = FinalMark::where('session_id', $sessionId)->distinct()->pluck('semester_id');

        return Semester::whereIn('id', $scoredTermIds)->orderByDesc('start_date')->first();
    }

    /**
     * @param  array{status: string}|null  $term
     */
    private function isRunning(?array $term): bool
    {
        return $term !== null && $term['status'] === 'in_progress';
    }

    /**
     * Successful payments in the running term, or the whole session when no term is running.
     *
     * @param  array{status: string, start: Carbon, end: Carbon}|null  $term
     * @return Builder<Payment>
     */
    private function successfulPayments(int $sessionId, ?array $term): Builder
    {
        return Payment::where('session_id', $sessionId)
            ->where('status', Payment::STATUS_SUCCESS)
            ->when($this->isRunning($term), fn ($query) => $query->whereBetween('payment_date', [$term['start']->toDateString(), $term['end']->toDateString()]));
    }

    /**
     * Pupils with a successful tuition payment in the running term (or session).
     *
     * @param  array{status: string, start: Carbon, end: Carbon}|null  $term
     * @return Collection<int, int>
     */
    public function tuitionPayerIds(int $sessionId, ?array $term): Collection
    {
        return $this->successfulPayments($sessionId, $term)->where('category', 'Tuition')->distinct()->pluck('student_id');
    }

    /**
     * @param  Collection<int, SchoolClass>  $classes
     * @return Collection<int, array{class_id: int, class_name: string, pupils: int, taken: bool, present: int, absent: int}>
     */
    private function classRegisterRows(int $sessionId, Collection $classes): Collection
    {
        $classIds = $classes->pluck('id');

        $pupilsPerClass = Promotion::where('session_id', $sessionId)
            ->whereIn('class_id', $classIds)
            ->selectRaw('class_id, count(*) as pupils')
            ->groupBy('class_id')
            ->pluck('pupils', 'class_id');

        $todaysRegisters = Attendance::where('session_id', $sessionId)
            ->whereIn('class_id', $classIds)
            ->whereDate('created_at', Carbon::today())
            ->selectRaw("class_id, sum(case when status = 'on' then 1 else 0 end) as present, sum(case when status = 'on' then 0 else 1 end) as absent")
            ->groupBy('class_id')
            ->get()
            ->keyBy('class_id');

        return $classes->map(function (SchoolClass $schoolClass) use ($pupilsPerClass, $todaysRegisters) {
            $register = $todaysRegisters->get($schoolClass->id);

            return [
                'class_id' => $schoolClass->id,
                'class_name' => $schoolClass->class_name,
                'pupils' => (int) ($pupilsPerClass[$schoolClass->id] ?? 0),
                'taken' => $register !== null,
                'present' => (int) ($register->present ?? 0),
                'absent' => (int) ($register->absent ?? 0),
            ];
        });
    }
}
