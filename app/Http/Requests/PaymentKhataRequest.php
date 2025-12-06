<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentKhataRequest extends FormRequest
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
            'payment_date'      => 'required|date',
            'payment_head_id'   => 'required|integer',
            'payment_type'      => 'required|in:regular,advance',
            'payment_details'   => 'nullable|string',
            'quantity'          => 'required|numeric|min:0',
            'amount'            => 'required|numeric|min:0',
            'paid_amount'       => 'required|numeric|min:0',
            'due_amount'        => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'payment_date.required'        => 'পেমেন্ট তারিখ অবশ্যই দিতে হবে।',
            'payment_date.date'            => 'পেমেন্ট তারিখ সঠিক নয়।',

            'payment_head_id.required'     => 'খতিয়ান সিলেক্ট করতে হবে।',
            'payment_head_id.integer'      => 'খতিয়ান সঠিক নয়।',

            'payment_type.required'        => 'পেমেন্টের ধরন নির্বাচন করুন।',
            'payment_type.in'              => 'পেমেন্টের ধরন অবশ্যই রেগুলার বা অগ্রিম হতে হবে।',

            'payment_details.string'       => 'পেমেন্টের বিবরণ সঠিক ফরম্যাটে দিন।',

            'quantity.required'            => 'পরিমাণ দিতে হবে।',
            'quantity.numeric'             => 'পরিমাণ সংখ্যা হতে হবে।',
            'quantity.min'                 => 'পরিমাণ নেগেটিভ হতে পারবে না।',

            'amount.required'              => 'মোট বিল দিতে হবে।',
            'amount.numeric'               => 'মোট বিল সংখ্যা হতে হবে।',
            'amount.min'                   => 'মোট বিল নেগেটিভ হতে পারবে না।',

            'paid_amount.required'         => 'পেমেন্টের পরিমাণ দিতে হবে।',
            'paid_amount.numeric'          => 'পেমেন্টের পরিমাণ সংখ্যা হতে হবে।',
            'paid_amount.min'              => 'পেমেন্টের পরিমাণ নেগেটিভ হতে পারবে না।',

            'due_amount.required'          => 'বাকি পরিমাণ প্রয়োজন।',
            'due_amount.numeric'           => 'বাকি পরিমাণ সংখ্যা হতে হবে।',
            'due_amount.min'               => 'বাকি পরিমাণ নেগেটিভ হতে পারবে না।',
        ];
    }

}
