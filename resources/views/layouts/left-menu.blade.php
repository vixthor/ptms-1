<div id="appSidebar" class="col-xs-1 col-sm-1 col-md-1 col-lg-2 col-xl-2 col-xxl-2 border-rt-e6 px-0 app-sidebar">
    @auth
    <div class="d-lg-none sidebar-account">
        @php
            $mobile_latest_school_session = \App\Models\SchoolSession::latest()->first();
            $mobile_current_session_name = $mobile_latest_school_session ? $mobile_latest_school_session->session_name : null;
        @endphp
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-white fw-bold" style="width:38px;height:38px;background:linear-gradient(135deg, var(--brand-500), var(--brand-800));font-size:.85rem;">{{ strtoupper(substr(Auth::user()->first_name,0,1) . substr(Auth::user()->last_name,0,1)) }}</span>
            <div>
                <div class="fw-semibold" style="font-size:.875rem;color:var(--ink-900);">{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</div>
                <span class="badge bg-light text-dark">{{ ucfirst(Auth::user()->role) }}</span>
            </div>
        </div>
        @if (session()->has('browse_session_name') && session('browse_session_name') !== $mobile_current_session_name)
            <div class="sidebar-account-session text-danger"><i class="bi bi-exclamation-diamond-fill me-1"></i> Browsing: {{session('browse_session_name')}}</div>
        @elseif($mobile_current_session_name)
            <div class="sidebar-account-session"><i class="bi bi-calendar3 me-1"></i> Session: {{$mobile_current_session_name}}</div>
        @else
            <div class="sidebar-account-session text-danger"><i class="bi bi-exclamation-diamond-fill me-1"></i> No academic session yet</div>
        @endif
        <div class="d-flex gap-2 mt-2">
            <a href="{{ route('password.edit') }}" class="btn btn-sm btn-outline-primary flex-grow-1"><i class="bi bi-key"></i> Password</a>
            <a href="{{ route('logout') }}" class="btn btn-sm btn-outline-primary flex-grow-1"><i class="bi bi-door-open"></i> Logout</a>
        </div>
    </div>
    @endauth
    <div class="d-flex flex-column align-items-center align-items-sm-start ">
                <ul class="nav flex-column pt-2 w-100">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('dashboard')? 'active' : '' }}" href="{{ route('dashboard')}}"><i class="ms-auto bi bi-grid"></i> <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">{{ __('Dashboard') }}</span></a>
                    </li>
                    {{-- @if (Auth::user()->role == "teacher")
                    <li class="nav-item">
                        <a type="button" href="{{url('attendances')}}" class="d-flex nav-link {{ request()->is('attendances*')? 'active' : '' }}"><i class="bi bi-calendar2-week"></i> <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Attendance</span></a>
                    </li>
                    @endif --}}
                    @can('view classes')
                    <li class="nav-item">
                        @php
                            if (session()->has('browse_session_id')){
                                $classCount = \App\Models\SchoolClass::where('session_id', session('browse_session_id'))->count();
                            } else {
                                $latest_session = \App\Models\SchoolSession::latest()->first();
                                if($latest_session) {
                                    $classCount = \App\Models\SchoolClass::where('session_id', $latest_session->id)->count();
                                } else {
                                    $classCount = 0;
                                }
                            }
                        @endphp
                        <a class="nav-link d-flex {{ request()->is('classes')? 'active' : '' }}" href="{{ route('class.list') }}"><i class="bi bi-diagram-3"></i> <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Classes</span> <span class="ms-auto d-inline d-sm-none d-md-none d-xl-inline">{{ $classCount }}</span></a>
                    </li>
                    @endcan
                    @if(Auth::user()->role != "student")
                    <li class="nav-item">
                        <a type="button" href="#student-submenu" data-bs-toggle="collapse" class="d-flex nav-link {{ request()->is('students*')? 'active' : '' }}"><i class="bi bi-person-lines-fill"></i> <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Students</span>
                            <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                        </a>
                        <ul class="nav collapse {{ request()->is('students*')? 'show' : 'hide' }} bg-white" id="student-submenu">
                            <li class="nav-item w-100" ><a class="nav-link" href="{{ route('student.list') }}"><i class="bi bi-person-video2 me-2"></i> View Students</a></li>
                            @if (!session()->has('browse_session_id') && Auth::user()->canManageStudentsAndTeachers())
                            <li class="nav-item w-100" ><a class="nav-link" href="{{ route('student.create') }}"><i class="bi bi-person-plus me-2"></i> Add Student</a></li>
                            @endif
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a type="button" href="#teacher-submenu" data-bs-toggle="collapse" class="d-flex nav-link {{ request()->is('teachers*')? 'active' : '' }}"><i class="bi bi-person-lines-fill"></i> <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Teachers</span>
                            <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                        </a>
                        <ul class="nav collapse {{ request()->is('teachers*')? 'show' : 'hide' }} bg-white" id="teacher-submenu">
                            <li class="nav-item w-100" {{ request()->routeIs('teacher.list.show')? 'style="font-weight:bold;"' : '' }} ><a class="nav-link" href="{{ route('teacher.list') }}"><i class="bi bi-person-video2 me-2"></i> View Teachers</a></li>
                            @if (!session()->has('browse_session_id') && Auth::user()->canManageStudentsAndTeachers())
                            <li class="nav-item w-100" {{ request()->routeIs('teacher.create')? 'style="font-weight:bold;"' : '' }} ><a class="nav-link" href="{{ route('teacher.create') }}"><i class="bi bi-person-plus me-2"></i> Add Teacher</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif
                    @if(Auth::user()->role == "teacher")
                    <li class="nav-item">
                        <a class="nav-link {{ (request()->is('courses/teacher*') || request()->is('courses/assignments*'))? 'active' : '' }}" href="{{route('course.teacher.list', ['teacher_id' => Auth::user()->id])}}"><i class="bi bi-journal-medical"></i> <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">My Courses</span></a>
                    </li>
                    @endif
                    @if(Auth::user()->role == "student")
                    <li class="nav-item">
                        <a class="nav-link " href="{{route('student.attendance', ['id' => Auth::user()->id])}}"><i class="bi bi-calendar2-week"></i> <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Attendance</span></a>
                    <li class="nav-item">
                        <a class="nav-link " href="{{route('course.student.list', ['student_id' => Auth::user()->id])}}"><i class="bi bi-journal-medical"></i> <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Courses</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('my-payments*')? 'active' : '' }}" href="{{route('student.payments.list')}}"><i class="bi bi-currency-exchange"></i> <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">My Payments</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('*/report-card')? 'active' : '' }}" href="{{route('report.card.show', ['id' => Auth::user()->id])}}" target="_blank"><i class="bi bi-printer"></i> <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Report Card</span></a>
                    </li>
                    {{-- <li class="nav-item">
                        <a class="nav-link" href="#"><i class="bi bi-file-post"></i> <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Assignments</span></a>
                    </li><li class="nav-item">
                        <a class="nav-link" href="#"><i class="bi bi-cloud-sun"></i> <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Marks</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="bi bi-journal-text"></i> <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Syllabus</span></a>
                    </li> --}}
                    <li class="nav-item border-bottom">
                        @php
                            if (session()->has('browse_session_id')){
                                $class_info = \App\Models\Promotion::where('session_id', session('browse_session_id'))->where('student_id', Auth::user()->id)->first();
                            } else {
                                $latest_session = \App\Models\SchoolSession::latest()->first();
                                if($latest_session) {
                                    $class_info = \App\Models\Promotion::where('session_id', $latest_session->id)->where('student_id', Auth::user()->id)->first();
                                } else {
                                    $class_info = [];
                                }
                            }
                        @endphp
                        @if ($class_info)
                        <a class="nav-link" href="{{route('routine.list', [
                            'class_id'  => $class_info->class_id,
                            'section_id'=> $class_info->section_id
                        ])}}"><i class="bi bi-calendar4-range"></i> <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Routine</span></a>
                        @else
                        <a class="nav-link disabled" href="#" aria-disabled="true" title="Not yet assigned to a class"><i class="bi bi-calendar4-range"></i> <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Routine</span></a>
                        @endif
                    </li>
                    @endif
                    @if(Auth::user()->role != "student")
                    <li class="nav-item border-bottom">
                        <a type="button" href="#exam-grade-submenu" data-bs-toggle="collapse" class="d-flex nav-link {{ request()->is('exams*')? 'active' : '' }}"><i class="bi bi-file-text"></i> <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Exams / Grades</span>
                            <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                        </a>
                        <ul class="nav collapse {{ request()->is('exams*')? 'show' : 'hide' }} bg-white" id="exam-grade-submenu">
                            <li class="nav-item w-100" ><a class="nav-link" href="{{ route('exam.list') }}"><i class="bi bi-file-text me-2"></i> View Exams</a></li>
                            @if (Auth::user()->canManageAcademics() || Auth::user()->role == "teacher")
                            <li class="nav-item w-100" ><a class="nav-link" href="{{ route('exam.create') }}"><i class="bi bi-file-plus me-2"></i> Create Exams</a></li>
                            @endif
                            @if (Auth::user()->canManageAcademics())
                            <li class="nav-item w-100" ><a class="nav-link" href="{{ route('exam.grade.system.create') }}"><i class="bi bi-file-plus me-2"></i> Add Grade Systems</a></li>
                            @endif
                            <li class="nav-item w-100" ><a class="nav-link" href="{{ route('exam.grade.system.list') }}"><i class="bi bi-file-ruled me-2"></i> View Grade Systems</a></li>
                            @if (Auth::user()->canManageAcademics())
                            <li class="nav-item w-100" ><a class="nav-link {{ request()->is('report-cards/mass-sign')? 'active' : '' }}" href="{{ route('report.card.mass-sign.form') }}"><i class="bi bi-vector-pen me-2"></i> Mass Sign Results</a></li>
                            @endif
                        </ul>
                    </li>
                    {{-- <li class="nav-item border-bottom">
                        <a type="button" href="#" class="d-flex nav-link {{ request()->is('marks*')? 'active' : '' }} dropdown-toggle caret-off" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-cloud-sun"></i> <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Marks / Results</span>
                            <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{url('marks/view')}}">View Marks</a></li>
                            <li><a class="dropdown-item" href="{{url('marks/results')}}">View Results</a></li>
                        </ul>
                    </li> --}}
                    @endif
                    @if (Auth::user()->canManageAcademics() || Auth::user()->canManageClerical())
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('notice*')? 'active' : '' }}" href="{{ route('notice.create') }}"><i class="bi bi-megaphone"></i> <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Notice</span></a>
                    </li>
                    @endif
                    @if (Auth::user()->canManageAcademics())
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('calendar-event*')? 'active' : '' }}" href="{{ route('events.list') }}"><i class="bi bi-calendar-event"></i> <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Event</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('syllabus*')? 'active' : '' }}" href="{{ route('syllabus.create') }}"><i class="bi bi-journal-text"></i> <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Syllabus</span></a>
                    </li>
                    <li class="nav-item border-bottom">
                        <a class="nav-link {{ request()->is('routine*')? 'active' : '' }}" href="{{ route('routine.create') }}"><i class="bi bi-calendar4-range"></i> <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Routine</span></a>
                    </li>
                    @endif
                    @if (Auth::user()->canManageAcademics())
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('academics*')? 'active' : '' }}" href="{{ route('academic.setting') }}"><i class="bi bi-tools"></i> <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Academic</span></a>
                    </li>
                    @endif
                    @if (!session()->has('browse_session_id') && Auth::user()->canManageAcademics())
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('promotions*')? 'active' : '' }}" href="{{ route('promotions.list') }}"><i class="bi bi-sort-numeric-up-alt"></i> <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Promotion</span></a>
                    </li>
                    @endif
                    @if (Auth::user()->canManagePayments())
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('payments*')? 'active' : '' }}" href="{{ route('payments.list') }}"><i class="bi bi-currency-exchange"></i> <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Payment</span></a>
                    </li>
                    @endif
                    @if (Auth::user()->canManageStaff())
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('staff*')? 'active' : '' }}" href="{{ route('staff.list') }}"><i class="bi bi-person-lines-fill"></i> <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Staff</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link disabled" href="#" aria-disabled="true"><i class="bi bi-journals"></i> <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Library</span></a>
                    </li>
                    @endif
                    @if (Auth::user()->canManageWebsite())
                    <li class="nav-item">
                        <a type="button" href="#website-submenu" data-bs-toggle="collapse" class="d-flex nav-link {{ request()->is('website-admin*')? 'active' : '' }}"><i class="bi bi-globe"></i> <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Website</span>
                            <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                        </a>
                        <ul class="nav collapse {{ request()->is('website-admin*')? 'show' : 'hide' }} bg-white" id="website-submenu">
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('website-admin.pages.index') }}"><i class="bi bi-file-earmark-text me-2"></i> Pages</a></li>
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('website-admin.academic-programs.index') }}"><i class="bi bi-mortarboard me-2"></i> Academic Programs</a></li>
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('website-admin.admission-steps.index') }}"><i class="bi bi-list-ol me-2"></i> Admission Steps</a></li>
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('website-admin.fee-items.index') }}"><i class="bi bi-cash-coin me-2"></i> Fees</a></li>
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('website-admin.gallery-photos.index') }}"><i class="bi bi-images me-2"></i> Gallery</a></li>
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('website-admin.settings.edit') }}"><i class="bi bi-gear me-2"></i> Site Settings</a></li>
                        </ul>
                    </li>
                    @endif
                </ul>
            </div>
        </div>