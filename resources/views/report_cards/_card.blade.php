@php
    $forPdf = $forPdf ?? false;
    $imgSrc = function ($path) use ($forPdf) {
        if (! $forPdf) {
            return asset($path);
        }

        // dompdf needs a proper file:// URI here — a raw Windows path like
        // "C:\...\logo.png" gets misread by parse_url() as scheme "C",
        // which dompdf rejects as an unsupported protocol and silently
        // swaps in its broken-image placeholder instead. Exactly two
        // slashes after "file:" — a third turns "C:/..." into "/C:/...",
        // which realpath() can't resolve on Windows.
        return 'file://'.str_replace('\\', '/', public_path($path));
    };
    $schoolClass = optional(optional($promotion)->section)->schoolClass;
    $section = optional($promotion)->section;
    $selectedSemester = $semesters->firstWhere('id', $selectedSemesterId);
    $behaviouralItems = ['Attendance', 'Skills', 'Punctuality', 'Neatness', 'Politeness', 'Self Control', 'Relationship'];
    $ratings = $reportCard->behavioural_ratings ?? [];
    $maxBarHeight = 110;
@endphp
<table class="rc-table rc-w-full" cellpadding="4" cellspacing="0">
    <tr>
        <td class="rc-header-cell" style="text-align:center;">
            <img src="{{ $imgSrc('sikhi/logo.png') }}" style="height:60px;">
        </td>
    </tr>
</table>

<table class="rc-table rc-w-full" cellpadding="4" cellspacing="0">
    <tr>
        <td class="rc-cell" style="width:90px;text-align:center;">
            @if (!empty($student->photo))
                <img src="{{ $imgSrc($student->photo) }}" style="height:80px;width:80px;object-fit:cover;">
            @else
                <div style="height:80px;width:80px;background:#e2e2ea;"></div>
            @endif
        </td>
        <td class="rc-cell">
            <div><span class="rc-label">Name:</span> <span class="rc-value">{{ strtoupper($student->last_name) }}, {{ $student->first_name }}</span></div>
            <div><span class="rc-label">Class:</span> <span class="rc-value">{{ $schoolClass->class_name ?? '—' }}@if($section), {{ $section->section_name }}@endif</span></div>
            <div><span class="rc-label">Term:</span> <span class="rc-value">{{ $selectedSemester->semester_name ?? '—' }}</span>
                &nbsp; <span class="rc-label">Session:</span> <span class="rc-value">{{ optional(\App\Models\SchoolSession::find($promotion->session_id ?? null))->session_name ?? '—' }}</span></div>
            <div>
                <span class="rc-label">Total Score:</span>
                <span class="rc-value">{{ $totalScore !== null ? number_format($totalScore, 2) : '—' }} / {{ $totalPossible ?? '—' }}</span>
                &nbsp; <span class="rc-label">Ave:</span>
                <span class="rc-value">{{ $overallAverage !== null ? $overallAverage.'%' : '—' }}</span>
                &nbsp; <span class="rc-label">Overall Grade:</span>
                <span class="rc-value">{{ $overallGrade ?? '—' }}{{ $overallRemark ? ', '.$overallRemark : '' }}</span>
            </div>
        </td>
    </tr>
    <tr>
        <td></td>
        <td class="rc-cell">
            <span class="rc-label">Highest:</span> <span class="rc-value">{{ $highestAverage ?? '—' }}</span>
            &nbsp; <span class="rc-label">Lowest:</span> <span class="rc-value">{{ $lowestAverage ?? '—' }}</span>
            &nbsp; <span class="rc-label">Class Ave Score:</span> <span class="rc-value">{{ $classAverageOverall !== null ? $classAverageOverall.'%' : '—' }}</span>
        </td>
    </tr>
    <tr>
        <td></td>
        <td class="rc-cell">
            <span class="rc-label">House:</span> <span class="rc-value">{{ $reportCard->house ?? '—' }}</span>
            &nbsp; <span class="rc-label">Weight:</span> <span class="rc-value">{{ $reportCard->weight ?? '—' }}</span>
            &nbsp; <span class="rc-label">Height:</span> <span class="rc-value">{{ $reportCard->height ?? '—' }}</span>
        </td>
    </tr>
    <tr>
        <td></td>
        <td class="rc-cell">
            <span class="rc-label">Attendance:</span> <span class="rc-value">{{ $attendanceDays ?? '—' }}</span>
            &nbsp; <span class="rc-label">Days School Opened:</span> <span class="rc-value">{{ $daysSchoolOpened ?? '—' }}</span>
            &nbsp; <span class="rc-label">School Resumes:</span>
            <span class="rc-value">{{ optional($reportCard->school_resumes_on)->format('l, F j, Y') ?? '—' }}</span>
        </td>
    </tr>
</table>

