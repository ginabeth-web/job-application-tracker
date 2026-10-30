<?php

namespace App\Http\Requests;

use App\Enums\ApplicationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $application = $this->route('application');

        return $application === null || $this->user()->can('update', $application);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'url', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'salary' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(ApplicationStatus::class)],
            'applied_on' => ['nullable', 'date'],
            'follow_up_on' => ['nullable', 'date'],
        ];
    }
}
