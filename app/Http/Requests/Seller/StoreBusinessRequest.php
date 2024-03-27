<?php

namespace App\Http\Requests\Seller;

use Illuminate\Foundation\Http\FormRequest;

class StoreBusinessRequest extends FormRequest
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
            'business_type' => 'required|string',
            'business_name' => 'required_if:business_type,corporate',
            'email' => 'required_if:business_type,corporate|string|email|unique:seller_businesses,email',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',
            'registration_number' => 'required_if:business_type,corporate|string',
            'registration_certificate' => 'required_if:business_type,corporate|file|mimes:png,jpg,jpeg,pdf|max:15000',
            'phone' => 'required|string|unique:seller_businesses,phone',
            'government_id_type' => 'required_if:business_type,personal',
            'government_id' => 'required_if:business_type,personal|file|mimes:jpg,jpeg,png,pdf|max:15000'
        ];
    }
}
