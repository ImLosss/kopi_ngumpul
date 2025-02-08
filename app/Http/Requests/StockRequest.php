<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockRequest extends FormRequest
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
        $productId = $this->route('stock'); 

        // dd($productId);

        return [
            'name' => [
                'required',
                Rule::unique('stocks')->ignore($productId),
            ]
        ];
    }

     /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        // $validator->after(function ($validator) {
        //     if ($this->harga <= $this->modal) {
        //         $validator->errors()->add('harga', 'Harga harus lebih besar dari modal.');
        //     }
        // });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        
        return [
            'name.unique' => 'Bahan ini sudah ada.'
        ];
    }
}
