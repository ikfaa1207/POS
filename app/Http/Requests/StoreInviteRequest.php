<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInviteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $actor = $this->user();
        $roles = $actor && $actor->hasRole('Owner')
            ? ['Owner', 'Manager', 'Sales', 'Encoder']
            : ['Sales'];

        return [
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
                Rule::unique('user_invites', 'email')->where(fn ($query) => $query->whereNull('used_at')),
            ],
            'role_name' => [
                'required',
                'string',
                Rule::in($roles),
            ],
        ];
    }
}
