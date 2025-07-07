<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

/**
 * Custom Base Request Class
 * 
 * Provides:
 * - Consistent API error responses
 * - Custom validation rules
 * - Centralized authorization
 * - Input sanitization
 * - Detailed error logging
 */
abstract class CustomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Override this method in child classes for specific authorization
     */
    public function authorize(): bool
    {
        return true; // Default: Allow all requests
    }

    /**
     * Get the validation rules that apply to the request.
     * Must be implemented by child classes
     */
    abstract public function rules(): array;

    /**
     * Get custom error messages for validation rules
     */
    public function messages(): array
    {
        return [
            'required' => ':attribute alanı zorunludur.',
            'string' => ':attribute bir metin olmalıdır.',
            'email' => ':attribute geçerli bir e-posta adresi olmalıdır.',
            'unique' => ':attribute zaten kullanılmaktadır.',
            'min' => ':attribute en az :min karakter olmalıdır.',
            'max' => ':attribute en fazla :max karakter olmalıdır.',
            'numeric' => ':attribute bir sayı olmalıdır.',
            'integer' => ':attribute bir tam sayı olmalıdır.',
            'confirmed' => ':attribute doğrulaması eşleşmiyor.',
            'exists' => 'Seçilen :attribute geçersizdir.',
            'in' => 'Seçilen :attribute geçersizdir.',
            'array' => ':attribute bir dizi olmalıdır.',
            'boolean' => ':attribute true veya false olmalıdır.',
            'date' => ':attribute geçerli bir tarih olmalıdır.',
            'image' => ':attribute bir resim dosyası olmalıdır.',
            'mimes' => ':attribute şu türlerden biri olmalıdır: :values.',
            'size' => ':attribute boyutu :size KB olmalıdır.',
        ];
    }

    /**
     * Get custom attribute names for error messages
     */
    public function attributes(): array
    {
        return [
            'name' => 'Ad',
            'email' => 'E-posta',
            'password' => 'Şifre',
            'password_confirmation' => 'Şifre Doğrulaması',
            'title' => 'Başlık',
            'description' => 'Açıklama',
            'content' => 'İçerik',
            'price' => 'Fiyat',
            'category_id' => 'Kategori',
            'status' => 'Durum',
            'image' => 'Resim',
            'file' => 'Dosya',
            'phone' => 'Telefon',
            'address' => 'Adres',
            'city' => 'Şehir',
            'country' => 'Ülke',
            'zip_code' => 'Posta Kodu',
        ];
    }

    /**
     * Handle a failed validation attempt.
     * Returns consistent API error response
     */
    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors()->toArray();
        
        // Log validation errors for debugging
        \Log::warning('Validation Failed', [
            'url' => $this->fullUrl(),
            'method' => $this->method(),
            'input' => $this->except(['password', 'password_confirmation']),
            'errors' => $errors,
            'user_id' => auth()->id(),
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent(),
        ]);

        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $errors,
                'error_code' => 'VALIDATION_ERROR',
                'timestamp' => now()->toISOString(),
                'path' => $this->getPathInfo(),
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization(): void
    {
        \Log::warning('Authorization Failed', [
            'url' => $this->fullUrl(),
            'method' => $this->method(),
            'user_id' => auth()->id(),
            'ip' => $this->ip(),
        ]);

        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'You are not authorized to perform this action.',
                'error_code' => 'AUTHORIZATION_ERROR',
                'timestamp' => now()->toISOString(),
                'path' => $this->getPathInfo(),
            ], JsonResponse::HTTP_FORBIDDEN)
        );
    }

    /**
     * Prepare the data for validation.
     * Clean and sanitize input data
     */
    protected function prepareForValidation(): void
    {
        $input = $this->all();

        // Trim whitespace from string inputs
        $input = array_map(function ($value) {
            return is_string($value) ? trim($value) : $value;
        }, $input);

        // Remove empty strings (convert to null)
        $input = array_map(function ($value) {
            return $value === '' ? null : $value;
        }, $input);

        // Convert string booleans to actual booleans
        foreach (['active', 'enabled', 'is_active', 'status'] as $field) {
            if (isset($input[$field])) {
                if (in_array(strtolower($input[$field]), ['true', '1', 'yes', 'on'])) {
                    $input[$field] = true;
                } elseif (in_array(strtolower($input[$field]), ['false', '0', 'no', 'off'])) {
                    $input[$field] = false;
                }
            }
        }

        $this->replace($input);
    }

    /**
     * Get validated data with additional processing
     */
    public function validatedData(): array
    {
        $validated = $this->validated();
        
        // Add timestamp
        $validated['validated_at'] = now();
        
        // Add user context if authenticated
        if (auth()->check()) {
            $validated['validated_by'] = auth()->id();
        }

        return $validated;
    }

    /**
     * Custom validation rules that can be used in child classes
     */
    protected function customRules(): array
    {
        return [
            'phone_tr' => 'regex:/^(\+90|0)?[5][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]$/',
            'slug' => 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'password_strong' => 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
        ];
    }

    /**
     * Custom validation messages for custom rules
     */
    protected function customRuleMessages(): array
    {
        return [
            'phone_tr' => ':attribute geçerli bir Türkiye telefon numarası olmalıdır.',
            'slug' => ':attribute sadece küçük harf, sayı ve tire içerebilir.',
            'password_strong' => ':attribute en az bir büyük harf, bir küçük harf, bir sayı ve bir özel karakter içermelidir.',
        ];
    }
}