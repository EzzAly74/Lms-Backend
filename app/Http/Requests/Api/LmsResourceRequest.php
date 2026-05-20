<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class LmsResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // On update (PUT) the file field is never mandatory — the existing file is kept.
        $isUpdate   = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $fileRules  = $isUpdate
            ? ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx', 'max:51200']
            : ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx', 'max:51200', 'required_if:type,file'];

        return [
            'title'                  => ['required', 'string', 'max:255'],
            'type'                   => ['required', 'in:article,link,file'],
            'content'                => ['nullable', 'string', 'required_if:type,article'],
            'url'                    => ['nullable', 'url', 'required_if:type,link'],
            'file'                   => $fileRules,
            'qualification_skill_id' => ['nullable', 'integer', 'exists:qualification_skills,id'],
        ];
    }
}
