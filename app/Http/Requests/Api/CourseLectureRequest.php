<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CourseLectureRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            // `section_id` is optional: the service will fall back to a
            // course-default section when omitted, since the admin "Module"
            // form intentionally hides section selection.
            'section_id'         => 'nullable|integer|exists:course_sections,id',

            'title'              => 'required|array',
            'title.ar'           => 'required|string|max:255',
            'title.en'           => 'nullable|string|max:255',

            'content_type'       => 'required|in:video,document,article,link',
            'learner_scope'      => 'required|in:all,cohort',
            'session_id'         => 'nullable|required_if:learner_scope,cohort|integer|exists:course_sessions,id',
            'duration_minutes'   => 'nullable|integer|min:0|max:10000',

            // `type` reflects how the `video` value is stored (url | file).
            // Defaults derived from `content_type` in the service if absent.
            'type'               => 'nullable|in:url,file',
            'video'              => 'required|string|max:2048',

            // Optional original filename for uploaded documents — preserved so
            // the Edit dialog can render "File Title.pdf · 313 KB" instead of a
            // storage hash. Ignored for URL-based content types.
            'file_name'          => 'nullable|string|max:255',

            'instructions'       => 'nullable|array',
            'instructions.en'    => 'nullable|string|max:1000',
            'instructions.ar'    => 'nullable|string|max:1000',

            'require_completion' => 'nullable|boolean',
        ];
    }
}
