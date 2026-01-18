<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoadingRequest extends FormRequest
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
            'loading_date'   => 'required|date',
            'load_type'      => 'required|in:1,2,3',
            'field_list_id'  => 'nullable|integer',
            'item_id'        => 'nullable|integer',
            'quantity'       => 'required|numeric|min:1',
            'round'       => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'loading_date.required'  => 'লোডিং তারিখ অবশ্যই দিতে হবে।',
            'loading_date.date'      => 'সঠিক তারিখ দিন।',

            'load_type.required'     => 'লোডের ধরন সিলেক্ট করুন।',
            'load_type.in'           => 'সঠিক লোড টাইপ নির্বাচন করুন।',

            'quantity.required'      => 'পরিমাণ দিতে হবে।',
            'quantity.numeric'       => 'পরিমাণ অবশ্যই সংখ্যা হতে হবে।',
            'quantity.min'           => 'পরিমাণ ন্যূনতম ১ হতে হবে।',

            'round.required'      => 'রাউন্ড নির্বাচন করুন।',
        ];
    }
}
