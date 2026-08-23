<?php

namespace App\Http\Requests;

use App\Models\IdentityType;
use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequestV2 extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Cast file_ids from strings to integers before validation runs.
     * Multipart/form-data (Swagger, HTML forms) always sends values as strings,
     * so 'exists:files,id' and 'integer' rules would fail without this.
     */
    protected function prepareForValidation(): void
    {
        $documents = $this->input('documents', []);

        if (is_array($documents)) {
            foreach ($documents as $index => $document) {
                if (!empty($document['file_ids']) && is_array($document['file_ids'])) {
                    $documents[$index]['file_ids'] = array_map('intval', $document['file_ids']);
                }
            }

            $this->merge(['documents' => $documents]);
        }
    }

    public function rules(): array
    {
        return [
            'documents'                    => ['nullable', 'array'],
            'documents.*.identity_type_id' => ['required_with:documents', 'integer', 'exists:identity_types,id'],
            'documents.*.files'            => ['nullable', 'array'],
            'documents.*.files.*'          => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'documents.*.file_ids'         => ['nullable', 'array'],
            'documents.*.file_ids.*'       => ['integer', 'exists:files,id'],

            'certificate_id' => ['nullable', 'integer', 'exists:identity_types,id'],
            'certificates'   => ['nullable', 'array'],
            'certificates.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],

            'profile_photo'  => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            $documents = $this->input('documents', []);

            foreach ($documents as $index => $document) {

                $identityTypeId = $document['identity_type_id'] ?? null;

                if (!$identityTypeId) {
                    continue;
                }

                $identityType = IdentityType::find($identityTypeId);

                if (!$identityType || !$identityType->is_required) {
                    continue;
                }

                $uploadedFiles   = $this->file("documents.$index.files", []);
                $uploadedCount   = is_array($uploadedFiles) ? count($uploadedFiles) : 0;

                $existingFileIds = $document['file_ids'] ?? [];
                $existingCount   = is_array($existingFileIds) ? count($existingFileIds) : 0;

                $totalCount = $uploadedCount + $existingCount;

                if ($totalCount !== (int) $identityType->total_documents) {
                    $validator->errors()->add(
                        "documents.$index.files",
                        "{$identityType->name} requires exactly {$identityType->total_documents} file(s). " .
                        "You provided {$totalCount} (new: {$uploadedCount}, kept: {$existingCount})."
                    );
                }
            }
        });
    }
}