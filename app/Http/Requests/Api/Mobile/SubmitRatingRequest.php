<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Mobile;

use App\Services\Mobile\MobileSettings;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validate cohort feedback (Figma 543:41637 → 543:41844).
 *
 * Every constraint is settings-driven:
 *   - min/max value           ← rating_min_value / rating_max_value
 *   - comment required cutoff ← rating_comment_required_at_or_below
 *   - comment max length      ← rating_comment_max_length
 */
final class SubmitRatingRequest extends FormRequest
{
    public function __construct(
        private readonly MobileSettings $settings,
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        $content = null,
    ) {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $min = $this->settings->ratingMinValue();
        $max = $this->settings->ratingMaxValue();
        $commentMax = $this->settings->ratingCommentMaxLength();

        return [
            'rating'  => ['required', 'integer', "min:{$min}", "max:{$max}"],
            'comment' => ['nullable', 'string', "max:{$commentMax}"],
        ];
    }

    /**
     * Conditional rule — for ratings at or below the configured cutoff,
     * the comment must be present and non-empty.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $rating  = (int) $this->input('rating', 0);
            $cutoff  = $this->settings->ratingCommentRequiredAtOrBelow();
            $comment = trim((string) $this->input('comment', ''));

            if ($rating > 0 && $rating <= $cutoff && $comment === '') {
                $v->errors()->add(
                    'comment',
                    __('validation.required', ['attribute' => 'comment']),
                );
            }
        });
    }
}
