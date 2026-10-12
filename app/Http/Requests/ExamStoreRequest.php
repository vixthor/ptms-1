<?php

namespace App\Http\Requests;

use App\Models\Exam;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExamStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('create exams');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'exam_name' => ['required', 'string', Rule::in(Exam::CA_EXAM_NAMES)],
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'semester_id' => 'required|integer|gt:0',
            'class_id' => 'required|integer|gt:0',
            'course_id' => 'required|integer|gt:0',
            'session_id' => 'required|integer|gt:0',
        ];
    }
}
