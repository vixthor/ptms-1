@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3"><i class="bi bi-diagram-3"></i> Classes</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Classes</li>
                        </ol>
                    </nav>
                    <div class="row">
                        @isset($school_classes)
                            @foreach ($school_classes as $school_class)
                                <div class="col-12">
                                    <div class="card my-3">
                                        <div class="card-header bg-transparent">
                                            <ul class="nav nav-tabs card-header-tabs">
                                                <li class="nav-item">
                                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#class{{$school_class->id}}" role="tab" aria-current="true"><i class="bi bi-diagram-3"></i> {{$school_class->class_name}}</button>
                                                </li>
                                                <li class="nav-item">
                                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#class{{$school_class->id}}-syllabus" role="tab" aria-current="false"><i class="bi bi-journal-text"></i> Syllabus</button>
                                                </li>
                                                <li class="nav-item">
                                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#class{{$school_class->id}}-courses" role="tab" aria-current="false"><i class="bi bi-journal-medical"></i> Subjects</button>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="card-body text-dark">
                                            <div class="tab-content">
                                                <div class="tab-pane fade show active" id="class{{$school_class->id}}" role="tabpanel">
                                                    @php
                                                        $class_section = \App\Models\Section::defaultForClass($school_class);
                                                    @endphp
                                                    <div class="list-group">
                                                        <a href="{{ route('student.list', ['class_id' => $school_class->id, 'section_id' => $class_section->id]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                            View Students
                                                        </a>
                                                        <a href="{{ route('routine.list',['class_id' => $school_class->id, 'section_id' => $class_section->id]) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                            View Routine
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="tab-pane fade" id="class{{$school_class->id}}-syllabus" role="tabpanel">
                                                    @isset($school_class->syllabi)
                                                    <table class="table table-borderless">
                                                        <thead>
                                                        <tr>
                                                            <th scope="col">Syllabus Name</th>
                                                            <th scope="col">Actions</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @foreach ($school_class->syllabi as $syllabus)
                                                            <tr>
                                                            <td>{{$syllabus->syllabus_name}}</td>
                                                            <td>
                                                                <div class="btn-group" role="group">
                                                                    <a href="{{asset($syllabus->syllabus_file_path)}}" role="button" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i> Download</a>
                                                                </div>
                                                            </td>
                                                            </tr>
                                                        @endforeach
                                                        </tbody>
                                                    </table>
                                                    @endisset
                                                </div>
                                                <div class="tab-pane fade" id="class{{$school_class->id}}-courses" role="tabpanel">
                                                    @isset($school_class->courses)
                                                        <table class="table">
                                                            <thead>
                                                            <tr>
                                                                <th scope="col">Subject Name</th>
                                                                <th scope="col">Type</th>
                                                                <th scope="col">Actions</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($school_class->courses as $course)
                                                            <tr>
                                                                <td>{{$course->course_name}}</td>
                                                                <td>{{$course->course_type}}</td>
                                                                <td>
                                                                    @can('edit courses')
                                                                    <a href="{{route('course.edit', ['id' => $course->id])}}" class="btn btn-sm btn-outline-primary" role="button"><i class="bi bi-pencil"></i> Edit</a>
                                                                    @endcan
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                            </tbody>
                                                        </table>
                                                    @endisset
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-transparent d-flex justify-content-between">
                                            <span></span>
                                            @can('edit classes')
                                            <span><a href="{{route('class.edit', ['id' => $school_class->id])}}" class="btn btn-sm btn-outline-primary" role="button"><i class="bi bi-pencil"></i> Edit Class</a></span>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endisset
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
