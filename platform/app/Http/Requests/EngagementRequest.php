<?php

namespace App\Http\Requests;

use App\Enums\EngagementStatus;
use App\Enums\EngagementType;
use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EngagementRequest extends FormRequest
{
    /**
     * Authorization is handled by the controller through EngagementPolicy.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $staff = Rule::exists('users', 'id')->whereIn('role', [Role::Admin->value, Role::Pentester->value]);

        return [
            'client_id' => ['required', 'exists:clients,id'],
            'title' => ['required', 'string', 'max:160'],
            'type' => ['required', Rule::enum(EngagementType::class)],
            'status' => ['required', Rule::enum(EngagementStatus::class)],
            'scope' => ['required', 'string', 'max:5000'],
            'methodology' => ['nullable', 'string', 'max:160'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
            'executive_summary' => ['nullable', 'string', 'max:10000'],
            'lead_id' => ['required', $staff],
            'team' => ['array'],
            'team.*' => ['integer', $staff],
        ];
    }
}
