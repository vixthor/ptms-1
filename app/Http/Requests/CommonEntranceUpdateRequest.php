<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommonEntranceUpdateRequest extends FormRequest
{
    /**
     * Academic staff, or a class teacher (checked against the class in the controller).
     */
    public function authorize(): bool
    {
        return $this->user()->canManageAcademics() || $this->user()->role === 'teacher';
    }

    /**
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'candidates' => ['nullable', 'array'],
            'candidates.*.registration_number' => ['nullable', 'string', 'max:50'],
            'candidates.*.score' => ['nullable', 'numeric', 'min:0', 'max:999'],
            'candidates.*.admitted_school' => ['nullable', 'string', 'max:150'],
        ];
    }
}
