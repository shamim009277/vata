<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentHeadRequest extends FormRequest
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
            'name'  => 'required|string|max:100',
            'group' => 'nullable|string|max:100',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'নাম ফিল্ডটি অবশ্যই পূরণ করতে হবে।',
            'name.string'   => 'নাম অবশ্যই টেক্সট আকারে হতে হবে।',
            'name.max'      => 'নাম ১০০ অক্ষরের বেশি হতে পারবে না।',

            'group.string'  => 'গ্রুপ অবশ্যই টেক্সট আকারে হতে হবে।',
            'group.max'     => 'গ্রুপ ১০০ অক্ষরের বেশি হতে পারবে না।',
        ];
    }
}
