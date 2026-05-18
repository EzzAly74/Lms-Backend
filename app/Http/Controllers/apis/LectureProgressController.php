<?php

namespace App\Http\Controllers\apis;

use App\Http\Requests\Api\LectureProgressRequest;
use App\Models\Course;
use App\Models\CourseLecture;
use App\Services\LectureProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LectureProgressController extends ApiController
{
    public function __construct(private readonly LectureProgressService $progressService) {}

    /**
     * User: report watch progress for a lecture.
     * Auto-marks as completed at 90%+.
     */
    public function store(LectureProgressRequest $request, Course $course, CourseLecture $lecture): JsonResponse
    {
        abort_if($lecture->course_id !== $course->id, 404);

        $progress = $this->progressService->track(
            $request->user()->id,
            $lecture->id,
            $request->validated('progress'),
        );

        return $this->success(__('messages.updated'), [
            'lecture_id' => $progress->lecture_id,
            'progress'   => $progress->progress,
            'completed'  => (bool) $progress->completed,
        ]);
    }

    /**
     * User: get overall course completion % and per-lecture breakdown.
     */
    public function show(Request $request, Course $course): JsonResponse
    {
        $userId = $request->user()->id;

        return $this->success(__('messages.retrieved'), [
            'course_id'       => $course->id,
            'overall_progress' => $this->progressService->getCourseProgress($userId, $course->id),
            'lectures'         => $this->progressService->getLectureProgress($userId, $course->id),
        ]);
    }
}
