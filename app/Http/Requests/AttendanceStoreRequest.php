<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->can('take attendances');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'class_id' => 'required|integer|exists:school_classes,id',
            'section_id' => 'required|integer',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'integer',
            'status' => 'nullable|array',
            'session_id' => 'required|integer',
        ];
    }
}