@if ($finalMarks->count() > 0)
    <table class="rc-table rc-w-full rc-subjects" cellpadding="4" cellspacing="0">
        <thead>
            <tr class="rc-thead-row">
                <th>SN</th>
                <th style="text-align:left;">Subject</th>
                @foreach ($caExamNames as $examName)
                    <th>{{ $examName }}</th>
                @endforeach
                <th>Term Score</th>
                <th>Class Ave</th>
                <th>Grade</th>
                <th>Pos</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($finalMarks as $mark)
                @php $caByName = $mark->ca_breakdown->keyBy('exam_name'); @endphp
                <tr class="{{ $loop->even ? 'rc-row-alt' : '' }}">
                    <td style="text-align:center;">{{ $mark->sn }}</td>
                    <td>{{ optional($mark->course)->course_name ?? '—' }}</td>
                    @foreach ($caExamNames as $examName)
                        <td style="text-align:center;">{{ data_get($caByName->get($examName), 'marks', '—') ?? '—' }}</td>
                    @endforeach
                    <td style="text-align:center;">{{ number_format($mark->final_marks, 2) }}</td>
                    <td style="text-align:center;">{{ $mark->class_average ?? '—' }}</td>
                    <td style="text-align:center;">{{ $mark->grade }}</td>
                    <td style="text-align:center;">{{ $mark->position ?? '—' }}</td>
                    <td style="text-align:center;">{{ $mark->remark ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="rc-chart-container">
        <div class="rc-chart-plot-wrap">
            <div class="rc-chart-plot">
                @foreach ([0, 25, 50, 75, 100] as $gridPercent)
                    <div class="rc-chart-gridline" style="bottom:{{ $gridPercent }}%;"><span class="rc-chart-axis-tick">{{ $gridPercent }}</span></div>
                @endforeach
            </div>
            <table class="rc-chart-table" cellpadding="0" cellspacing="0">
                <tr>
                    @foreach ($finalMarks as $mark)
                        @php
                            $studentHeight = max(2, (int) round(($mark->final_marks / 100) * $maxBarHeight));
                            $classHeight = max(2, (int) round((($mark->class_average ?? 0) / 100) * $maxBarHeight));
                        @endphp
                        <td class="rc-chart-group-cell">
                            <div class="rc-chart-bars">
                                <div class="rc-bar rc-bar-student" style="height:{{ $studentHeight }}px;"></div><div class="rc-bar rc-bar-class" style="height:{{ $classHeight }}px;"></div>
                            </div>
                        </td>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($finalMarks as $mark)
                        <td class="rc-chart-label-cell">{{ optional($mark->course)->course_name ?? '' }}</td>
                    @endforeach
                </tr>
            </table>
        </div>
    </div>
@else
    <div class="rc-empty-note">
        No final marks have been submitted for this student this semester yet.
    </div>
@endif

<table class="rc-table rc-w-full" cellpadding="4" cellspacing="0">
    <tr>
        <td class="rc-cell" style="width:260px;vertical-align:top;">
            <table class="rc-table rc-w-full rc-behavioural" cellpadding="2" cellspacing="0">
                <tr class="rc-thead-row">
                    <th style="text-align:left;">Behavioural</th>
                    <th>5</th><th>4</th><th>3</th><th>2</th><th>1</th>
                </tr>
                @foreach ($behaviouralItems as $item)
                    <tr class="{{ $loop->even ? 'rc-row-alt' : '' }}">
                        <td>{{ $item }}</td>
                        @for ($score = 5; $score >= 1; $score--)
                            <td style="text-align:center;">{{ (isset($ratings[$item]) && (int) $ratings[$item] === $score) ? '✓' : '' }}</td>
                        @endfor
                    </tr>
                @endforeach
            </table>
        </td>
        <td class="rc-cell" style="vertical-align:top;">
            <div class="rc-label">Class Teacher's Comment:</div>
            <div class="rc-comment">{{ $reportCard->class_teacher_comment ?? '—' }}</div>
        </td>
        <td class="rc-cell" style="width:90px;vertical-align:top;text-align:center;">
            @if (!empty($reportCard->class_teacher_signature))
                <img src="{{ $imgSrc($reportCard->class_teacher_signature) }}" style="height:35px;">
            @endif
        </td>
    </tr>
    <tr>
        <td></td>
        <td class="rc-cell" style="vertical-align:top;">
            <div class="rc-label">Principal / HM's Comment:</div>
            <div class="rc-comment">{{ $reportCard->headteacher_comment ?? '—' }}</div>
        </td>
        <td class="rc-cell" style="text-align:center;vertical-align:top;">
            @if (!empty($reportCard->headteacher_signature))
                <img src="{{ $imgSrc($reportCard->headteacher_signature) }}" style="height:35px;">
            @endif
        </td>
    </tr>
</table>

<table class="rc-table rc-w-full" cellpadding="4" cellspacing="0">
    <tr>
        <td class="rc-cell rc-legend">
            5=Excellent, 4=Very Good, 3=Good, 2=Average, 1=Fair &nbsp;&nbsp;
            <span class="rc-legend-swatch rc-legend-student"></span> = Student Scores &nbsp;&nbsp;
            <span class="rc-legend-swatch rc-legend-class"></span> = Class Average Scores
        </td>
    </tr>
</table>
