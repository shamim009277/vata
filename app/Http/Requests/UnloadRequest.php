<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnloadRequest extends FormRequest
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
            'unload_date' => ['required', 'date'],
            'load.loading_date' => ['required', 'date'],
            'load.round' => ['required', 'string'],
            'load.total_quantity' => ['required', 'numeric', 'min:1'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'integer', 'exists:items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'unload_date.required' => 'আনলোড তারিখ দেওয়া বাধ্যতামূলক।',
            'unload_date.date' => 'আনলোড তারিখ সঠিক ফরম্যাটে দিন।',

            'load.required' => 'লোড তথ্য দেওয়া বাধ্যতামূলক।',

            'load.loading_date.required' => 'লোড তারিখ দেওয়া বাধ্যতামূলক।',
            'load.loading_date.date' => 'লোড তারিখ সঠিক ফরম্যাটে দিন।',

            'load.round.required' => 'রাউন্ড নির্বাচন করা বাধ্যতামূলক।',
            'load.round.string' => 'রাউন্ড সঠিক ফরম্যাটে দিন।',

            'load.total_quantity.required' => 'মোট লোড পরিমাণ দেওয়া বাধ্যতামূলক।',
            'load.total_quantity.numeric' => 'মোট লোড পরিমাণ অবশ্যই সংখ্যা হতে হবে।',
            'load.total_quantity.min' => 'মোট লোড পরিমাণ ১-এর বেশি হতে হবে।',

            'items.required' => 'কমপক্ষে একটি আইটেম নির্বাচন করতে হবে।',
            'items.array' => 'আইটেম তথ্য সঠিক ফরম্যাটে নেই।',
            'items.min' => 'কমপক্ষে একটি আইটেম যোগ করতে হবে।',

            'items.*.item_id.required' => 'আইটেম নির্বাচন করা বাধ্যতামূলক।',
            'items.*.item_id.exists' => 'নির্বাচিত আইটেমটি সঠিক নয়।',

            'items.*.quantity.required' => 'আইটেমের পরিমাণ দেওয়া বাধ্যতামূলক।',
            'items.*.quantity.numeric' => 'আইটেমের পরিমাণ অবশ্যই সংখ্যা হতে হবে।',
            'items.*.quantity.min' => 'আইটেমের পরিমাণ ১-এর বেশি হতে হবে।',
        ];
    }
}
