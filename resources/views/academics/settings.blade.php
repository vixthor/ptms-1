@extends('layouts.app')

@section('content')
@php
    $isCurrentSession = $latest_school_session_id == $current_school_session_id;
    $marksSubmissionOpen = ($academic_setting->marks_submission_status ?? 'off') === 'on';
    $viewedSessionName = optional($school_sessions->firstWhere('id', $current_school_session_id))->session_name;
@endphp
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h1 class="display-6 mb-0">
                            <i class="bi bi-tools"></i> Academic Settings
                        </h1>
                        <form action="{{ route('session.browse') }}" method="POST" class="d-flex align-items-center gap-2">
                            @csrf
                            <label for="browseSession" class="form-label mb-0 small text-muted text-nowrap">Viewing session</label>
                            <select id="browseSession" class="form-select form-select-sm" name="session_id" required onchange="this.form.submit()">
                                @foreach ($school_sessions as $school_session)
                                    <option value="{{ $school_session->id }}" @selected($school_session->id == $current_school_session_id)>
                                        {{ $school_session->session_name }}{{ $school_session->id == $latest_school_session_id ? ' (current)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <noscript><button class="btn btn-sm btn-outline-primary" type="submit">Go</button></noscript>
                        </form>
                    </div>

                    @include('session-messages')

                    @unless ($isCurrentSession)
                        <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <span>
                                <i class="bi bi-eye me-2"></i>
                                You're viewing <strong>{{ $browse_session_name ?? $viewedSessionName }}</strong> (read-only). Settings can only be changed in the current session.
                            </span>
                            <form action="{{ route('session.browse') }}" method="POST">
                                @csrf
                                <input type="hidden" name="session_id" value="{{ $latest_school_session_id }}">
                                <button class="btn btn-sm btn-warning" type="submit">Switch back to current</button>
                            </form>
                        </div>
                    @endunless

                    <ul class="nav nav-tabs mb-3" id="academicSettingsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-sessions" data-bs-toggle="tab" data-bs-target="#pane-sessions" type="button" role="tab" aria-controls="pane-sessions" aria-selected="true">
                                <i class="bi bi-calendar3 me-1"></i> Session &amp; Terms
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-classes" data-bs-toggle="tab" data-bs-target="#pane-classes" type="button" role="tab" aria-controls="pane-classes" aria-selected="false">
                                <i class="bi bi-diagram-3 me-1"></i> Classes &amp; Subjects
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-teachers" data-bs-toggle="tab" data-bs-target="#pane-teachers" type="button" role="tab" aria-controls="pane-teachers" aria-selected="false">
                                <i class="bi bi-person-badge me-1"></i> Teachers
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-policies" data-bs-toggle="tab" data-bs-target="#pane-policies" type="button" role="tab" aria-controls="pane-policies" aria-selected="false">
                                <i class="bi bi-sliders me-1"></i> Policies
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content mb-4">
                        {{-- Session & Terms --}}
                        <div class="tab-pane fade show active" id="pane-sessions" role="tabpanel" aria-labelledby="tab-sessions" tabindex="0">
                            <div class="row g-4">
                                <div class="col-lg-7">
                                    <div class="p-3 border bg-white shadow-sm h-100">
                                        <h6>Terms in {{ $viewedSessionName ?? 'this session' }}</h6>
                                        @if ($semesters->isEmpty())
                                            <p class="text-muted small mb-0">No terms yet. Create the first term to start adding subjects.</p>
                                        @else
                                            <div class="table-responsive">
                                                <table class="table table-sm align-middle mb-0">
                                                    <thead>
                                                        <tr><th>Term</th><th>Starts</th><th>Ends</th></tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($semesters as $semester)
                                                            <tr>
                                                                <td>{{ $semester->semester_name }}</td>
                                                                <td>{{ \Illuminate\Support\Carbon::parse($semester->start_date)->format('d M Y') }}</td>
                                                                <td>{{ \Illuminate\Support\Carbon::parse($semester->end_date)->format('d M Y') }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @if ($isCurrentSession)
                                    <div class="col-lg-5">
                                        <div class="p-3 border bg-light shadow-sm mb-4">
                                            <h6>Create Term for Current Session</h6>
                                            <form action="{{ route('semester.store') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">
                                                <div class="mb-2">
                                                    <label for="inputTermName" class="form-label">Term name <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control form-control-sm" id="inputTermName" placeholder="First Term" name="semester_name" required>
                                                </div>
                                                <div class="row g-2">
                                                    <div class="col-sm-6">
                                                        <label for="inputStarts" class="form-label">Starts <span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control form-control-sm" id="inputStarts" name="start_date" required>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <label for="inputEnds" class="form-label">Ends <span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control form-control-sm" id="inputEnds" name="end_date" required>
                                                    </div>
                                                </div>
                                                <button type="submit" class="mt-3 btn btn-sm btn-outline-primary"><i class="bi bi-check2"></i> Create term</button>
                                            </form>
                                        </div>
                                        <div class="p-3 border bg-light shadow-sm">
                                            <h6>Start a New Session</h6>
                                            <p class="text-danger small">
                                                <i class="bi bi-exclamation-diamond-fill me-1"></i> Create one session per academic year. The newest session becomes the current one for everyone.
                                            </p>
                                            <form action="{{ route('session.store') }}" method="POST">
                                                @csrf
                                                <label for="inputSessionName" class="form-label">Session name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control form-control-sm" id="inputSessionName" placeholder="2026 - 2027" name="session_name" required>
                                                <button class="mt-3 btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-check2"></i> Create session</button>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Classes & Subjects --}}
                        <div class="tab-pane fade" id="pane-classes" role="tabpanel" aria-labelledby="tab-classes" tabindex="0">
                            <div class="row g-4">
                                <div class="col-lg-7">
                                    <div class="p-3 border bg-white shadow-sm h-100">
                                        <h6>Classes</h6>
                                        @if ($school_classes->isEmpty())
                                            <p class="text-muted small mb-0">No classes yet.</p>
                                        @else
                                            <div class="table-responsive">
                                                <table class="table table-sm align-middle mb-0">
                                                    <thead>
                                                        <tr><th>Class</th><th>Subjects</th><th>Class teacher</th></tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($school_classes as $school_class)
                                                            @php $classTeacher = optional($class_teachers->get($school_class->id))->teacher; @endphp
                                                            <tr>
                                                                <td>{{ $school_class->class_name }}</td>
                                                                <td>{{ $school_class->courses->count() }}</td>
                                                                <td>
                                                                    @if ($classTeacher)
                                                                        {{ $classTeacher->first_name }} {{ $classTeacher->last_name }}
                                                                    @else
                                                                        <span class="text-muted">Not assigned</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            <a href="{{ route('class.list') }}" class="small d-inline-block mt-2">Manage classes and subjects <i class="bi bi-arrow-right"></i></a>
                                        @endif
                                    </div>
                                </div>
                                @if ($isCurrentSession)
                                    <div class="col-lg-5">
                                        <div class="p-3 border bg-light shadow-sm mb-4">
                                            <h6>Create Class</h6>
                                            <form action="{{ route('class.store') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">
                                                <label for="inputClassName" class="form-label">Class name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control form-control-sm" id="inputClassName" name="class_name" placeholder="Primary 1" required>
                                                <button class="mt-3 btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-check2"></i> Create class</button>
                                            </form>
                                        </div>
                                        <div class="p-3 border bg-light shadow-sm">
                                            <h6>Create Subject</h6>
                                            @if ($semesters->isEmpty() || $school_classes->isEmpty())
                                                <p class="text-muted small mb-0">
                                                    <i class="bi bi-info-circle-fill me-1"></i>
                                                    Create at least one {{ $semesters->isEmpty() ? 'term' : 'class' }} before adding subjects.
                                                </p>
                                            @else
                                                <form action="{{ route('course.store') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">
                                                    <div class="mb-2">
                                                        <label for="inputSubjectName" class="form-label">Subject name <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm" id="inputSubjectName" name="course_name" placeholder="Mathematics" required>
                                                    </div>
                                                    <div class="mb-2">
                                                        <label for="inputSubjectType" class="form-label">Subject type <span class="text-danger">*</span></label>
                                                        <select class="form-select form-select-sm" id="inputSubjectType" name="course_type" required>
                                                            <option value="Core">Core</option>
                                                            <option value="General">General</option>
                                                            <option value="Elective">Elective</option>
                                                            <option value="Optional">Optional</option>
                                                        </select>
                                                    </div>
                                                    <div class="row g-2">
                                                        <div class="col-sm-6">
                                                            <label for="inputSubjectTerm" class="form-label">Term <span class="text-danger">*</span></label>
                                                            <select class="form-select form-select-sm" id="inputSubjectTerm" name="semester_id" required>
                                                                @foreach ($semesters as $semester)
                                                                    <option value="{{ $semester->id }}">{{ $semester->semester_name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-sm-6">
                                                            <label for="inputSubjectClass" class="form-label">Class <span class="text-danger">*</span></label>
                                                            <select class="form-select form-select-sm" id="inputSubjectClass" name="class_id" required>
                                                                @foreach ($school_classes as $school_class)
                                                                    <option value="{{ $school_class->id }}">{{ $school_class->class_name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <button class="mt-3 btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-check2"></i> Create subject</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Teachers --}}
                        <div class="tab-pane fade" id="pane-teachers" role="tabpanel" aria-labelledby="tab-teachers" tabindex="0">
                            <div class="row g-4">
                                <div class="col-lg-7">
                                    <div class="p-3 border bg-white shadow-sm h-100">
                                        <h6>Class Teachers</h6>
                                        @if ($school_classes->isEmpty())
                                            <p class="text-muted small mb-0">No classes yet.</p>
                                        @else
                                            <div class="table-responsive">
                                                <table class="table table-sm align-middle mb-0">
                                                    <thead>
                                                        <tr><th>Class</th><th>Class teacher</th></tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($school_classes as $school_class)
                                                            @php $classTeacher = optional($class_teachers->get($school_class->id))->teacher; @endphp
                                                            <tr>
                                                                <td>{{ $school_class->class_name }}</td>
                                                                <td>
                                                                    @if ($classTeacher)
                                                                        {{ $classTeacher->first_name }} {{ $classTeacher->last_name }}
                                                                    @else
                                                                        <span class="badge bg-warning text-dark">Not assigned</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @if ($isCurrentSession)
                                    <div class="col-lg-5">
                                        <div class="p-3 border bg-light shadow-sm">
                                            <h6>Assign Class Teacher</h6>
                                            <p class="text-primary small">
                                                <i class="bi bi-info-circle-fill me-1"></i> The class teacher handles every subject of the class. Re-assigning a class replaces its current teacher.
                                            </p>
                                            <form action="{{ route('teacher.assign') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">
                                                <div class="mb-2">
                                                    <label for="inputAssignTeacher" class="form-label">Teacher <span class="text-danger">*</span></label>
                                                    <select id="inputAssignTeacher" class="form-select form-select-sm" name="teacher_id" required>
                                                        <option value="" selected disabled>Please select a teacher</option>
                                                        @foreach ($teachers as $teacher)
                                                            <option value="{{ $teacher->id }}">{{ $teacher->first_name }} {{ $teacher->last_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label for="inputAssignToClass" class="form-label">Class <span class="text-danger">*</span></label>
                                                    <select id="inputAssignToClass" class="form-select form-select-sm" name="class_id" required>
                                                        <option value="" selected disabled>Please select a class</option>
                                                        @foreach ($school_classes as $school_class)
                                                            @php $classTeacher = optional($class_teachers->get($school_class->id))->teacher; @endphp
                                                            <option value="{{ $school_class->id }}">
                                                                {{ $school_class->class_name }}{{ $classTeacher ? ' — currently '.$classTeacher->first_name.' '.$classTeacher->last_name : '' }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <button type="submit" class="mt-3 btn btn-sm btn-outline-primary"><i class="bi bi-check2"></i> Assign</button>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Policies --}}
                        <div class="tab-pane fade" id="pane-policies" role="tabpanel" aria-labelledby="tab-policies" tabindex="0">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="p-3 border bg-light shadow-sm h-100">
                                        <h6>Attendance</h6>
                                        <p class="text-muted small mb-0">
                                            <i class="bi bi-info-circle-fill me-1"></i> Attendance is taken once a day, at the start of the school day, by each class's class teacher.
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 border bg-light shadow-sm h-100">
                                        <h6>Final Marks Submission</h6>
                                        <p class="text-muted small">
                                            <i class="bi bi-info-circle-fill me-1"></i> Open this just before the end of a term so teachers can submit final marks; close it at the start of a new term.
                                        </p>
                                        <form action="{{ route('final.marks.submission.status.update') }}" method="POST">
                                            @csrf
                                            <fieldset @disabled(! $isCurrentSession)>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="marks_submission_status" id="marks_submission_status_check" @checked($marksSubmissionOpen)>
                                                    <label class="form-check-label" for="marks_submission_status_check">
                                                        Teachers can submit final marks
                                                        <span class="badge {{ $marksSubmissionOpen ? 'bg-success' : 'bg-secondary' }} ms-1">{{ $marksSubmissionOpen ? 'Open' : 'Closed' }}</span>
                                                    </label>
                                                </div>
                                                <button type="submit" class="mt-3 btn btn-sm btn-outline-primary"><i class="bi bi-check2"></i> Save</button>
                                            </fieldset>
                                        </form>
                                    </div>
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
<script>
    (function () {
        var storageKey = 'academicSettingsTab';
        var tabs = document.querySelectorAll('#academicSettingsTabs [data-bs-toggle="tab"]');
        var saved = null;

        try { saved = sessionStorage.getItem(storageKey); } catch (e) {}

        var target = window.location.hash || saved;
        if (target) {
            var trigger = document.querySelector('#academicSettingsTabs [data-bs-target="' + target.replace('#tab-', '#pane-') + '"]');
            if (trigger && window.bootstrap) {
                bootstrap.Tab.getOrCreateInstance(trigger).show();
            }
        }

        tabs.forEach(function (tab) {
            tab.addEventListener('shown.bs.tab', function (event) {
                try { sessionStorage.setItem(storageKey, event.target.getAttribute('data-bs-target')); } catch (e) {}
            });
        });
    })();
</script>
@endsection
