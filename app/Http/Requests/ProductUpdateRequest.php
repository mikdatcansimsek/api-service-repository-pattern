<?php

namespace App\Http\Requests;

class ProductUpdateRequest extends CustomRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $product = $this->route('product');
        return auth()->check() && (
            auth()->user()->can('update', $product) ||
            auth()->user()->hasRole('admin')
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $product = $this->route('product');
        return [
            'name' => [
                'sometimes',
                'string',
                'min:3',
                'max:255',
                'unique:products,name,' . $productId
            ],
            'description' => [
                'sometimes',
                'string',
                'min:10',
                'max:1000'
            ],
            'price' => [
                'sometimes',
                'numeric',
                'min:0.01',
                'max:999999.99'
            ],
            'category_id' => [
                'sometimes',
                'integer',
                'exists:categories,id'
            ],
            'status' => [
                'sometimes',
                'boolean'
            ],
            'tags' => [
                'sometimes',
                'array',
                'max:10'
            ],
            'tags.*' => [
                'string',
                'max:50'
            ],
            'image' => [
                'sometimes',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048'
            ]
        ];
    }
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'name.unique' => 'Bu ürün adı başka bir ürün tarafından kullanılmaktadır.',
        ]);
    }
}
