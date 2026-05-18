<?php

namespace App\Http\Controllers\apis;

use App\Http\Requests\Api\SubmitCourseEvaluationRequest;
use App\Http\Resources\EvaluationCategoryResource;
use App\Models\Course;
use App\Services\UserCourseEvaluationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserCourseEvaluationController extends ApiController
{
    public function __construct(private readonly UserCourseEvaluationService $evalService) {}

    /**
     * GET — Return the evaluation form (categories + questions) and
     * whether the user has already submitted it for this course.
     */
    public function show(Request $request, Course $course): JsonResponse
    {
        abort_if(!$course->is_evaluate, 404);

        return $this->success(__('messages.retrieved'), [
            'already_evaluated'    => $this->evalService->hasEvaluated($request->user()->id, $course->id),
            'evaluation_categories' => EvaluationCategoryResource::collection($this->evalService->getForm()),
        ]);
    }

    /**
     * POST — Submit course evaluation.
     * Mirrors EvaluationController::store() from the legacy web controller.
     */
    public function store(SubmitCourseEvaluationRequest $request, Course $course): JsonResponse
    {
        abort_if(!$course->is_evaluate, 403);

        if ($this->evalService->hasEvaluated($request->user()->id, $course->id)) {
            return $this->error(__('messages.already_evaluated'), 409);
        }

        $validated = $request->validated();

        $this->evalService->submit(
            $request->user(),
            $course,
            $validated['instructor_id'],
            $validated['questions'],
        );

        return $this->created(__('messages.created'));
    }
}
