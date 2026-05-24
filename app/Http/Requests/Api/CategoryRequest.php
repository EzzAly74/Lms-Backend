<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /**
         * `_method=PUT` is the canonical pattern Laravel uses to map a
         * multipart `POST` to an `update()` action — required because PHP
         * cannot parse `multipart/form-data` bodies on non-POST verbs.
         * We treat anything other than a real `POST` (or a spoofed PUT/
         * PATCH via `_method`) as an update, so the logo stays optional
         * when only metadata changes.
         */
        $isCreate = $this->isMethod('post') && !in_array(
            strtoupper((string) $this->input('_method', '')),
            ['PUT', 'PATCH'],
            true,
        );

        $logoRule = $isCreate
            ? 'required|image|mimes:png,jpg,jpeg,webp,svg|max:2048'
            : 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:2048';

        return [
            /**
             * `Category::$translatable = ['name']` (Spatie) — admins submit
             * the localised pair `name[en]` / `name[ar]` from the bilingual
             * dialog. Mirrors `PublicNotificationRequest::rules()`.
             */
            'name'    => 'required|array',
            'name.ar' => 'required|string|max:255',
            'name.en' => 'nullable|string|max:255',
            'active'  => 'nullable|boolean',
            'logo'    => $logoRule,
        ];
    }
}
