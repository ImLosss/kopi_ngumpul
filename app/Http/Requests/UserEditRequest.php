<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserEditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user'); 
        
        return [
            'name' => 'required',
            'email' => [
                'required',
                Rule::unique('users')->ignore($userId),
                'regex:/^.+@.+$/i'
            ],
            'notelp' => 'required|numeric',
            'role' => 'required|exists:roles,name'
        ];
    }
}
