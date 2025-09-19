<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SubscriptionPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'interval' => strtolower($this->interval),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'duration'    => 'required|integer|min:1',
            'interval'    => ['required', Rule::in(['day', 'month', 'year'])],
            'is_active'   => 'boolean',
        ];

        if ($this->isMethod('post')) {
            $rules['name'] = 'required|string|max:255|unique:subscription_plans,name';
        } elseif ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['name'] = [
                'required',
                'string',
                'max:255',
                Rule::unique('subscription_plans', 'name')->ignore(
                    is_object($this->route('subscription_plan'))
                        ? $this->route('subscription_plan')->id
                        : $this->route('subscription_plan')
                )
            ];
        }

        return $rules;
    }
}
