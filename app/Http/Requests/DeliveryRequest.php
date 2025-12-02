<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeliveryRequest extends FormRequest
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
            'invoice_no' => 'required',
            'delivery_no' => 'required',
            'customer_name' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'delivery_date' => 'required|date',
            'note' => 'required|string|nullable',
            'item_id' => 'required',
            'customer_id' => 'required',
            'delivery_qty' => 'required|numeric',
            'today_delivery_qty' => 'required|numeric',
            'remaining_delivery_qty' => 'required|numeric',
            'driver_name' => 'required',
            'driver_phone' => 'required',
            'truck_number' => 'required',
            'delivery_rant' => 'nullable|numeric',
        ];
    }

    public function messages()
    {
        return [
            'invoice_no.required' => 'ইনভয়েস নম্বর প্রদান করা বাধ্যতামূলক।',
            'delivery_no.required' => 'ডেলিভারি নম্বর প্রদান করা বাধ্যতামূলক।',

            'customer_name.required' => 'গ্রাহকের নাম প্রদান করুন।',
            'phone.required' => 'গ্রাহকের ফোন নম্বর প্রদান করুন।',
            'address.required' => 'ঠিকানা প্রদান করুন।',

            'delivery_date.required' => 'ডেলিভারির তারিখ প্রদান করুন।',
            'delivery_date.date' => 'ডেলিভারির তারিখ সঠিক ফরম্যাটে নয়।',

            'note.string' => 'নোট অবশ্যই স্ট্রিং হতে হবে।',
            'note.required' => 'নোট অবশ্যই প্রদান করুন।',

            'item_id.required' => 'আইটেম নির্বাচন করুন।',
            'customer_id.required' => 'গ্রাহক নির্বাচন করুন।',

            'delivery_qty.required' => 'মোট ডেলিভারি পরিমাণ প্রদান করুন।',
            'delivery_qty.numeric' => 'মোট ডেলিভারি পরিমাণ সংখ্যায় হতে হবে।',

            'today_delivery_qty.required' => 'আজকের ডেলিভারি পরিমাণ প্রদান করুন।',
            'today_delivery_qty.numeric' => 'আজকের ডেলিভারি পরিমাণ সংখ্যায় হতে হবে।',

            'remaining_delivery_qty.required' => 'বাকি ডেলিভারির পরিমাণ প্রদান করুন।',
            'remaining_delivery_qty.numeric' => 'বাকি ডেলিভারির পরিমাণ সংখ্যায় হতে হবে।',

            'driver_name.required' => 'ড্রাইভারের নাম প্রদান করুন।',
            'driver_phone.required' => 'ড্রাইভারের ফোন নম্বর প্রদান করুন।',
            'truck_number.required' => 'ট্রাক নম্বর প্রদান করুন।',

            'delivery_rant.numeric' => 'ডেলিভারি ভাড়া সংখ্যায় হতে হবে।',
        ];
    }
}
