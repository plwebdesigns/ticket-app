<?php

namespace App\Http\Requests;

use App\Enums\Priority;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $attachments = $this->file('attachments');

        if ($attachments instanceof UploadedFile) {
            $files = $this->files->all();
            $files['attachments'] = [$attachments];
            $this->files->replace($files);
            $this->convertedFiles = null;
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type_id' => ['required', 'integer', 'exists:types,id'],
            'current_state_id' => ['required', 'integer', 'exists:current_states,id'],
            'priority' => ['required', Rule::enum(Priority::class)],
            'requested_by_id' => ['nullable', 'integer', 'exists:users,id'],
            'assigned_to_id' => ['nullable', 'integer', 'exists:users,id'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
        ];
    }
}
