<?php

declare(strict_types=1);

namespace App\Http\Controllers\apis;

use App\Http\Requests\Api\Mobile\IssuePasscodeRequest;
use App\Http\Resources\SessionPasscodeStateResource;
use App\Models\Instructor;
use App\Services\DashboardPasscodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use OpenApi\Annotations as OA;

/**
 * Instructor-dashboard "Passcode" widget (Figma 515:34995 / 515:37969 /
 * 515:35489). Read the current live-session passcode state, and
 * generate a passcode for the current live session in one tap — without
 * the dashboard having to know a session id up-front.
 *
 * ADDITIVE: delegates real generation to the existing
 * SessionPasscodeService (same as POST /admin/course-sessions/{session}
 * /passcode) so the dashboard and mobile S-06 flow never diverge.
 */
class DashboardPasscodeController extends ApiController
{
    public function __construct(private readonly DashboardPasscodeService $service) {}

    /**
     * @OA\Get(
     *     path="/dashboard/passcode",
     *     tags={"Dashboard"},
     *     summary="Current instructor-dashboard passcode widget state.",
     *     security={{"BearerAuth": {}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguage"),
     *     @OA\Response(
     *         response=200,
     *         description="Passcode widget state",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/SuccessResponse"),
     *                 @OA\Schema(@OA\Property(property="result", ref="#/components/schemas/SessionPasscodeState"))
     *             }
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=403, ref="#/components/responses/Forbidden")
     * )
     */
    public function current(Request $request): JsonResponse
    {
        return $this->success(
            __('messages.retrieved'),
            new SessionPasscodeStateResource(
                $this->service->currentState($this->instructorCourseIds($request)),
            ),
        );
    }

    /**
     * @OA\Post(
     *     path="/dashboard/passcode",
     *     tags={"Dashboard"},
     *     summary="Generate / rotate the passcode for the current live session.",
     *     description="Resolves the live-now session (or the earliest session scheduled for today) and issues a numeric passcode that powers the mobile S-06 Mark Present screen.",
     *     security={{"BearerAuth": {}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguage"),
     *     @OA\RequestBody(required=false, @OA\JsonContent(
     *         @OA\Property(property="expires_at", type="string", format="date-time", nullable=true),
     *         @OA\Property(property="length",     type="integer", nullable=true)
     *     )),
     *     @OA\Response(
     *         response=200,
     *         description="Passcode generated",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/SuccessResponse"),
     *                 @OA\Schema(@OA\Property(property="result", ref="#/components/schemas/SessionPasscodeState"))
     *             }
     *         )
     *     ),
     *     @OA\Response(response=422, description="No live session to generate a passcode for."),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=403, ref="#/components/responses/Forbidden")
     * )
     */
    public function generate(IssuePasscodeRequest $request): JsonResponse
    {
        $courseIds = $this->instructorCourseIds($request);

        // Only instructors who teach can generate a passcode.
        if ($courseIds === null) {
            return $this->error(__('messages.forbidden'), 403);
        }

        $target = $this->service->resolveTargetSession($courseIds);

        if ($target === null) {
            return $this->error(__('messages.passcode.no_live_session'), 422);
        }

        $expiresAt = $request->filled('expires_at')
            ? Carbon::parse((string) $request->validated('expires_at'))
            : null;

        return $this->success(
            __('messages.passcode.generated'),
            new SessionPasscodeStateResource(
                $this->service->issueFor($target, $request->validated('length'), $expiresAt),
            ),
        );
    }

    /**
     * Resolve the courses the authenticated back-office user teaches.
     *
     * Instructors are matched to the `instructors` catalogue by email
     * (the unified admin Users view keeps the two personas in sync). A
     * `null` return means "not an instructor / teaches nothing" — the
     * widget is then hidden and generation is forbidden.
     *
     * @return array<int, int>|null
     */
    private function instructorCourseIds(Request $request): ?array
    {
        $user = $request->user();

        if ($user === null || empty($user->email)) {
            return null;
        }

        $instructor = Instructor::query()
            ->where('email', $user->email)
            ->first();

        if ($instructor === null) {
            return null;
        }

        $ids = $instructor->courses()->pluck('courses.id')->all();

        return empty($ids) ? null : array_map('intval', $ids);
    }
}
