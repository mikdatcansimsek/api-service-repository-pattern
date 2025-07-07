<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'category_id' => 'required|exists:categories,id',
            'is_active' => 'sometimes|boolean'
        ];
    }

    /**
     * Prepare the data for validation.
     * Slug otomatik oluştur
     */
    protected function prepareForValidation(): void
    {
        // Eğer slug gönderilmemişse, name'den otomatik oluştur
        if (!$this->has('slug') || empty($this->slug)) {
            $this->merge([
                'slug' => \Str::slug($this->name)
            ]);
        }

        // is_active default true yap
        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }
    }

    /**
     * Get custom error messages
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Ürün adı zorunludur.',
            'slug.unique' => 'Bu slug zaten kullanılmaktadır.',
            'description.required' => 'Ürün açıklaması zorunludur.',
            'price.required' => 'Fiyat zorunludur.',
            'price.numeric' => 'Fiyat sayısal olmalıdır.',
            'quantity.required' => 'Miktar zorunludur.',
            'sku.unique' => 'Bu SKU zaten kullanılmaktadır.',
            'category_id.required' => 'Kategori seçimi zorunludur.',
            'category_id.exists' => 'Seçilen kategori bulunamadı.',
        ];
    }
}