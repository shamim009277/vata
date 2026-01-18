<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IssueRequest extends FormRequest
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
            'product_name' => 'required|string',
            'name' => 'required|string',
            'location' => 'required|string',
            'issue_date' => 'required|date',
            'quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string',
        ];
    }

     public function messages(): array
    {
        return [
            'product_name.required' => 'প্রোডাক্ট নাম প্রয়োজনীয়',
            'name.required' => 'নাম প্রয়োজনীয়',
            'location.required' => 'অবস্থান প্রয়োজনীয়',
            'issue_date.required' => 'ইস্যু তারিখ প্রয়োজনীয়',
            'quantity.required' => 'পরিমাণ প্রয়োজনীয়',
            'quantity.min' => 'পরিমাণ কমপক্ষে ১ হতে হবে',
        ];
    }
}
