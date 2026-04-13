<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
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
            'name' => ['required', 'string', 'max:120'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'sku' => [
                'required',
                'string',
                'max:40',
                'alpha_dash',
                Rule::unique('products', 'sku')->ignore($this->route('product')),
            ],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['Healthy', 'Low stock', 'Urgent'])],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
