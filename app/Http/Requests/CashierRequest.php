<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CashierRequest extends FormRequest
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
        return [
            'menu' => 'required|exists:products,id',
            'jumlah' => 'required',
            'harga' => 'required',
            'diskon_id' => 'exists:discounts,id'
        ];
    }
}
