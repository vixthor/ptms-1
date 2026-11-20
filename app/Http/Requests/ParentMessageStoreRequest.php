<?php

namespace App\Http\Requests;

use App\Models\ParentMessage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ParentMessageStoreRequest extends FormRequest
{
    /**
     * Office staff message any parents; class teachers only their own class
     * (enforced per class in the controller).
     */
    public function authorize(): bool
    {
        return $this->user()->canManageStudentsAndTeachers() || $this->user()->role === 'teacher';
    }

    /**
     * @return array<string, array<int, mixed>|string>
     */
    public function rules(): array
    {
        return [
            'audience' => ['required', Rule::in([ParentMessage::AUDIENCE_SCHOOL, ParentMessage::AUDIENCE_CLASS, ParentMessage::AUDIENCE_UNPAID_TUITION])],
            'class_id' => ['nullable', 'required_if:audience,'.ParentMessage::AUDIENCE_CLASS, 'integer', 'exists:school_classes,id'],
            'body' => ['required', 'string', 'max:480'],
            'channel' => ['required', Rule::in([ParentMessage::CHANNEL_SMS, ParentMessage::CHANNEL_WHATSAPP])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'class_id.required_if' => 'Choose which class to message.',
            'body.max' => 'Keep the message under 480 characters (three SMS pages).',
        ];
    }
}
