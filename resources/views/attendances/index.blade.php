@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3"><i class="bi bi-calendar2-week"></i> Attendance</h1>
                    <div class="row">
                        <div class="col">
                            <div class="row">
                                @foreach ($classes_and_sections['school_classes'] as $school_class)
                                <div class="col-12">
                                    <div class="card my-3">
                                        <div class="card-header bg-transparent">
                                            <i class="bi bi-diagram-2"></i> {{$school_class->class_name}}
                                        </div>
                                        <div class="card-body text-dark">
                                            @if ($academic_setting->attendance_type == 'course')
                                                @foreach ($courses as $course)
                                                    @if ($course->class_id == $school_class->id)
                                                    <h6>
                                                        Subject: {{$course->course_name}}
                                                    </h6>
                                                    <div class="list-group mb-2">
                                                        <a href="{{url('attendances/view?class_id='.$school_class->id.'&class_name='.$school_class->class_name.'&course_id='.$course->id.'&course_name='.$course->course_name)}}" class="list-group-item list-group-item-action  d-flex justify-content-between align-items-center">
                                                            View Attendance
                                                            {{-- <span class="badge bg-success rounded-pill">PRESENT TODAY 38</span> --}}
                                                        </a>
                                                        <a href="{{url('attendances/take?class_id='.$school_class->id.'&class_name='.$school_class->class_name.'&course_id='.$course->id.'&course_name='.$course->course_name)}}" class="list-group-item list-group-item-action">
                                                            Take Attendance
                                                        </a>
                                                    </div>   
                                                    @endif
                                                @endforeach
                                            @else
                                            @php
                                                $class_section = \App\Models\Section::defaultForClass($school_class);
                                            @endphp
                                            <div class="list-group mb-2">
                                                <a href="{{url('attendances/view?class_id='.$school_class->id.'&section_id='.$class_section->id.'&class_name='.$school_class->class_name)}}" class="list-group-item list-group-item-action  d-flex justify-content-between align-items-center">
                                                    View Attendance
                                                </a>
                                                <a href="{{url('attendances/take?class_id='.$school_class->id.'&class_name='.$school_class->class_name.'&section_id='.$class_section->id)}}" class="list-group-item list-group-item-action">
                                                    Take Attendance
                                                </a>
                                            </div>
                                            @endif
                                        </div>
                                        {{-- <div class="card-footer bg-transparent">Total Students: 120</div> --}}
                                    </div>
                                </div>
                                @endforeach
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
