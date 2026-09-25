@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-ui-checks"></i> CBT Questions
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{route('exam.list')}}">Exams</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $exam->exam_name }}</li>
                        </ol>
                    </nav>
                    @include('session-messages')

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <div class="fw-semibold" style="font-size:1rem;">{{ $exam->exam_name }}</div>
                            <div class="text-secondary" style="font-size:.8125rem;">{{ optional($exam->course)->course_name }} &middot; {{ $questions->count() }} question(s) &middot; {{ $questions->sum('marks') }} total marks</div>
                        </div>
                        <a href="{{ route('cbt.questions.create', ['exam' => $exam->id]) }}" class="btn btn-primary d-flex align-items-center gap-2" style="width:fit-content;">
                            <i class="bi bi-plus-circle"></i> Add Question
                        </a>
                    </div>

                    <div class="bg-white border shadow-sm p-3">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Question</th>
                                    <th scope="col">Correct Answer</th>
                                    <th scope="col">Marks</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($questions as $index => $question)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td style="white-space:normal;max-width:420px;">{{ $question->question }}</td>
                                    <td><span class="badge" style="background:var(--success-100);color:var(--success-500);">{{ strtoupper($question->correct_option) }}. {{ $question->{"option_{$question->correct_option}"} }}</span></td>
                                    <td>{{ $question->marks }}</td>
                                    <td>
                                        @if($question->status)
                                            <span class="badge" style="background:var(--success-100);color:var(--success-500);">Active</span>
                                        @else
                                            <span class="badge" style="background:var(--ink-100);color:var(--ink-500);">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{route('cbt.questions.edit', ['exam' => $exam->id, 'question' => $question->id])}}" role="button" class="btn btn-sm btn-outline-primary"><i class="bi bi-pen"></i> Edit</a>
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="event.preventDefault(); if(confirm('Delete this question?')) document.getElementById('delete-question-{{$question->id}}').submit();"><i class="bi bi-trash2"></i> Delete</button>
                                            <form id="delete-question-{{$question->id}}" action="{{ route('cbt.questions.destroy', ['exam' => $exam->id, 'question' => $question->id]) }}" method="POST" class="d-none">
                                                @csrf
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-secondary py-4">
                                        <i class="bi bi-ui-checks d-block mb-2" style="font-size:1.5rem;"></i>
                                        No questions added yet for this exam.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
