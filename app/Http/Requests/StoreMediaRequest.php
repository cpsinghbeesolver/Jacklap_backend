<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
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
            'identity_type_id' => ['required', 'exists:identity_types,id' ],
            'id_front'         => ['nullable', 'image', 'mimes:png,jpg,jpeg,png'],
            'id_back'          => ['nullable', 'image', 'mimes:png,jpg,jpeg,png'],
            // 'certificate'      => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048' ], 
            'profile_photo'    => ['nullable', 'image', 'mimes:png,jpg,jpeg,png' ],
            'certificates' => 'nullable|array',
            'certificates.*' => 'file|mimes:jpg,jpeg,png,pdf',
        ];
    }
}
