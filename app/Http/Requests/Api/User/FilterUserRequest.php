<?php

namespace App\Http\Requests\Api\User;

use App\Models\User;
use App\Support\TablePagination;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates query parameters for paginated user listing.
 */
class FilterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', User::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => TablePagination::rules(),
            'order' => ['nullable', 'string', Rule::in(['latest', 'oldest'])],
        ];
    }
}
