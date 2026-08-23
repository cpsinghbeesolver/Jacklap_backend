<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProfessionalDetailRequest extends FormRequest
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
            'service_category_id' => ['required', 'exists:service_categories,id'],
            'price' => ['nullable', 'numeric','min:0'],
            'experience' => ['required', 'numeric', 'min:0'],
            'languages' => ['required', 'string'],
            'tools' => ['nullable', 'string'],
            'skills' => ['nullable', 'string'],

            'teaching_mode' => [
                'nullable',
                'required_if:service_category_id,3',
                'in:1,2,3'
            ],
            
            'safety_record' => [
                'nullable',
                'required_if:service_category_id,5',
                'string'
            ],
            'transmission_type' => [
                'nullable',
                'required_if:service_category_id,5',
                'in:0,1,2,3' // 0=none,1=auto,2=manual,3=both
            ],
            'license_type_ids' => [
                'nullable',
                'required_if:service_category_id,5',
                'array'
            ],

            'license_type_ids.*' => [
                'integer',
                'exists:license_types,id'
            ],

            'service_usecase_ids' => [
                'nullable',
                'required_if:service_category_id,5',
                'array'
            ],

            'service_usecase_ids.*' => [
                'integer',
                'exists:service_use_cases,id'
            ],

            'material_type_ids' => [
                'nullable',
                'required_if:service_category_id,1',
                'array'
            ],

            'material_type_ids.*' => [
                'integer',
                'exists:material_types,id'
            ],

            'services' => ['nullable', 'required_if:service_category_id,1,2,3,4', 'array'],
            'services.*.service_id'   => ['nullable', 'integer', 'exists:master_services,id'],
            'services.*.name' => ['required', 'string'],
            'services.*.type'         => ['nullable', 'string', 'in:service,package,specialization,product'],
            'services.*.subject_type' => ['nullable', 'integer', 'in:1,2,3,4,5'],
            'services.*.price' => ['required', 'numeric', 'min:0'],
            'services.*.description' => ['nullable', 'string'],
            'services.*.is_default' => ['nullable', 'boolean'],
            'services.*.min_people'   => ['nullable', 'integer', 'min:1'],
            'services.*.max_people'   => ['nullable', 'integer', 'min:1'],
            // 'services.*.subject_type' => [
            //     'nullable',
            //     'required_if:service_category_id,3',
            //     'in:1,2'
            // ],
            'services.*.classes' => [
                'nullable',
                'array'
            ],

            'services.*.classes.*.class_name' => [
                'required_with:services.*.classes',
                'string'
            ],

            'services.*.classes.*.price' => [
                'required_with:services.*.classes',
                'numeric',
                'min:0'
            ],
            /**
             * SERVICE CATEGORY ID = 1
             */
            'services.*.items' => [
                'nullable',
                // 'required_if:service_category_id,1',
                'array'
            ],

            'services.*.items.*.id' => [
                'required_with:services.*.items',
                'integer',
                'exists:master_service_items,id'
            ],

            'services.*.items.*.name' => [
                'required_with:services.*.items',
                'string'
            ],

            'services.*.items.*.price' => [
                'required_with:services.*.items',
                'numeric',
                'min:0'
            ],
            'addon_services' => ['nullable', 'array'],
            'addon_services.*.name' => ['required_with:addon_services', 'string', 'max:255'],
            'addon_services.*.price' => ['required_with:addon_services', 'numeric', 'min:0'],
        ];
    }
}
