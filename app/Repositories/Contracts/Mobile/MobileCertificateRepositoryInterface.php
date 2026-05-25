<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Mobile;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Read surface for S-07 (Certificates).
 *
 * The legacy backend treats certificates as DERIVED rows (issued from
 * a passed `UserExam` or a submitted `UserCourseEvaluation`) — there's
 * no `user_certificates` table. This contract preserves that fact
 * while giving the mobile service a paginated, sortable view.
 */
interface MobileCertificateRepositoryInterface
{
    /**
     * Paginated list of certificates for the user (newest first).
     *
     * Each row is a normalised array with keys:
     *   - id              : compound key "exam:123" / "evaluation:456"
     *   - type            : 'exam' | 'evaluation'
     *   - course_id       : int
     *   - course_title    : localized string
     *   - issued_at       : ISO date string
     *   - rating          : ?int (the user's own course rating, if any)
     *   - rating_sentiment: ?string
     *
     * @return LengthAwarePaginator<array<string, mixed>>
     */
    public function paginate(User $user, string $locale, int $perPage): LengthAwarePaginator;

    /**
     * Top-N for the My Learning overview.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function preview(User $user, string $locale, int $limit): Collection;

    /**
     * Look up a single certificate by its compound id.
     * Returns the same shape as `paginate` rows, or `null`.
     *
     * @return array<string, mixed>|null
     */
    public function findById(User $user, string $compoundId, string $locale): ?array;
}
