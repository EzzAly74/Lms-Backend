<?php

namespace App\Http\Controllers\apis;

use App\Http\Resources\CourseLectureQuestionResource;
use App\Models\Course;
use App\Models\CourseLecture;
use App\Models\CourseLectureQuestion;
use App\Services\CourseLectureQuestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseLectureQuestionController extends ApiController
{
    public function __construct(private readonly CourseLectureQuestionService $questionService) {}

    /** Admin: list questions with optional filters. */
    public function index(Request $request): JsonResponse
    {
        $filters = array_filter([
            'course_id'  => $request->get('course_id'),
            'lecture_id' => $request->get('lecture_id'),
            'user_id'    => $request->get('user_id'),
            'answered'   => $request->has('answered') ? filter_var($request->get('answered'), FILTER_VALIDATE_BOOLEAN) : null,
        ], fn ($v) => $v !== null);

        $questions = $this->questionService->paginate(
            (int) $request->get('per_page', 20),
            $filters,
        );

        return $this->paginated(__('messages.retrieved'), CourseLectureQuestionResource::collection($questions));
    }

    /** User: submit a question on a lecture. */
    public function store(Request $request, Course $course, CourseLecture $lecture): JsonResponse
    {
        abort_if($lecture->course_id !== $course->id, 404);

        $data = $request->validate([
            'question' => 'required|string|max:2000',
        ]);

        $question = $this->questionService->submit([
            'question'   => $data['question'],
            'course_id'  => $course->id,
            'lecture_id' => $lecture->id,
            'user_id'    => $request->user()->id,
        ]);

        return $this->created(__('messages.created'), new CourseLectureQuestionResource($question));
    }

    /** Admin: post an answer to a question. */
    public function answer(Request $request, CourseLectureQuestion $question): JsonResponse
    {
        $data = $request->validate([
            'answer' => 'required|string|max:5000',
        ]);

        $question = $this->questionService->answer($question, $request->user()->id, $data['answer']);

        return $this->success(__('messages.updated'), new CourseLectureQuestionResource($question->load(['user', 'lecture', 'answeredBy'])));
    }

    /** Admin: delete a question. */
    public function destroy(CourseLectureQuestion $question): JsonResponse
    {
        $this->questionService->delete($question);
        return $this->deleted();
    }
}
