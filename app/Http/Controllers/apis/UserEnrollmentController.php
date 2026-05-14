<?php

namespace App\Http\Controllers\apis;

use App\Http\Requests\Api\CourseEnrollmentRequest;
use App\Models\Course;
use App\Models\UsersCourse;
use App\Services\UserEnrollmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserEnrollmentController extends ApiController
{
    public function __construct(private readonly UserEnrollmentService $service) {}

    public function index(Course $course, Request $request): JsonResponse
    {
        $enrollments = $this->service->paginate(
            $course,
            (int) $request->get('per_page', 20),
            $request->get('group_id') ? (int) $request->get('group_id') : null
        );
        return $this->paginated(__('messages.retrieved'), $enrollments);
    }

    public function store(Course $course, CourseEnrollmentRequest $request): JsonResponse
    {
        $data    = $request->validated();
        $enrolled = $this->service->enroll($course, $data['user_ids'], $data['group_id'] ?? null);
        return $this->success(__('messages.created'), ['enrolled' => $enrolled]);
    }

    public function destroy(Course $course, UsersCourse $enrollment): JsonResponse
    {
        abort_if($enrollment->course_id !== $course->id, 404);
        $this->service->remove($enrollment);
        return $this->deleted(__('messages.deleted'));
    }
}
