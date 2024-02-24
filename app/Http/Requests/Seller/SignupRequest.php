<?php

namespace App\Http\Requests\Seller;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class SignupRequest extends FormRequest
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
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|string|email|unique:sellers,email',
            'password' => ['required', Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised()],
            'business_type' => 'required|string',
            'phone' => 'required_if:business_type,personal|string',
            'address' => 'required_if:business_type,personal|string',
            'city' => 'required_if:business_type,personal|string',
            'state' => 'required_if:business_type,personal|string',
            'zip_code' => 'string|nullable',
            'description' => 'string|nullable',
            'government_id_type' => 'required|string',
            'government_id' => 'required|file|mimes:jpg,jpeg,png|max:15000'
        ];
    }
}
