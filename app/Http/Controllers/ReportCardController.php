<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Exam;
use App\Models\FinalMark;
use App\Models\GradeRule;
use App\Models\GradingSystem;
use App\Models\Mark;
use App\Models\Promotion;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\Section;
use App\Models\Semester;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportCardController extends Controller
{
    protected function getCurrentSessionId()
    {
        if (session()->has('browse_session_id')) {
            return session('browse_session_id');
        }

        $latest = SchoolSession::latest()->first();

        return $latest ? $latest->id : 0;
    }

    protected function ordinal(int $number): string
    {
        if (in_array($number % 100, [11, 12, 13], true)) {
            return $number.'th';
        }

        return match ($number % 10) {
            1 => $number.'st',
            2 => $number.'nd',
            3 => $number.'rd',
            default => $number.'th',
        };
    }

    protected function authorizeAccess(User $student): void
    {
        abort_unless(
            Auth::user()->canManageAcademics() || Auth::id() == $student->id,
            403
        );
    }

    protected function buildReportCardData(Request $request, $id): array
    {
        $student = User::where('role', 'student')->findOrFail($id);

        $this->authorizeAccess($student);

        $current_school_session_id = $this->getCurrentSessionId();

        $semesters = Semester::where('session_id', $current_school_session_id)
            ->orderBy('start_date')
            ->get();

        $semester_id = $request->query('semester_id') ?: optional($semesters->last())->id;

        $promotion = Promotion::with(['section.schoolClass'])
            ->where('session_id', $current_school_session_id)
            ->where('student_id', $student->id)
            ->first();

        $finalMarks = collect();
        $overallAverage = null;
        $overallGrade = null;
        $overallRemark = null;
        $gradingSystem = null;
        $totalScore = null;
        $totalPossible = null;
        $highestAverage = null;
        $lowestAverage = null;
        $classAverageOverall = null;

        if ($semester_id && $promotion) {
            $classmateIds = Promotion::where('session_id', $current_school_session_id)
                ->where('class_id', $promotion->class_id)
                ->where('section_id', $promotion->section_id)
                ->pluck('student_id');

            $finalMarks = FinalMark::with('course')
                ->where('session_id', $current_school_session_id)
                ->where('semester_id', $semester_id)
                ->where('student_id', $student->id)
                ->get();

            $gradingSystem = GradingSystem::where('session_id', $current_school_session_id)
                ->where('semester_id', $semester_id)
                ->where('class_id', $promotion->class_id)
                ->first();

            $gradeRules = $gradingSystem
                ? GradeRule::where('grading_system_id', $gradingSystem->id)->get()
                : collect();

            $findRule = function ($marks) use ($gradeRules) {
                return $gradeRules->first(fn ($rule) => $marks >= $rule->start_at && $marks <= $rule->end_at);
            };

            $finalMarks = $finalMarks->values()->map(function ($mark, $index) use ($findRule, $classmateIds, $semester_id, $current_school_session_id, $student) {
                $rule = $findRule($mark->final_marks);
                $mark->grade = $rule->grade ?? '—';
                $mark->point = $rule->point ?? null;
                $mark->remark = $rule->remark ?? null;
                $mark->sn = $index + 1;

                $courseMates = FinalMark::where('course_id', $mark->course_id)
                    ->where('semester_id', $semester_id)
                    ->where('session_id', $current_school_session_id)
                    ->whereIn('student_id', $classmateIds)
                    ->get();

                $mark->class_average = $courseMates->count() > 0 ? round($courseMates->avg('final_marks'), 2) : null;

                $ranked = $courseMates->sortByDesc('final_marks')->values();
                $rankIndex = $ranked->search(fn ($row) => (float) $row->final_marks === (float) $mark->final_marks);
                $mark->position = $rankIndex !== false ? $this->ordinal($rankIndex + 1) : null;

                $caColumns = collect();
                $exams = Exam::where('course_id', $mark->course_id)
                    ->where('semester_id', $semester_id)
                    ->where('session_id', $current_school_session_id)
                    ->orderBy('start_date')
                    ->get();

                if ($exams->count() > 0) {
                    $marksByExam = Mark::where('student_id', $student->id)
                        ->where('course_id', $mark->course_id)
                        ->where('session_id', $current_school_session_id)
                        ->whereIn('exam_id', $exams->pluck('id'))
                        ->get()
                        ->keyBy('exam_id');

                    $caColumns = $exams->map(fn ($exam) => [
                        'exam_name' => $exam->exam_name,
                        'marks' => optional($marksByExam->get($exam->id))->marks,
                    ]);
                }

                $mark->ca_breakdown = $caColumns;

                return $mark;
            });

            $caExamNames = $finalMarks->flatMap(fn ($mark) => $mark->ca_breakdown->pluck('exam_name'))->unique()->values();

            if ($finalMarks->count() > 0) {
                $overallAverage = round($finalMarks->avg('final_marks'), 2);
                $overallRule = $findRule($overallAverage);
                $overallGrade = $overallRule->grade ?? '—';
                $overallRemark = $overallRule->remark ?? null;
                $totalScore = round($finalMarks->sum('final_marks'), 2);
                $totalPossible = $finalMarks->count() * 100;
            }

            $classAverages = FinalMark::where('semester_id', $semester_id)
                ->where('session_id', $current_school_session_id)
                ->whereIn('student_id', $classmateIds)
                ->get()
                ->groupBy('student_id')
                ->map(fn ($rows) => $rows->avg('final_marks'))
                ->filter(fn ($average) => $average !== null);

            if ($classAverages->count() > 0) {
                $highestAverage = round($classAverages->max(), 2);
                $lowestAverage = round($classAverages->min(), 2);
                $classAverageOverall = round($classAverages->avg(), 2);
            }
        }

        $reportCard = ReportCard::firstOrNew(
            [
                'student_id' => $student->id,
                'semester_id' => $semester_id,
                'session_id' => $current_school_session_id,
            ],
            [
                'class_id' => optional($promotion)->class_id,
                'section_id' => optional($promotion)->section_id,
            ]
        );

        $attendanceDays = $reportCard->attendance_days;
        $daysSchoolOpened = $reportCard->days_school_opened;

        if ($promotion && $attendanceDays === null) {
            $attendanceDays = Attendance::where('student_id', $student->id)
                ->where('class_id', $promotion->class_id)
                ->where('section_id', $promotion->section_id)
                ->where('session_id', $current_school_session_id)
                ->where('status', 'present')
                ->count();
        }

        if ($promotion && $daysSchoolOpened === null) {
            $daysSchoolOpened = Attendance::where('student_id', $student->id)
                ->where('class_id', $promotion->class_id)
                ->where('section_id', $promotion->section_id)
                ->where('session_id', $current_school_session_id)
                ->count();
        }

        return [
            'student' => $student,
            'promotion' => $promotion,
            'semesters' => $semesters,
            'selectedSemesterId' => (int) $semester_id,
            'finalMarks' => $finalMarks,
            'caExamNames' => $caExamNames ?? collect(),
            'overallAverage' => $overallAverage,
            'overallGrade' => $overallGrade,
            'overallRemark' => $overallRemark,
            'totalScore' => $totalScore,
            'totalPossible' => $totalPossible,
            'highestAverage' => $highestAverage,
            'lowestAverage' => $lowestAverage,
            'classAverageOverall' => $classAverageOverall,
            'gradingSystem' => $gradingSystem,
            'reportCard' => $reportCard,
            'attendanceDays' => $attendanceDays,
            'daysSchoolOpened' => $daysSchoolOpened,
        ];
    }

    public function show(Request $request, $id)
    {
        return view('report_cards.show', $this->buildReportCardData($request, $id));
    }

    public function download(Request $request, $id)
    {
        $data = $this->buildReportCardData($request, $id);

        $pdf = Pdf::loadView('report_cards.pdf', $data)->setPaper('a4', 'portrait');

        $fileName = str_replace(' ', '-', $data['student']->first_name.'-'.$data['student']->last_name.'-report-card.pdf');

        return $pdf->download($fileName);
    }

    public function editDetails(Request $request, $id)
    {
        $student = User::where('role', 'student')->findOrFail($id);

        abort_unless(Auth::user()->canManageAcademics(), 403);

        $data = $this->buildReportCardData($request, $id);

        return view('report_cards.edit', $data);
    }

    public function updateDetails(Request $request, $id)
    {
        $student = User::where('role', 'student')->findOrFail($id);

        abort_unless(Auth::user()->canManageAcademics(), 403);

        $current_school_session_id = $this->getCurrentSessionId();

        $semester_id = $request->input('semester_id');

        $promotion = Promotion::where('session_id', $current_school_session_id)
            ->where('student_id', $student->id)
            ->first();

        $validated = $request->validate([
            'semester_id' => 'required',
            'house' => 'nullable|string|max:255',
            'weight' => 'nullable|string|max:255',
            'height' => 'nullable|string|max:255',
            'attendance_days' => 'nullable|integer|min:0',
            'days_school_opened' => 'nullable|integer|min:0',
            'school_resumes_on' => 'nullable|date',
            'class_teacher_comment' => 'nullable|string',
            'headteacher_comment' => 'nullable|string',
            'behavioural_ratings' => 'nullable|array',
            'class_teacher_signature' => 'nullable|file|image',
            'headteacher_signature' => 'nullable|file|image',
        ]);

        $reportCard = ReportCard::firstOrNew([
            'student_id' => $student->id,
            'semester_id' => $semester_id,
            'session_id' => $current_school_session_id,
        ]);

        $reportCard->class_id = optional($promotion)->class_id;
        $reportCard->section_id = optional($promotion)->section_id;
        $reportCard->house = $validated['house'] ?? null;
        $reportCard->weight = $validated['weight'] ?? null;
        $reportCard->height = $validated['height'] ?? null;
        $reportCard->attendance_days = $validated['attendance_days'] ?? null;
        $reportCard->days_school_opened = $validated['days_school_opened'] ?? null;
        $reportCard->school_resumes_on = $validated['school_resumes_on'] ?? null;
        $reportCard->class_teacher_comment = $validated['class_teacher_comment'] ?? null;
        $reportCard->headteacher_comment = $validated['headteacher_comment'] ?? null;
        $reportCard->behavioural_ratings = $validated['behavioural_ratings'] ?? null;

        if ($request->hasFile('class_teacher_signature')) {
            $photo = $request->file('class_teacher_signature');
            $image = uniqid().'.'.$photo->getClientOriginalExtension();
            $photo->move(public_path().'/upload/signature/', $image);
            $reportCard->class_teacher_signature = 'upload/signature/'.$image;
        }

        if ($request->hasFile('headteacher_signature')) {
            $photo = $request->file('headteacher_signature');
            $image = uniqid().'.'.$photo->getClientOriginalExtension();
            $photo->move(public_path().'/upload/signature/', $image);
            $reportCard->headteacher_signature = 'upload/signature/'.$image;
        }

        $reportCard->save();

        return redirect()
            ->route('report.card.show', ['id' => $student->id, 'semester_id' => $semester_id])
            ->with('status', 'Report card details updated successfully!');
    }

    public function massSignForm(Request $request)
    {
        abort_unless(Auth::user()->canManageAcademics(), 403);

        $current_school_session_id = $this->getCurrentSessionId();

        $semesters = Semester::where('session_id', $current_school_session_id)
            ->orderBy('start_date')
            ->get();

        $classes = SchoolClass::where('session_id', $current_school_session_id)->get();

        $semester_id = $request->query('semester_id');
        $class_id = $request->query('class_id');
        $section_id = $request->query('section_id');

        $sections = $class_id
            ? Section::where('class_id', $class_id)->where('session_id', $current_school_session_id)->get()
            : collect();

        $roster = collect();

        if ($semester_id && $class_id && $section_id) {
            $roster = Promotion::with('student')
                ->where('session_id', $current_school_session_id)
                ->where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->get()
                ->filter(fn ($promotion) => $promotion->student)
                ->sortBy(fn ($promotion) => $promotion->student->last_name);

            $signedStudentIds = ReportCard::where('semester_id', $semester_id)
                ->where('session_id', $current_school_session_id)
                ->whereIn('student_id', $roster->pluck('student_id'))
                ->whereNotNull('headteacher_signature')
                ->pluck('student_id');
        }

        return view('report_cards.mass_sign', [
            'semesters' => $semesters,
            'classes' => $classes,
            'sections' => $sections,
            'roster' => $roster,
            'signedStudentIds' => $signedStudentIds ?? collect(),
            'selectedSemesterId' => $semester_id,
            'selectedClassId' => $class_id,
            'selectedSectionId' => $section_id,
        ]);
    }

    public function massSign(Request $request)
    {
        abort_unless(Auth::user()->canManageAcademics(), 403);

        $validated = $request->validate([
            'semester_id' => 'required|integer',
            'class_id' => 'required|integer',
            'section_id' => 'required|integer',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'integer',
            'headteacher_comment' => 'nullable|string',
            'headteacher_signature' => 'required|file|image',
        ]);

        $current_school_session_id = $this->getCurrentSessionId();

        $photo = $request->file('headteacher_signature');
        $image = uniqid().'.'.$photo->getClientOriginalExtension();
        $photo->move(public_path().'/upload/signature/', $image);
        $signaturePath = 'upload/signature/'.$image;

        foreach ($validated['student_ids'] as $studentId) {
            $reportCard = ReportCard::firstOrNew([
                'student_id' => $studentId,
                'semester_id' => $validated['semester_id'],
                'session_id' => $current_school_session_id,
            ]);

            $reportCard->class_id = $validated['class_id'];
            $reportCard->section_id = $validated['section_id'];
            $reportCard->headteacher_signature = $signaturePath;

            if (! empty($validated['headteacher_comment'])) {
                $reportCard->headteacher_comment = $validated['headteacher_comment'];
            }

            $reportCard->save();
        }

        return redirect()
            ->route('report.card.mass-sign.form', [
                'semester_id' => $validated['semester_id'],
                'class_id' => $validated['class_id'],
                'section_id' => $validated['section_id'],
            ])
            ->with('status', count($validated['student_ids']).' report card(s) signed successfully!');
    }
}
