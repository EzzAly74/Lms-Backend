<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for the "Add New User" modal from the 2026 Figma redesign.
 *
 * Captures bilingual full names, contact email, the chosen role bucket
 * (Admin · Instructor · Learner) and an optional job role.  Because the
 * three roles persist into separate tables, the `email` uniqueness check
 * is scoped to whichever table will receive the row.
 */
class AdminUserStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $role = strtolower((string) $this->input('role'));
        $table = match ($role) {
            'admin'      => 'admins',
            'instructor' => 'instructors',
            default      => 'users',
        };

        return [
            'name_en'         => ['required', 'string', 'max:255'],
            'name_ar'         => ['required', 'string', 'max:255'],
            'email'           => ['required', 'email', 'max:255', Rule::unique($table, 'email')],
            'role'            => ['required', Rule::in(['admin', 'instructor', 'learner'])],
            'job_title'       => ['nullable', 'string', 'max:255'],
            'department_name' => ['nullable', 'string', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:50'],
            'learner_type'    => ['nullable', Rule::in(['online', 'offline', 'hybrid'])],
        ];
    }
}
