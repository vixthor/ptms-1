@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-calendar2-week"></i> Take Attendance
                    </h1>

                    @include('session-messages')

                    <h3><i class="bi bi-compass"></i> Class: {{$school_class->class_name}}</h3>
                    <div class="mt-4">Date: {{ now()->format('l, j F Y') }}</div>
                    @if ($todays_attendance->isNotEmpty())
                        <p class="text-success mt-2">
                            <i class="bi bi-check-circle-fill me-2"></i> Today's attendance was taken at {{ $todays_attendance->min('created_at')->format('g:i a') }}. You can correct it below.
                        </p>
                    @else
                        <p class="text-primary mt-2">
                            <i class="bi bi-info-circle-fill me-2"></i> Attendance is taken once, at the start of the school day.
                        </p>
                    @endif
                    <div class="row mt-4">
                        <div class="col-10 bg-white border p-3 shadow-sm">
                            <form action="{{route('attendances.store')}}" method="POST">
                                @csrf
                                <input type="hidden" name="session_id" value="{{$current_school_session_id}}">
                                <input type="hidden" name="class_id" value="{{$school_class->id}}">
                                <input type="hidden" name="section_id" value="{{$school_section->id}}">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col"># ID Card Number</th>
                                            <th scope="col">Student Name</th>
                                            <th scope="col">Present</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($student_list as $student)
                                        <input type="hidden" name="student_ids[]" value="{{$student->student_id}}">
                                        <tr>
                                            <th scope="row">{{$student->id_card_number}}</th>
                                            <td>{{$student->student->first_name}} {{$student->student->last_name}}</td>
                                            <td>
                                                <input class="form-check-input" type="checkbox" name="status[{{$student->student_id}}]" {{ optional($todays_attendance->get($student->student_id))->status === 'off' ? '' : 'checked' }}>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @if(count($student_list) > 0)
                                <div class="mb-4">
                                    <button type="submit" class="btn btn-outline-primary"><i class="bi bi-check2"></i> {{ $todays_attendance->isNotEmpty() ? 'Update Attendance' : 'Submit' }}</button>
                                </div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
