<?php

namespace App\Http\Requests;

use App\Data\ContactSubmissionData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:120',
            ],
            'phone' => [
                'required',
                'string',
                'min:7',
                'max:32',
                'regex:/^\+?[0-9\s().-]+$/',
            ],
            'email' => [
                'required',
                'string',
                'max:255',
                Rule::email()->rfcCompliant(strict: true),
            ],
            'comment' => [
                'required',
                'string',
                'min:10',
                'max:4000',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'The phone number format is invalid.',
        ];
    }

    public function toData(): ContactSubmissionData
    {
        /** @var array{
         *     name: string,
         *     phone: string,
         *     email: string,
         *     comment: string
         * } $validated
         */
        $validated = $this->validated();

        return new ContactSubmissionData(
            name: $validated['name'],
            phone: $validated['phone'],
            email: $validated['email'],
            comment: $validated['comment'],
        );
    }

    protected function prepareForValidation(): void
    {
        $name = $this->input('name');
        $phone = $this->input('phone');
        $email = $this->input('email');
        $comment = $this->input('comment');

        $this->merge([
            'name' => is_string($name)
                ? preg_replace('/\s+/u', ' ', trim($name))
                : $name,
            'phone' => is_string($phone)
                ? trim($phone)
                : $phone,
            'email' => is_string($email)
                ? mb_strtolower(trim($email))
                : $email,
            'comment' => is_string($comment)
                ? trim($comment)
                : $comment,
        ]);
    }
}
