<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BusinessStoreRequest extends FormRequest
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
            'store_name'     => 'required|string|max:50',
            'store_name_en'  => 'nullable|string|max:50',
            'address'        => 'required|string|max:100',
            'phone'          => 'required|string|max:50',
            'owner_name'     => 'required|string|max:50',
            'owner_phone'    => 'required|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'store_name.required'     => 'দোকানের নাম অবশ্যই দিতে হবে',
            'store_name_en.required'  => 'দোকানের নাম (ইংরেজিতে) অবশ্যই দিতে হবে',
            'address.required'        => 'ঠিকানা অবশ্যই দিতে হবে',
            'phone.required'          => 'ফোন নম্বর অবশ্যই দিতে হবে',
            'owner_name.required'     => 'মালিকের নাম অবশ্যই দিতে হবে',
            'owner_phone.required'    => 'মালিকের ফোন নম্বর অবশ্যই দিতে হবে',
        ];
    }
}
