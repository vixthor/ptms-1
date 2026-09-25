<?php

namespace App\Http\Controllers;

use App\Models\AssignedTeacher;
use App\Models\CbtQuestion;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CbtQuestionController extends Controller
{
    /**
     * Admin-level/headteacher staff can manage questions on any exam; a
     * teacher may only manage questions for exams on courses they're
     * assigned to teach.
     */
    protected function authorizeExam(Exam $exam): void
    {
        $user = Auth::user();

        if ($user->canManageAcademics()) {
            return;
        }

        $isAssigned = AssignedTeacher::where('teacher_id', $user->id)
            ->where('course_id', $exam->course_id)
            ->exists();

        abort_unless($isAssigned, 403);
    }

    public function index(Exam $exam)
    {
        $this->authorizeExam($exam);

        $questions = CbtQuestion::where('exam_id', $exam->id)
            ->orderBy('id')
            ->get();

        return view('cbt.questions.index', [
            'exam' => $exam,
            'questions' => $questions,
        ]);
    }

    public function create(Exam $exam)
    {
        $this->authorizeExam($exam);

        return view('cbt.questions.create', ['exam' => $exam]);
    }

    protected function validatePayload(Request $request, ?int $ignoreId = null)
    {
        return Validator::make($request->all(), [
            'question' => ['required', 'string'],
            'option_a' => ['required', 'string'],
            'option_b' => ['required', 'string'],
            'option_c' => ['nullable', 'string'],
            'option_d' => ['nullable', 'string'],
            'option_e' => ['nullable', 'string'],
            'correct_option' => ['required', 'in:a,b,c,d,e'],
            'marks' => ['required', 'integer', 'min:1'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);
    }

    public function store(Request $request, Exam $exam)
    {
        $this->authorizeExam($exam);

        $validate = $this->validatePayload($request);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        // The chosen correct option must actually have a value filled in.
        if (blank($request->input('option_'.$request->correct_option))) {
            return redirect()->back()
                ->withErrors(['correct_option' => 'The correct answer must be one of the options you filled in.'])
                ->withInput();
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = uniqid().'.'.$image->getClientOriginalExtension();
            $image->move(public_path().'/upload/cbt/', $filename);
            $imagePath = 'upload/cbt/'.$filename;
        }

        CbtQuestion::create([
            'exam_id' => $exam->id,
            'question' => $request->question,
            'option_a' => $request->option_a,
            'option_b' => $request->option_b,
            'option_c' => $request->option_c,
            'option_d' => $request->option_d,
            'option_e' => $request->option_e,
            'correct_option' => $request->correct_option,
            'image_path' => $imagePath,
            'marks' => $request->marks,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('cbt.questions.index', ['exam' => $exam->id])
            ->with('status', 'Question added successfully.');
    }

    public function edit(Exam $exam, CbtQuestion $question)
    {
        $this->authorizeExam($exam);
        abort_unless($question->exam_id === $exam->id, 404);

        return view('cbt.questions.edit', ['exam' => $exam, 'question' => $question]);
    }

    public function update(Request $request, Exam $exam, CbtQuestion $question)
    {
        $this->authorizeExam($exam);
        abort_unless($question->exam_id === $exam->id, 404);

        $validate = $this->validatePayload($request);

        if ($validate->fails()) {
            return redirect()->back()->withErrors($validate)->withInput();
        }

        if (blank($request->input('option_'.$request->correct_option))) {
            return redirect()->back()
                ->withErrors(['correct_option' => 'The correct answer must be one of the options you filled in.'])
                ->withInput();
        }

        $imagePath = $question->image_path;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = uniqid().'.'.$image->getClientOriginalExtension();
            $image->move(public_path().'/upload/cbt/', $filename);
            $imagePath = 'upload/cbt/'.$filename;
        }

        $question->update([
            'question' => $request->question,
            'option_a' => $request->option_a,
            'option_b' => $request->option_b,
            'option_c' => $request->option_c,
            'option_d' => $request->option_d,
            'option_e' => $request->option_e,
            'correct_option' => $request->correct_option,
            'image_path' => $imagePath,
            'marks' => $request->marks,
            'status' => $request->boolean('status', true),
        ]);

        return redirect()->route('cbt.questions.index', ['exam' => $exam->id])
            ->with('status', 'Question updated successfully.');
    }

    public function destroy(Exam $exam, CbtQuestion $question)
    {
        $this->authorizeExam($exam);
        abort_unless($question->exam_id === $exam->id, 404);

        $question->delete();

        return redirect()->route('cbt.questions.index', ['exam' => $exam->id])
            ->with('status', 'Question deleted.');
    }
}
