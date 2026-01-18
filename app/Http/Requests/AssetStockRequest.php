<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use SebastianBergmann\Type\TrueType;

class AssetStockRequest extends FormRequest
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
            'product_name' => 'required|string|max:255',
            'product_category' => 'required|string|max:255',
            'vendor' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:1',
            'unit_price' => 'required|numeric|min:0',
            'total_price' => 'nullable|numeric',
            'location' => 'nullable|string|max:255',
            'has_warranty' => 'required|boolean',
            'warranty_expiry_date' => 'required_if:has_warranty,1|nullable|date|after:today',
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }


    public function messages(): array
    {
        return [
            'product_name.required' => 'পণ্যের নাম অবশ্যই দিতে হবে।',
            'product_name.string'   => 'পণ্যের নাম অবশ্যই টেক্সট হতে হবে।',
            'product_name.max'      => 'পণ্যের নাম সর্বোচ্চ ২৫৫ অক্ষরের হতে পারে।',
            'product_category.required' => 'পণ্যের ক্যাটাগরি নির্বাচন করা বাধ্যতামূলক।',
            'product_category.string'   => 'পণ্যের ক্যাটাগরি সঠিক ফরম্যাটে নয়।',
            'vendor.required' => 'ভেন্ডরের নাম অবশ্যই দিতে হবে।',
            'vendor.string'   => 'ভেন্ডরের নাম অবশ্যই টেক্সট হতে হবে।',
            'quantity.required' => 'পণ্যের পরিমাণ দিতে হবে।',
            'quantity.numeric'  => 'পণ্যের পরিমাণ অবশ্যই সংখ্যায় হতে হবে।',
            'quantity.min'      => 'পণ্যের পরিমাণ কমপক্ষে ১ হতে হবে।',
            'unit_price.required' => 'ইউনিট মূল্য দিতে হবে।',
            'unit_price.numeric'  => 'ইউনিট মূল্য অবশ্যই সংখ্যায় হতে হবে।',
            'unit_price.min'      => 'ইউনিট মূল্য শূন্য বা তার বেশি হতে হবে।',
            'location.string' => 'লোকেশন সঠিক ফরম্যাটে নয়।',
            'has_warranty.required' => 'ওয়ারেন্টি আছে কিনা তা নির্বাচন করতে হবে।',
            'has_warranty.boolean'  => 'ওয়ারেন্টি ফিল্ডটি সঠিক নয়।',
            'warranty_expiry_date.required_if' => 'ওয়ারেন্টি থাকলে মেয়াদ শেষ হওয়ার তারিখ দিতে হবে।',
            'warranty_expiry_date.date'        => 'ওয়ারেন্টি মেয়াদের তারিখ সঠিক নয়।',
            'warranty_expiry_date.after'       => 'ওয়ারেন্টির তারিখ আজকের পরের হতে হবে।',
            'photo.required' => 'পণ্যের ছবি আপলোড করা বাধ্যতামূলক।',
            'photo.image'    => 'ফাইলটি অবশ্যই একটি ছবি হতে হবে।',
            'photo.mimes'    => 'ছবির ফরম্যাট jpg, jpeg, png অথবা webp হতে হবে।',
            'photo.max'      => 'ছবির সাইজ সর্বোচ্চ ২ এমবি হতে পারবে।',
        ];
    }
}
