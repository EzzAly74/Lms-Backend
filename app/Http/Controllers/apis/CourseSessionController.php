<?php

namespace App\Http\Controllers\apis;

use App\Http\Requests\Api\CourseSessionRequest;
use App\Http\Resources\CourseSessionResource;
use App\Models\Course;
use App\Models\CourseSession;
use App\Services\CourseSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseSessionController extends ApiController
{
    public function __construct(private readonly CourseSessionService $service) {}

    public function index(Course $course, Request $request): JsonResponse
    {
        $sessions = $this->service->paginate(
            $course,
            (int) $request->get('per_page', 20),
            $request->get('section_id') ? (int) $request->get('section_id') : null
        );
        return $this->paginated(__('messages.retrieved'), $sessions);
    }

    public function store(Course $course, CourseSessionRequest $request): JsonResponse
    {
        $session = $this->service->create($course, $request->validated());
        return $this->created(__('messages.created'), new CourseSessionResource($session));
    }

    public function update(Course $course, CourseSession $session, CourseSessionRequest $request): JsonResponse
    {
        abort_if($session->course_id !== $course->id, 404);
        $session = $this->service->update($session, $request->validated());
        return $this->success(__('messages.updated'), new CourseSessionResource($session));
    }

    public function destroy(Course $course, CourseSession $session): JsonResponse
    {
        abort_if($session->course_id !== $course->id, 404);
        $this->service->delete($session);
        return $this->deleted(__('messages.deleted'));
    }
}
