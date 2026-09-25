@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-ui-checks"></i> Edit Question
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{route('exam.list')}}">Exams</a></li>
                            <li class="breadcrumb-item"><a href="{{route('cbt.questions.index', ['exam' => $exam->id])}}">{{ $exam->exam_name }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit Question</li>
                        </ol>
                    </nav>
                    @include('session-messages')

                    <div class="mb-4 form-card" style="max-width: 720px;">
                        <form action="{{ route('cbt.questions.update', ['exam' => $exam->id, 'question' => $question->id]) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="question" class="form-label">Question<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                    <textarea class="form-control" id="question" name="question" rows="2" required>{{old('question', $question->question)}}</textarea>
                                </div>
                                <div class="col-12">
                                    <label for="image" class="form-label">Diagram / Image <span class="text-secondary">(optional)</span></label>
                                    @if ($question->image_path)
                                        <div class="mb-2"><img src="{{asset($question->image_path)}}" alt="Current diagram" style="max-height:120px;border-radius:8px;"></div>
                                    @endif
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                </div>

                                <div class="col-12 mt-2"><hr></div>

                                @foreach (['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D', 'e' => 'E'] as $letter => $label)
                                <div class="col-12">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="correct_option" id="correct_{{$letter}}" value="{{$letter}}" {{old('correct_option', $question->correct_option) == $letter ? 'checked' : ''}} {{ in_array($letter, ['a','b']) ? 'required' : '' }}>
                                            <label class="form-check-label" for="correct_{{$letter}}" title="Mark option {{$label}} as the correct answer"></label>
                                        </div>
                                        <div class="flex-grow-1">
                                            <label for="option_{{$letter}}" class="form-label mb-1">Option {{$label}}{{ in_array($letter, ['a','b']) ? '' : ' (optional)' }}</label>
                                            <input type="text" class="form-control" id="option_{{$letter}}" name="option_{{$letter}}" value="{{old("option_{$letter}", $question->{"option_{$letter}"})}}" {{ in_array($letter, ['a','b']) ? 'required' : '' }}>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                <div class="col-12">
                                    <small class="text-secondary"><i class="bi bi-info-circle me-1"></i> Select the radio button next to the correct answer.</small>
                                    @error('correct_option')<div class="text-danger" style="font-size:.8125rem;">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-12 mt-2"><hr></div>

                                <div class="col-md-3">
                                    <label for="marks" class="form-label">Marks<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                    <input type="number" min="1" class="form-control" id="marks" name="marks" value="{{old('marks', $question->marks)}}" required>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="status" name="status" value="1" {{ old('status', $question->status) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status">Active (visible in the test)</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Update Question</button>
                                <a href="{{route('cbt.questions.index', ['exam' => $exam->id])}}" class="btn btn-outline-primary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
