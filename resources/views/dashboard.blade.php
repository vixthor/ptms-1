@extends('layouts.app')

@section('content')
@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
@endphp
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-4">
                <div class="col ps-4">
                    <div class="mb-3">
                        <h4 class="mb-1">{{ $greeting }}, {{ Auth::user()->first_name }}</h4>
                        <p class="text-secondary mb-0" style="font-size:.875rem;">{{ now()->format('l, j F Y') }}</p>
                    </div>

                    {{-- Term timeline --}}
                    <div class="card mb-3">
                        <div class="card-body">
                            @if($term)
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="dash-icon" style="background:var(--brand-50);color:var(--brand-600);"><i class="bi bi-calendar3-week"></i></span>
                                        <div>
                                            <div class="dash-label">Current term</div>
                                            <div class="fw-semibold" style="color:var(--ink-900);">
                                                {{ $term['name'] }}
                                                @if($term['status'] === 'in_progress')
                                                    &middot; Week {{ $term['week'] }} of {{ $term['total_weeks'] }}
                                                @elseif($term['status'] === 'upcoming')
                                                    &middot; starts {{ $term['start']->format('j M Y') }}
                                                @else
                                                    &middot; ended {{ $term['end']->format('j M Y') }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="dash-meta text-md-end">
                                        {{ $term['start']->format('j M') }} &ndash; {{ $term['end']->format('j M Y') }}
                                        @if($term['status'] === 'in_progress')
                                            <br><strong style="color:var(--ink-700);">{{ $term['days_left'] }} {{ Str::plural('day', $term['days_left']) }} left</strong>
                                        @endif
                                    </div>
                                </div>
                                @if($term['status'] === 'in_progress')
                                    <div class="dash-bar" role="progressbar" aria-label="Term progress" aria-valuenow="{{ $term['week'] }}" aria-valuemin="0" aria-valuemax="{{ $term['total_weeks'] }}">
                                        <span style="width: {{ round($term['week'] / $term['total_weeks'] * 100) }}%"></span>
                                    </div>
                                @endif
                            @else
                                <div class="d-flex align-items-center gap-3">
                                    <span class="dash-icon" style="background:var(--warning-100);color:var(--warning-500);"><i class="bi bi-calendar-x"></i></span>
                                    <div>
                                        <div class="fw-semibold" style="color:var(--ink-900);">No terms set up for this session</div>
                                        <div class="dash-meta">
                                            Add term dates
                                            @if(Auth::user()->canManageAcademics())
                                                in <a href="{{ route('academic.setting') }}">Academic settings</a>
                                            @endif
                                            to track the term week by week.
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($showsSchoolOverview)
                        {{-- Headline numbers --}}
                        <div class="row g-3 dashboard">
                            <div class="col-6 col-xl-3">
                                <div class="card h-100">
                                    <div class="card-body d-flex align-items-center gap-3">
                                        <span class="dash-icon" style="background:var(--brand-50);color:var(--brand-600);"><i class="bi bi-person-lines-fill"></i></span>
                                        <div>
                                            <div class="dash-label">Pupils</div>
                                            <div class="dash-value">{{ $studentCount }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-xl-3">
                                <div class="card h-100">
                                    <div class="card-body d-flex align-items-center gap-3">
                                        <span class="dash-icon" style="background:var(--accent-100);color:var(--accent-500);"><i class="bi bi-person-workspace"></i></span>
                                        <div>
                                            <div class="dash-label">Teachers</div>
                                            <div class="dash-value">{{ $teacherCount }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-xl-3">
                                <div class="card h-100">
                                    <div class="card-body d-flex align-items-center gap-3">
                                        <span class="dash-icon" style="background:var(--info-100);color:var(--info-500);"><i class="bi bi-diagram-3"></i></span>
                                        <div>
                                            <div class="dash-label">Classes</div>
                                            <div class="dash-value">{{ $classCount }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-xl-3">
                                <div class="card h-100">
                                    <div class="card-body d-flex align-items-center gap-3">
                                        <span class="dash-icon" style="background:var(--success-100);color:var(--success-500);"><i class="bi bi-check2-circle"></i></span>
                                        <div>
                                            <div class="dash-label">Present today</div>
                                            <div class="dash-value">{{ $attendance['present_rate'] === null ? '—' : $attendance['present_rate'].'%' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-0">
                            {{-- Today's registers --}}
                            <div class="col-lg-7">
                                <div class="card h-100">
                                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-clipboard-check me-2" style="color:var(--brand-500);"></i> Attendance today</span>
                                        @if($attendance['classes']->isNotEmpty())
                                            @if($attendance['classes_pending'] > 0)
                                                <span class="badge" style="background:var(--warning-100);color:var(--warning-500);">{{ $attendance['classes_pending'] }} {{ Str::plural('register', $attendance['classes_pending']) }} not taken</span>
                                            @else
                                                <span class="badge" style="background:var(--success-100);color:var(--success-500);">All registers taken</span>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="card-body p-0">
                                        @if($attendance['classes']->isEmpty())
                                            <div class="p-4 text-center text-secondary">
                                                <i class="bi bi-clipboard d-block mb-2" style="font-size:1.5rem;"></i>
                                                No classes with pupils in this session yet
                                            </div>
                                        @else
                                            <ul class="dash-list">
                                                @foreach($attendance['classes'] as $class)
                                                    <li>
                                                        <div>
                                                            <div class="fw-semibold" style="color:var(--ink-900);">{{ $class['class_name'] }}</div>
                                                            <div class="dash-meta">{{ $class['pupils'] }} {{ Str::plural('pupil', $class['pupils']) }}</div>
                                                        </div>
                                                        @if($class['taken'])
                                                            <a href="{{ route('attendance.list.show', ['class_id' => $class['class_id']]) }}" class="text-decoration-none text-end">
                                                                <span class="badge" style="background:var(--success-100);color:var(--success-500);">{{ $class['present'] }} present</span>
                                                                @if($class['absent'] > 0)
                                                                    <span class="badge" style="background:var(--danger-100);color:var(--danger-500);">{{ $class['absent'] }} absent</span>
                                                                @endif
                                                            </a>
                                                        @else
                                                            <span class="badge" style="background:var(--ink-100);color:var(--ink-500);">Not taken</span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Pupils missing school --}}
                            <div class="col-lg-5">
                                @include('dashboard-absentees', ['absentees' => $absentees])
                            </div>
                        </div>
                    @endif

                    @if($showsFees)
                        {{-- Fee collection --}}
                        <div class="card mt-3">
                            <div class="card-header bg-transparent d-flex flex-wrap gap-2 justify-content-between align-items-center">
                                <span><i class="bi bi-cash-coin me-2" style="color:var(--brand-500);"></i> Fees &middot; {{ $fees['period_label'] }}</span>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('payments.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Record payment</a>
                                    <a href="{{ route('payments.list') }}" class="btn btn-sm btn-outline-primary">All payments</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-6 col-md-3">
                                        <div class="dash-label">Collected</div>
                                        <div class="dash-value">₦{{ number_format($fees['collected'], 2) }}</div>
                                        <div class="dash-meta">{{ $fees['payments_count'] }} {{ Str::plural('payment', $fees['payments_count']) }}</div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="dash-label">Collected today</div>
                                        <div class="dash-value">₦{{ number_format($fees['collected_today'], 2) }}</div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="dash-label">Tuition not paid</div>
                                        <div class="dash-value" style="{{ $fees['unpaid_tuition_count'] > 0 ? 'color:var(--danger-500);' : '' }}">{{ $fees['unpaid_tuition_count'] }}</div>
                                        <div class="dash-meta">of {{ $fees['enrolled'] }} {{ Str::plural('pupil', $fees['enrolled']) }}</div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="dash-label">Pending online</div>
                                        <div class="dash-value">{{ $fees['pending_count'] }}</div>
                                        <div class="dash-meta">₦{{ number_format($fees['pending_amount'], 2) }} awaiting confirmation</div>
                                    </div>
                                </div>

                                <div class="row g-4 mt-1">
                                    <div class="col-md-6">
                                        <div class="fw-semibold mb-2" style="font-size:.875rem;">By category</div>
                                        @forelse($fees['by_category'] as $category => $amount)
                                            <div class="mb-2">
                                                <div class="d-flex justify-content-between dash-meta mb-1">
                                                    <span>{{ $category }}</span>
                                                    <span class="fw-semibold" style="color:var(--ink-700);">₦{{ number_format($amount, 2) }}</span>
                                                </div>
                                                <div class="dash-bar"><span style="width: {{ $fees['collected'] > 0 ? round($amount / $fees['collected'] * 100) : 0 }}%"></span></div>
                                            </div>
                                        @empty
                                            <div class="dash-meta">No payments recorded yet.</div>
                                        @endforelse
                                    </div>
                                    <div class="col-md-6">
                                        <div class="fw-semibold mb-2" style="font-size:.875rem;">Pupils yet to pay tuition</div>
                                        @if($fees['unpaid_tuition']->isEmpty())
                                            <div class="dash-meta"><i class="bi bi-check2-circle me-1" style="color:var(--success-500);"></i> Every pupil has paid tuition.</div>
                                        @else
                                            <ul class="dash-list border rounded-3">
                                                @foreach($fees['unpaid_tuition'] as $promotion)
                                                    <li>
                                                        <a href="{{ route('student.profile', $promotion->student_id) }}" class="text-decoration-none">{{ $promotion->student->first_name }} {{ $promotion->student->last_name }}</a>
                                                        <span class="dash-meta">{{ optional($promotion->schoolClass)->class_name }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                            @if($fees['unpaid_tuition_count'] > $fees['unpaid_tuition']->count())
                                                <div class="dash-meta mt-2">and {{ $fees['unpaid_tuition_count'] - $fees['unpaid_tuition']->count() }} more</div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(Auth::user()->role === 'teacher')
                        {{-- My class today --}}
                        <div class="row g-3">
                            <div class="col-lg-7">
                                <div class="card h-100">
                                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                                        <span><i class="bi bi-people me-2" style="color:var(--brand-500);"></i> My class today</span>
                                        @if($teacherClasses->isNotEmpty())
                                            <a href="{{ route('parent-messages.create', ['audience' => 'class', 'class_id' => $teacherClasses->first()['class_id']]) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-chat-dots"></i> Message parents</a>
                                        @endif
                                    </div>
                                    <div class="card-body p-0">
                                        @if($teacherClasses->isEmpty())
                                            <div class="p-4 text-center text-secondary">
                                                <i class="bi bi-people d-block mb-2" style="font-size:1.5rem;"></i>
                                                You are not class teacher of any class this session
                                            </div>
                                        @else
                                            <ul class="dash-list">
                                                @foreach($teacherClasses as $class)
                                                    <li class="flex-wrap">
                                                        <div>
                                                            <div class="fw-semibold" style="color:var(--ink-900);">{{ $class['class_name'] }}</div>
                                                            <div class="dash-meta">
                                                                {{ $class['pupils'] }} {{ Str::plural('pupil', $class['pupils']) }}
                                                                @if($class['taken'])
                                                                    &middot; {{ $class['present'] }} present, {{ $class['absent'] }} absent
                                                                @endif
                                                            </div>
                                                        </div>
                                                        @if($class['taken'])
                                                            <a href="{{ route('attendance.create.show', ['class_id' => $class['class_id']]) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Update register</a>
                                                        @else
                                                            <a href="{{ route('attendance.create.show', ['class_id' => $class['class_id']]) }}" class="btn btn-sm btn-primary"><i class="bi bi-clipboard-check"></i> Take today's attendance</a>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                @include('dashboard-absentees', ['absentees' => $absentees])
                            </div>
                        </div>
                    @endif

                    @if($showsSchoolOverview)
                        {{-- Shortcuts --}}
                        <div class="row g-3 mt-0">
                            <div class="col-md-4">
                                <a href="{{ route('parent-messages.create') }}" class="card h-100 text-decoration-none">
                                    <div class="card-body d-flex align-items-center gap-3">
                                        <span class="dash-icon" style="background:var(--success-100);color:var(--success-500);"><i class="bi bi-chat-dots"></i></span>
                                        <div>
                                            <div class="fw-semibold" style="color:var(--ink-900);">Message parents</div>
                                            <div class="dash-meta">SMS or WhatsApp a class or the whole school</div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="{{ route('reports.census') }}" class="card h-100 text-decoration-none">
                                    <div class="card-body d-flex align-items-center gap-3">
                                        <span class="dash-icon" style="background:var(--info-100);color:var(--info-500);"><i class="bi bi-clipboard-data"></i></span>
                                        <div>
                                            <div class="fw-semibold" style="color:var(--ink-900);">School census</div>
                                            <div class="dash-meta">Enrolment by class, sex and age</div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            @if($commonEntrance)
                                <div class="col-md-4">
                                    <a href="{{ route('common-entrance.index') }}" class="card h-100 text-decoration-none">
                                        <div class="card-body d-flex align-items-center gap-3">
                                            <span class="dash-icon" style="background:var(--brand-50);color:var(--brand-600);"><i class="bi bi-mortarboard"></i></span>
                                            <div>
                                                <div class="fw-semibold" style="color:var(--ink-900);">Common Entrance &middot; {{ $commonEntrance['class']->class_name }}</div>
                                                <div class="dash-meta">{{ $commonEntrance['registered'] }} of {{ $commonEntrance['pupils'] }} registered &middot; {{ $commonEntrance['placed'] }} placed</div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($atRisk !== null || $resultProgress->isNotEmpty())
                        <div class="row g-3 mt-0">
                            @if($atRisk !== null)
                                <div class="col-lg-6">
                                    @include('dashboard-at-risk', ['atRisk' => $atRisk, 'includesFees' => $showsFees && Auth::user()->role !== 'teacher'])
                                </div>
                            @endif
                            @if($resultsTerm && ($resultProgress->isNotEmpty() || Auth::user()->canManageAcademics()))
                                <div class="col-lg-6">
                                    @include('dashboard-results', ['resultsTerm' => $resultsTerm, 'resultProgress' => $resultProgress])
                                </div>
                            @endif
                        </div>
                    @endif

                    @if(Auth::user()->role === 'student')
                        {{-- Pupil / parent view --}}
                        <div class="row g-3 dashboard">
                            <div class="col-6 col-xl-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="dash-label">My class</div>
                                        <div class="dash-value">{{ $student['class_name'] ?? '—' }}</div>
                                        @if($student['class_teacher'])
                                            <div class="dash-meta mt-1">
                                                Class teacher: {{ $student['class_teacher']->first_name }} {{ $student['class_teacher']->last_name }}
                                                @if($student['class_teacher']->phone)
                                                    <br><a href="tel:{{ $student['class_teacher']->phone }}" class="text-decoration-none"><i class="bi bi-telephone"></i> {{ $student['class_teacher']->phone }}</a>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-xl-3">
                                <a href="{{ route('student.attendance', Auth::id()) }}" class="text-decoration-none">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="dash-label">Attendance{{ $term && $term['status'] === 'in_progress' ? ' this term' : '' }}</div>
                                            <div class="dash-value">{{ $student['attendance_rate'] === null ? '—' : $student['attendance_rate'].'%' }}</div>
                                            <div class="dash-meta mt-1">Present {{ $student['attendance_present'] }} of {{ $student['attendance_marked'] }} {{ Str::plural('day', $student['attendance_marked']) }}</div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-6 col-xl-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="dash-label">Fees paid this session</div>
                                        <div class="dash-value">₦{{ number_format($student['paid_this_session'], 2) }}</div>
                                        @if($student['pending_payments'] > 0)
                                            <div class="dash-meta mt-1">{{ $student['pending_payments'] }} {{ Str::plural('payment', $student['pending_payments']) }} awaiting confirmation</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-xl-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="dash-label">Latest result</div>
                                        @if($student['latest_result'])
                                            <div class="dash-value">{{ $student['latest_result']['average'] }}%</div>
                                            <div class="dash-meta mt-1">{{ $student['latest_result']['term_name'] }} average &middot; {{ $student['latest_result']['subjects'] }} {{ Str::plural('subject', $student['latest_result']['subjects']) }}</div>
                                        @else
                                            <div class="dash-value">—</div>
                                            <div class="dash-meta mt-1">No results yet</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-0">
                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div class="card-header bg-transparent d-flex flex-wrap gap-2 justify-content-between align-items-center">
                                        <span><i class="bi bi-cash-coin me-2" style="color:var(--brand-500);"></i> Fees</span>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('student.payments.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-credit-card"></i> Pay fees</a>
                                            <a href="{{ route('student.payments.list') }}" class="btn btn-sm btn-outline-primary">History</a>
                                        </div>
                                    </div>
                                    <div class="card-body p-0">
                                        @if($student['recent_payments']->isEmpty())
                                            <div class="p-4 text-center text-secondary">No payments this session yet</div>
                                        @else
                                            <ul class="dash-list">
                                                @foreach($student['recent_payments'] as $payment)
                                                    <li>
                                                        <div>
                                                            <div class="fw-semibold" style="color:var(--ink-900);">{{ $payment->paid_for }}</div>
                                                            <div class="dash-meta">{{ $payment->category }} &middot; {{ \Illuminate\Support\Carbon::parse($payment->payment_date)->format('j M Y') }}</div>
                                                        </div>
                                                        <span class="fw-semibold">₦{{ number_format($payment->amount, 2) }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div class="card-header bg-transparent"><i class="bi bi-journal-text me-2" style="color:var(--brand-500);"></i> Report card</div>
                                    <div class="card-body">
                                        @if($student['latest_result'])
                                            <div class="fw-semibold" style="color:var(--ink-900);">{{ $student['latest_result']['term_name'] }} report card</div>
                                            <div class="dash-meta mb-3">
                                                @if($student['latest_result']['signed'])
                                                    <i class="bi bi-patch-check-fill" style="color:var(--success-500);"></i> Signed by the headteacher
                                                @else
                                                    <i class="bi bi-hourglass-split"></i> Awaiting headteacher's signature
                                                @endif
                                            </div>
                                            <div class="d-flex flex-wrap gap-2">
                                                <a href="{{ route('report.card.show', ['id' => Auth::id(), 'semester_id' => $student['latest_result']['semester_id']]) }}" class="btn btn-sm btn-primary"><i class="bi bi-eye"></i> View</a>
                                                <a href="{{ route('report.card.download', ['id' => Auth::id(), 'semester_id' => $student['latest_result']['semester_id']]) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i> Download</a>
                                            </div>
                                        @else
                                            <div class="text-secondary">Your report card will appear here once results are out.</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($showsSchoolOverview && $studentCount > 0)
                        @php
                            $malePercentage = (int) round($maleStudentsBySession / $studentCount * 100);
                            $femalePercentage = 100 - $malePercentage;
                        @endphp
                        <div class="card mt-3">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="fw-semibold" style="font-size:.875rem;">Pupil gender split</span>
                                    <div class="d-flex align-items-center gap-3" style="font-size:.8125rem;">
                                        <span class="d-flex align-items-center gap-1 text-secondary"><span class="rounded-circle d-inline-block" style="width:8px;height:8px;background:#0678c8;"></span>Boys</span>
                                        <span class="d-flex align-items-center gap-1 text-secondary"><span class="rounded-circle d-inline-block" style="width:8px;height:8px;background:#49a4fe;"></span>Girls</span>
                                    </div>
                                </div>
                                <div class="progress" style="height:10px;border-radius:999px;overflow:hidden;">
                                    <div class="progress-bar" role="progressbar" style="background-color:#0678c8;width:{{ $malePercentage }}%" aria-valuenow="{{ $malePercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    <div class="progress-bar" role="progressbar" style="background-color:#49a4fe;width:{{ $femalePercentage }}%" aria-valuenow="{{ $femalePercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-1 text-secondary" style="font-size:.75rem;">
                                    <span>{{ $malePercentage }}% Boys</span>
                                    <span>{{ $femalePercentage }}% Girls</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="row g-3 mt-0 mb-3">
                        {{-- Upcoming events --}}
                        <div class="col-lg-5">
                            <div class="card h-100">
                                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-calendar-event me-2" style="color:var(--brand-500);"></i> Coming up</span>
                                    <a href="{{ route('events.list') }}" class="dash-meta text-decoration-none">Calendar</a>
                                </div>
                                <div class="card-body p-0">
                                    @if($upcomingEvents->isEmpty())
                                        <div class="p-4 text-center text-secondary">
                                            <i class="bi bi-calendar d-block mb-2" style="font-size:1.5rem;"></i>
                                            No upcoming events
                                        </div>
                                    @else
                                        <ul class="dash-list">
                                            @foreach($upcomingEvents as $event)
                                                @php $eventStart = \Illuminate\Support\Carbon::parse($event->start); @endphp
                                                <li class="justify-content-start">
                                                    <div class="text-center rounded-3 flex-shrink-0" style="width:44px;background:var(--brand-50);color:var(--brand-700);padding:.25rem 0;">
                                                        <div style="font-size:.6875rem;font-weight:700;text-transform:uppercase;">{{ $eventStart->format('M') }}</div>
                                                        <div style="font-family:var(--font-display);font-weight:700;line-height:1;">{{ $eventStart->format('j') }}</div>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold" style="color:var(--ink-900);">{{ $event->title }}</div>
                                                        <div class="dash-meta">{{ $eventStart->isToday() ? 'Today' : $eventStart->diffForHumans(['parts' => 1]) }}</div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Notices --}}
                        <div class="col-lg-7">
                            <div class="card h-100">
                                <div class="card-header bg-transparent"><i class="bi bi-megaphone me-2" style="color:var(--brand-500);"></i> Notices</div>
                                <div class="card-body p-0 text-dark">
                                    @if(count($notices) < 1)
                                        <div class="p-4 text-center text-secondary">
                                            <i class="bi bi-megaphone d-block mb-2" style="font-size:1.5rem;"></i>
                                            No notices yet
                                        </div>
                                    @else
                                        <div class="accordion accordion-flush" id="noticeAccordion">
                                            @foreach ($notices as $notice)
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="flush-heading{{$notice->id}}">
                                                        <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse{{$notice->id}}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="flush-collapse{{$notice->id}}">
                                                            <span class="d-block text-truncate me-2" style="min-width:0;">
                                                                <span class="fw-semibold">{{ Str::limit(trim(html_entity_decode(strip_tags($notice->notice))), 60) }}</span>
                                                                <span class="d-block dash-meta">{{ $notice->created_at->diffForHumans() }}</span>
                                                            </span>
                                                        </button>
                                                    </h2>
                                                    <div id="flush-collapse{{$notice->id}}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="flush-heading{{$notice->id}}" data-bs-parent="#noticeAccordion">
                                                        <div class="accordion-body overflow-auto">{!! \Mews\Purifier\Facades\Purifier::clean($notice->notice) !!}</div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
