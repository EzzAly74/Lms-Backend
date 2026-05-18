<?php

namespace App\Http\Controllers\apis;

use App\Http\Requests\Api\RecordAttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\Course;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\UserDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends ApiController
{
    public function __construct(
        private readonly AttendanceService      $attendanceService,
        private readonly UserDashboardService   $dashboardService,
    ) {}

    /** Admin: paginated attendance list with filters. */
    public function index(Request $request): JsonResponse
    {
        $filters = array_filter([
            'course_id'  => $request->get('course_id'),
            'user_id'    => $request->get('user_id'),
            'section_id' => $request->get('section_id'),
            'from'       => $request->get('from'),
            'to'         => $request->get('to'),
        ]);

        $attendance = $this->attendanceService->paginate(
            (int) $request->get('per_page', 20),
            $filters,
        );

        return $this->paginated(__('messages.retrieved'), AttendanceResource::collection($attendance));
    }

    /**
     * Admin: manually record (status=1) or remove (status=0) an attendance session.
     * Mirrors the legacy AttendanceController@store behavior.
     */
    public function store(RecordAttendanceRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user   = User::findOrFail($validated['user_id']);
        $course = Course::with('category')->findOrFail($validated['course_id']);

        if ($validated['status']) {
            $result = $this->attendanceService->record($user, $course);
        } else {
            $result = $this->attendanceService->remove($user, $course, $request->user()->id);
        }

        if (!$result['success']) {
            return $this->error($result['message'], 422);
        }

        return $this->success($result['message']);
    }

    /** User: view own attendance records for a specific course. */
    public function myCourseAttendance(Request $request, Course $course): JsonResponse
    {
        return $this->success(
            __('messages.retrieved'),
            $this->dashboardService->getUserCourseAttendance($request->user()->id, $course->id),
        );
    }
}
