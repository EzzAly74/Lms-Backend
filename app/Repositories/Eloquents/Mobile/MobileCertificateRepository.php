<?php

declare(strict_types=1);

namespace App\Repositories\Eloquents\Mobile;

use App\Models\User;
use App\Models\UserCourseEvaluation;
use App\Models\UserExam;
use App\Repositories\Contracts\Mobile\MobileCertificateRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

/**
 * Read-only repository that *derives* the certificate list per user
 * from the same two sources the legacy `UserDashboardService` uses:
 *
 *   - UserExam     where exam.is_final = true AND status = success
 *                 AND course.certificate = true AND course.is_evaluate = false
 *   - UserCourseEvaluation where course.is_evaluate = true
 *
 * That keeps mobile + admin in perfect lock-step — no separate
 * `user_certificates` table to keep in sync.
 *
 * Output rows carry a *compound id* (`exam:123` / `evaluation:456`)
 * so the certificate download endpoint can route back to the source.
 */
final class MobileCertificateRepository implements MobileCertificateRepositoryInterface
{
    public function paginate(User $user, string $locale, int $perPage): LengthAwarePaginator
    {
        $collection = $this->collectAll($user, $locale);

        $page  = (int) max(1, request()->integer('page', 1));
        $items = $collection->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            items: $items,
            total: $collection->count(),
            perPage: $perPage,
            currentPage: $page,
            options: [
                'path'     => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ],
        );
    }

    public function preview(User $user, string $locale, int $limit): Collection
    {
        return $this->collectAll($user, $locale)->take($limit)->values();
    }

    public function findById(User $user, string $compoundId, string $locale): ?array
    {
        if (! str_contains($compoundId, ':')) {
            return null;
        }

        [$type, $id] = explode(':', $compoundId, 2);
        $id = (int) $id;

        if ($id <= 0) {
            return null;
        }

        $match = $this->collectAll($user, $locale)->first(
            fn (array $row) => $row['type'] === $type && (int) $row['source_id'] === $id,
        );

        return $match;
    }

    /**
     * Materialise the full certificate set for the user.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function collectAll(User $user, string $locale): Collection
    {
        $userId = $user->id;

        $examCertificates = UserExam::query()
            ->with([
                'course:id,title,certificate,title_for_certificate,is_evaluate',
                'exam:id,title,degree,is_final',
            ])
            ->where('user_id', $userId)
            ->where('status', 'success')
            ->whereHas('course', fn ($q) => $q->where('certificate', true))
            ->whereHas('exam',   fn ($q) => $q->where('is_final', true))
            ->get();

        $evaluationCertificates = UserCourseEvaluation::query()
            ->with(['course:id,title,is_evaluate'])
            ->where('user_id', $userId)
            ->get();

        $rows = collect();

        foreach ($examCertificates as $exam) {
            $course = $exam->course;
            if ($course === null || $course->is_evaluate) {
                continue; // Evaluation-driven courses use the other source.
            }

            $rows->push([
                'id'           => "exam:{$exam->id}",
                'source_id'    => (int) $exam->id,
                'type'         => 'exam',
                'course_id'    => (int) $course->id,
                'course_title' => $this->localizedTitle($course, $locale),
                'issued_at'    => $exam->created_at?->toIso8601String(),
                'issued_date'  => $exam->created_at?->toDateString(),
                'user_rating'  => null, // hydrated by the service if needed
                // HR-sourced business identity printed on the certificate.
                'learner_machine_code' => $user->machine_code,
                'learner_name'         => $user->name,
            ]);
        }

        foreach ($evaluationCertificates as $evaluation) {
            $course = $evaluation->course;
            if ($course === null || ! $course->is_evaluate) {
                continue;
            }

            // De-dupe: if the same course already produced an exam row
            // we keep the earlier insertion (it's the canonical proof).
            $alreadyAdded = $rows->contains(
                fn (array $r) => (int) $r['course_id'] === (int) $course->id,
            );
            if ($alreadyAdded) {
                continue;
            }

            $rows->push([
                'id'           => "evaluation:{$evaluation->id}",
                'source_id'    => (int) $evaluation->id,
                'type'         => 'evaluation',
                'course_id'    => (int) $course->id,
                'course_title' => $this->localizedTitle($course, $locale),
                'issued_at'    => $evaluation->created_at?->toIso8601String(),
                'issued_date'  => $evaluation->created_at?->toDateString(),
                'user_rating'  => null,
                'learner_machine_code' => $user->machine_code,
                'learner_name'         => $user->name,
            ]);
        }

        return $rows->sortByDesc('issued_at')->values();
    }

    private function localizedTitle(\App\Models\Course $course, string $locale): string
    {
        return (string) ($course->getTranslation('title', $locale) ?? '');
    }
}
