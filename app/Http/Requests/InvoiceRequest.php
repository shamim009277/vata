<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
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
            'invoice_no' => 'required|numeric',
            'invoice_date' => 'required|date',
            'phone' => 'required|string|max:20',
            'customer_name' => 'required|string|max:100',
            'address' => 'nullable|string|max:255',
            'type' => 'required|string|max:20',
            'delivery_date' => 'required|date',

            'sub_total' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'rant' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'due_amount' => 'nullable|numeric|min:0',

            // Nested items validation
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.amount' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'invoice_no.required' => 'চালান নম্বরটি দিতে হবে।',
            'invoice_date.required' => 'চালানের তারিখ দিতে হবে।',
            'customer_name.required' => 'গ্রাহকের নাম দিতে হবে।',
            'customer_name.string' => 'গ্রাহকের নাম স্ট্রিং হতে হবে।',
            'customer_name.max' => 'গ্রাহকের নাম মোট 50 অক্ষরের মধ্যে হতে হবে।',
            'phone.required' => 'গ্রাহকের ফোন নম্বর দিতে হবে।',

            'sub_total.required' => 'সাব টোটাল দিতে হবে।',
            'total_amount.required' => 'মোট টাকা দিতে হবে।',

            'items.required' => 'কমপক্ষে একটি আইটেম দিতে হবে।',
            'items.*.item_id.required' => 'আইটেমের ধরন দিতে হবে।',
            'items.*.rate.required' => 'রেট দিতে হবে।',
            'items.*.quantity.required' => 'পরিমাণ দিতে হবে।',
            'items.*.quantity.min' => 'পরিমাণ অন্তত ১ হতে হবে।',
            'items.*.amount.required' => 'মোট টাকা দিতে হবে।',
        ];
    }
}
