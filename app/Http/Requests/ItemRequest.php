<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemRequest extends FormRequest
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
            'name' => 'required|string|max:50',
            'type' => 'required|string|max:50',
            'rate' => 'required|numeric',
            'is_active' => 'required|integer|in:0,1',
        ];
    }


    public function messages(): array
    {
        return [
            'name.required' => 'নাম ফিল্ডটি আবশ্যক।',
            'name.string' => 'নাম অবশ্যই টেক্সট হতে হবে।',
            'name.max' => 'নামের দৈর্ঘ্য সর্বোচ্চ ৫০ অক্ষর হতে পারবে।',

            'type.required' => 'টাইপ ফিল্ডটি আবশ্যক।',
            'type.string' => 'টাইপ অবশ্যই টেক্সট হতে হবে।',
            'type.max' => 'টাইপের দৈর্ঘ্য সর্বোচ্চ ৫০ অক্ষর হতে পারবে।',

            'rate.required' => 'রেট ফিল্ডটি আবশ্যক।',
            'rate.numeric' => 'রেট অবশ্যই একটি সংখ্যা হতে হবে।',

            'is_active.required' => 'স্ট্যাটাস নির্বাচন করা আবশ্যক।',
            'is_active.boolean' => 'স্ট্যাটাসের মান সঠিক নয়।',
        ];
    }
}
