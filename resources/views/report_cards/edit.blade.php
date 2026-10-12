@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-pencil"></i> Edit Report Card Details &middot; {{ $student->first_name }} {{ $student->last_name }}
                    </h1>
                    @include('session-messages')
                    <div class="bg-white border mt-4 p-3 shadow-sm" style="max-width:760px;">
                        <form action="{{ route('report.card.update', ['id' => $student->id]) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="semester_id" value="{{ $selectedSemesterId }}">

                            <div class="row">
                                <div class="col mt-2">
                                    <label class="form-label">House</label>
                                    <input type="text" name="house" class="form-control" value="{{ old('house', $reportCard->house) }}">
                                </div>
                                <div class="col mt-2">
                                    <label class="form-label">Weight</label>
                                    <input type="text" name="weight" class="form-control" value="{{ old('weight', $reportCard->weight) }}">
                                </div>
                                <div class="col mt-2">
                                    <label class="form-label">Height</label>
                                    <input type="text" name="height" class="form-control" value="{{ old('height', $reportCard->height) }}">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col mt-2">
                                    <label class="form-label">Attendance (days present)</label>
                                    <input type="number" min="0" name="attendance_days" class="form-control" value="{{ old('attendance_days', $reportCard->attendance_days ?? $attendanceDays) }}">
                                </div>
                                <div class="col mt-2">
                                    <label class="form-label">Days School Opened</label>
                                    <input type="number" min="0" name="days_school_opened" class="form-control" value="{{ old('days_school_opened', $reportCard->days_school_opened ?? $daysSchoolOpened) }}">
                                </div>
                                <div class="col mt-2">
                                    <label class="form-label">School Resumes</label>
                                    <input type="date" name="school_resumes_on" class="form-control" value="{{ old('school_resumes_on', optional($reportCard->school_resumes_on)->format('Y-m-d')) }}">
                                </div>
                            </div>

                            <div class="mt-3">
                                <label class="form-label fw-bold">Behavioural Ratings</label>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th class="text-center">5 - Excellent</th>
                                            <th class="text-center">4 - Very Good</th>
                                            <th class="text-center">3 - Good</th>
                                            <th class="text-center">2 - Average</th>
                                            <th class="text-center">1 - Fair</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $behaviouralItems = ['Attendance', 'Skills', 'Punctuality', 'Neatness', 'Politeness', 'Self Control', 'Relationship'];
                                            $ratings = old('behavioural_ratings', $reportCard->behavioural_ratings ?? []);
                                        @endphp
                                        @foreach ($behaviouralItems as $item)
                                            <tr>
                                                <td>{{ $item }}</td>
                                                @for ($score = 5; $score >= 1; $score--)
                                                    <td class="text-center">
                                                        <input type="radio" name="behavioural_ratings[{{ $item }}]" value="{{ $score }}" {{ (int) ($ratings[$item] ?? 0) === $score ? 'checked' : '' }}>
                                                    </td>
                                                @endfor
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-2">
                                <label class="form-label">Class Teacher's Comment</label>
                                <textarea name="class_teacher_comment" class="form-control" rows="2">{{ old('class_teacher_comment', $reportCard->class_teacher_comment) }}</textarea>
                            </div>
                            <div class="mt-2">
                                <label class="form-label">Class Teacher's Signature</label>
                                <input type="file" name="class_teacher_signature" class="form-control" accept="image/*">
                            </div>

                            <div class="mt-2">
                                <label class="form-label">Principal / HM's Comment</label>
                                <textarea name="headteacher_comment" class="form-control" rows="2">{{ old('headteacher_comment', $reportCard->headteacher_comment) }}</textarea>
                            </div>
                            <div class="mt-2">
                                <label class="form-label">Principal / HM's Signature</label>
                                <input type="file" name="headteacher_signature" class="form-control" accept="image/*">
                            </div>

                            <button type="submit" class="mt-3 btn btn-primary"><i class="bi bi-save"></i> Save Details</button>
                            <a href="{{ route('report.card.show', ['id' => $student->id, 'semester_id' => $selectedSemesterId]) }}" class="mt-3 btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Report Card</a>
                        </form>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
