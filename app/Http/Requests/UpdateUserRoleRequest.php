<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();
        $allowed = $user && $user->hasRole('Owner')
            ? ['Owner', 'Manager', 'Sales', 'Encoder']
            : ['Sales'];

        return [
            'role' => ['required', 'string', Rule::in($allowed)],
        ];
    }
}
