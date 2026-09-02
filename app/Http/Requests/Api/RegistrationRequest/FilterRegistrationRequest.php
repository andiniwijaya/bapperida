<?php

namespace App\Http\Requests\Api\RegistrationRequest;

use App\Models\RegistrationRequest;
use App\Support\TablePagination;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates query parameters for paginated registration request listing.
 */
class FilterRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', RegistrationRequest::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'per_page' => TablePagination::rules(),
            'order' => ['nullable', 'string', Rule::in(['latest', 'oldest'])],
        ];
    }
}
