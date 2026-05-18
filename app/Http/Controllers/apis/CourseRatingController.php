<?php

namespace App\Http\Controllers\apis;

use App\Http\Requests\Api\SubmitCourseRatingRequest;
use App\Http\Resources\CourseRatingResource;
use App\Models\Course;
use App\Models\CourseRating;
use App\Services\CourseRatingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseRatingController extends ApiController
{
    public function __construct(private readonly CourseRatingService $ratingService) {}

    /** Admin: paginated list of ratings for a course. */
    public function index(Request $request, Course $course): JsonResponse
    {
        $ratings = $this->ratingService->paginateForCourse(
            courseId: $course->id,
            perPage:  (int) $request->get('per_page', 20),
            userId:   $request->get('user_id'),
        );

        return $this->paginated(__('messages.retrieved'), CourseRatingResource::collection($ratings));
    }

    /** User: submit or update own rating for a course. */
    public function store(SubmitCourseRatingRequest $request, Course $course): JsonResponse
    {
        $rating = $this->ratingService->submitRating($course, $request->user()->id, $request->validated());

        return $this->success(__('messages.created'), new CourseRatingResource($rating->load('user')));
    }

    /** Admin: delete a rating. */
    public function destroy(Course $course, CourseRating $rating): JsonResponse
    {
        abort_if($rating->course_id !== $course->id, 404);

        $this->ratingService->delete($rating);

        return $this->deleted();
    }
}
