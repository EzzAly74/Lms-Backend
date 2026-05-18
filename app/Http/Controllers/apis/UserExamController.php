<?php

namespace App\Http\Controllers\apis;

use App\Http\Requests\Api\SubmitExamRequest;
use App\Http\Resources\UserExamResource;
use App\Models\Course;
use App\Models\CourseExam;
use App\Services\UserExamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserExamController extends ApiController
{
    public function __construct(private readonly UserExamService $examService) {}

    /** User: submit exam answers — auto-grade and return result. */
    public function submit(SubmitExamRequest $request, Course $course, CourseExam $exam): JsonResponse
    {
        abort_if($exam->course_id !== $course->id, 404);

        if ($this->examService->hasAlreadySubmitted($request->user()->id, $exam->id)) {
            return $this->error(__('messages.exam_already_submitted'), 409);
        }

        $userExam = $this->examService->submit(
            $request->user(),
            $course,
            $exam,
            $request->validated('questions'),
        );

        return $this->created(__('messages.created'), new UserExamResource($userExam));
    }

    /** User: list own exam history. */
    public function index(Request $request): JsonResponse
    {
        $exams = $this->examService->getUserExams($request->user()->id);

        return $this->success(__('messages.retrieved'), UserExamResource::collection($exams));
    }

    /** User: get own exam result with answers. */
    public function show(Request $request, int $id): JsonResponse
    {
        $exam = $this->examService->getUserExam($request->user()->id, $id);

        if (!$exam) {
            return $this->notFound();
        }

        return $this->success(__('messages.retrieved'), new UserExamResource($exam));
    }
}
