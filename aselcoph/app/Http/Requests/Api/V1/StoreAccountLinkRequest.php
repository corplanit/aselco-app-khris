<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'account_number' => ['required', 'numeric'],
            'owner_name' => ['required', 'string', 'max:255'],
            'privacy_accepted' => ['accepted'],
        ];
    }
}
