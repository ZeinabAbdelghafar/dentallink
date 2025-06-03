<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'sometimes|string|min:3|max:255',
            'description' => 'sometimes|string|min:10',
            'price' => 'sometimes|numeric|min:0',
            'category_id' => 'sometimes|exists:categories,id',
            'images.*' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'stock' => 'sometimes|integer|min:0',
        ];
    }
}