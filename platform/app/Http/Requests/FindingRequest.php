<?php

namespace App\Http\Requests;

use App\Enums\FindingStatus;
use App\Rules\CvssVector;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FindingRequest extends FormRequest
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
        return [
            'template_id' => ['nullable', 'exists:finding_templates,id'],
            'title' => ['required', 'string', 'max:200'],
            'cvss_vector' => ['required', 'string', new CvssVector],
            'status' => ['required', Rule::enum(FindingStatus::class)],
            'affected_assets' => ['required', 'string', 'max:3000'],
            'description' => ['required', 'string', 'max:20000'],
            'proof_of_concept' => ['nullable', 'string', 'max:20000'],
            'impact' => ['required', 'string', 'max:10000'],
            'remediation' => ['required', 'string', 'max:10000'],
            'references' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
