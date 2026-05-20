<?php

namespace App\Http\Controllers\apis;

use App\Models\UserExam;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuizController extends ApiController
{
    /**
     * Paginated list of all user exam attempts (admin only).
     * Supports filters: ?course_id, ?user_id
     */
    public function index(Request $request): JsonResponse
    {
        $perPage      = (int) $request->get('per_page', 20);
        $courseId     = $request->get('course_id') ? (int) $request->get('course_id') : null;
        $userId       = $request->get('user_id') ? (int) $request->get('user_id') : null;

        $exams = UserExam::query()
            ->with([
                'user:id,name',
                'course:id,title',
            ])
            ->when($courseId, fn ($q) => $q->where('course_id', $courseId))
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->latest()
            ->paginate($perPage);

        $resource = $exams->through(fn ($exam) => [
            'id'         => $exam->id,
            'user'       => [
                'id'   => $exam->user?->id,
                'name' => $exam->user?->name,
            ],
            'course'     => [
                'id'    => $exam->course?->id,
                'title' => $exam->course?->getTranslation('title', app()->getLocale()),
            ],
            'score'      => $exam->score,
            'status'     => $exam->status,
            'created_at' => $exam->created_at?->toDateTimeString(),
        ]);

        return $this->paginated(__('messages.retrieved'), $resource);
    }
}
