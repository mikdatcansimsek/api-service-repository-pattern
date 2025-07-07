<?php

namespace App\Http\Requests;

class UserRegisterRequest extends CustomRequest
{
    

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-ZÇĞıİÖŞÜçğıiöşü\s]+$/' // Turkish characters allowed
            ],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                'unique:users,email'
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:100',
                'confirmed',
                $this->customRules()['password_strong'] // Strong password
            ],
            'phone' => [
                'sometimes',
                'string',
                $this->customRules()['phone_tr'] // Turkish phone format
            ],
            'date_of_birth' => [
                'sometimes',
                'date',
                'before:' . now()->subYears(13)->format('Y-m-d') // Must be 13+ years old
            ],
            'terms_accepted' => [
                'required',
                'accepted'
            ],
            'marketing_consent' => [
                'sometimes',
                'boolean'
            ]
        ];
    }
    public function messages(): array
    {
        return array_merge(parent::messages(), $this->customRuleMessages(), [
            'name.regex' => 'Ad alanı sadece harf ve boşluk karakterleri içerebilir.',
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'email.unique' => 'Bu e-posta adresi zaten kayıtlı.',
            'password.confirmed' => 'Şifre doğrulaması eşleşmiyor.',
            'date_of_birth.before' => 'En az 13 yaşında olmalısınız.',
            'terms_accepted.accepted' => 'Kullanım koşullarını kabul etmelisiniz.',
        ]);
    }

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        // Normalize email
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower($this->email)
            ]);
        }

        // Normalize phone number
        if ($this->has('phone')) {
            $phone = preg_replace('/[^0-9+]/', '', $this->phone);
            if (str_starts_with($phone, '0')) {
                $phone = '+90' . substr($phone, 1);
            }
            $this->merge(['phone' => $phone]);
        }

        // Set marketing consent default
        if (!$this->has('marketing_consent')) {
            $this->merge(['marketing_consent' => false]);
        }
    }
}
