<?php

namespace App\Http\Controllers\apis;

use App\Http\Requests\Api\DetachUserRequest;
use App\Http\Requests\Api\EnrollUsersRequest;
use App\Http\Requests\Api\SyncEnrollmentRequest;
use App\Http\Resources\UsersCourseResource;
use App\Models\Course;
use App\Services\OnlineEnrollmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnlineEnrollmentController extends ApiController
{
    public function __construct(private readonly OnlineEnrollmentService $enrollmentService) {}

    /** List users enrolled in an online course. */
    public function index(Request $request, Course $course): JsonResponse
    {
        $enrollments = $this->enrollmentService->paginate(
            courseId: $course->id,
            perPage:  (int) $request->get('per_page', 20),
            search:   $request->get('search'),
        );

        return $this->paginated(__('messages.retrieved'), UsersCourseResource::collection($enrollments));
    }

    /** Attach (add without removing) users to an online course. */
    public function store(EnrollUsersRequest $request, Course $course): JsonResponse
    {
        $this->enrollmentService->attach($course, $request->validated('user_ids'));

        return $this->created(__('messages.created'));
    }

    /**
     * Sync users for an online course (replaces current enrollment list).
     * Supports toggling for_public via { for_public: true, user_ids: [] }.
     */
    public function update(SyncEnrollmentRequest $request, Course $course): JsonResponse
    {
        $validated = $request->validated();

        if (array_key_exists('for_public', $validated)) {
            $this->enrollmentService->setPublic($course, (bool) $validated['for_public']);
        }

        if (array_key_exists('user_ids', $validated)) {
            $this->enrollmentService->sync($course, $validated['user_ids']);
        }

        return $this->success(__('messages.updated'));
    }

    /** Remove a single user from an online course. */
    public function destroy(DetachUserRequest $request, Course $course): JsonResponse
    {
        $this->enrollmentService->detach($course, $request->validated('user_id'));

        return $this->deleted();
    }
}
