<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RowProductionRequest extends FormRequest
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
            'product_date' => 'required|date',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.field_list_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'কমপক্ষে একটি মাঠ যোগ করুন।',
            'items.min' => 'কমপক্ষে একটি মাঠ প্রয়োজন।',
            'items.*.field_list_id.required' => 'শ্রেণি নির্বাচন করুন।',
            'items.*.quantity.min' => 'পরিমাণ কমপক্ষে ১ হতে হবে।',
        ];
    }
}
