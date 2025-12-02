<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FieldRequest extends FormRequest
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
            'name' => 'required|string|max:100|unique:field_lists,name',
            'location' => 'nullable|string|max:100',
            'is_active' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'নাম ফিল্ডটি আবশ্যক।',
            'name.string' => 'নাম অবশ্যই টেক্সট হতে হবে।',
            'name.max' => 'নামের দৈর্ঘ্য সর্বোচ্চ ১০০ অক্ষর হতে পারবে।',
            'name.unique' => 'নাম ইতিমধ্যে ব্যবহার করা হয়েছে।',
            'location.string' => 'লোকেশন অবশ্যই টেক্সট হতে হবে।',
            'location.max' => 'লোকেশনের দৈর্ঘ্য সর্বোচ্চ ১০০ অক্ষর হতে পারবে।',
            'is_active.required' => 'স্ট্যাটাস ফিল্ডটি আবশ্যক।',
            'is_active.boolean' => 'স্ট্যাটাসের মান সঠিক নয়।',
        ];
    }
}
