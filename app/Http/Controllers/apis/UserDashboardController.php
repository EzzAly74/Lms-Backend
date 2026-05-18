<?php

namespace App\Http\Controllers\apis;

use App\Http\Resources\CourseResource;
use App\Http\Resources\UserExamResource;
use App\Models\User;
use App\Services\LectureProgressService;
use App\Services\UserDashboardService;
use App\Services\UserExamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserDashboardController extends ApiController
{
    public function __construct(
        private readonly UserDashboardService  $dashboardService,
        private readonly UserExamService       $examService,
        private readonly LectureProgressService $progressService,
    ) {}

    /** GET /my/dashboard — personal stats summary. */
    public function dashboard(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return $this->success(
            __('messages.retrieved'),
            $this->dashboardService->getStats($user),
        );
    }

    /** GET /my/courses — enrolled + public courses. */
    public function myCourses(Request $request): JsonResponse
    {
        /** @var User $user */
        $user    = $request->user();
        $courses = $this->dashboardService->getMyCourses($user);

        return $this->success(__('messages.retrieved'), CourseResource::collection($courses));
    }

    /** GET /my/exams — user's own exam history. */
    public function myExams(Request $request): JsonResponse
    {
        $exams = $this->examService->getUserExams($request->user()->id);

        return $this->success(__('messages.retrieved'), UserExamResource::collection($exams));
    }

    /** GET /my/exams/{id} — exam result with full answer breakdown. */
    public function myExam(Request $request, int $id): JsonResponse
    {
        $exam = $this->examService->getUserExam($request->user()->id, $id);

        if (!$exam) {
            return $this->notFound();
        }

        return $this->success(__('messages.retrieved'), new UserExamResource($exam));
    }

    /** GET /my/assignments — assignments across enrolled courses with submission status. */
    public function myAssignments(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return $this->success(
            __('messages.retrieved'),
            $this->dashboardService->getMyAssignments($user),
        );
    }

    /** GET /my/certificates — earned certificates. */
    public function myCertificates(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $locale = app()->getLocale();
        $certificates = $this->dashboardService->getCertificates($user)->map(fn ($cert) => [
            'type'       => $cert['type'],
            'course_id'  => $cert['data']->course?->id,
            'course'     => $cert['data']->course?->getTranslation('title', $locale),
            'earned_at'  => $cert['data']->created_at?->toDateString(),
        ]);

        return $this->success(__('messages.retrieved'), $certificates);
    }

    /** GET /my/ratings — user's own course ratings. */
    public function myRatings(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return $this->success(
            __('messages.retrieved'),
            $this->dashboardService->getMyRatings($user->id),
        );
    }

    /** GET /my/lecture-questions — user's own lecture questions with answers. */
    public function myLectureQuestions(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return $this->success(
            __('messages.retrieved'),
            $this->dashboardService->getMyLectureQuestions($user->id),
        );
    }

    /** GET /my/progress/{course} — course completion % with per-lecture detail. */
    public function myProgress(Request $request, int $courseId): JsonResponse
    {
        $userId = $request->user()->id;

        return $this->success(__('messages.retrieved'), [
            'course_id'        => $courseId,
            'overall_progress' => $this->progressService->getCourseProgress($userId, $courseId),
            'lectures'         => $this->progressService->getLectureProgress($userId, $courseId),
        ]);
    }
}
