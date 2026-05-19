<?php

namespace App\Http\Controllers\apis;

use App\Http\Requests\Api\CourseSectionSyncRequest;
use App\Http\Resources\CourseSectionResource;
use App\Models\Course;
use App\Models\CourseSection;
use App\Services\CourseSectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class CourseSectionController extends ApiController
{
    public function __construct(private readonly CourseSectionService $service) {}

    /**
     * @OA\Get(
     *     path="/courses/{course}/sections",
     *     tags={"Course Sections"},
     *     summary="List sections for a course (ordered).",
     *     security={{"BearerAuth": {}}},
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguage"),
     *     @OA\Parameter(
     *         name="course", in="path", required=true,
     *         description="Course id",
     *         @OA\Schema(type="integer", minimum=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Course sections",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/SuccessResponse"),
     *                 @OA\Schema(@OA\Property(
     *                     property="result",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/CourseSection")
     *                 ))
     *             }
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=404, ref="#/components/responses/NotFound")
     * )
     */
    public function index(Course $course): JsonResponse
    {
        $sections = $this->service->listForCourse($course);
        return $this->success(__('messages.retrieved'), CourseSectionResource::collection($sections));
    }

    /**
     * @OA\Post(
     *     path="/courses/{course}/sections",
     *     tags={"Course Sections"},
     *     summary="Create a section under a course (admin only).",
     *     security={{"BearerAuth": {}}},
     *     @OA\Parameter(
     *         name="course", in="path", required=true,
     *         description="Course id",
     *         @OA\Schema(type="integer", minimum=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", ref="#/components/schemas/TranslatedString")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/SuccessResponse"),
     *                 @OA\Schema(@OA\Property(property="result", ref="#/components/schemas/CourseSection"))
     *             }
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=403, ref="#/components/responses/Forbidden"),
     *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
     *     @OA\Response(response=422, ref="#/components/responses/ValidationError")
     * )
     */
    public function store(Course $course, Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'    => 'required|array',
            'name.ar' => 'required|string|max:255',
            'name.en' => 'nullable|string|max:255',
        ]);
        $section = $this->service->create($course, $data);
        return $this->created(__('messages.created'), new CourseSectionResource($section));
    }

    /**
     * @OA\Post(
     *     path="/courses/{course}/sections/sync",
     *     tags={"Course Sections"},
     *     summary="Bulk replace sections for a course (admin only). Pass full list; existing sections matched by id are kept, others are deleted.",
     *     security={{"BearerAuth": {}}},
     *     @OA\Parameter(
     *         name="course", in="path", required=true,
     *         description="Course id",
     *         @OA\Schema(type="integer", minimum=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"sections"},
     *             @OA\Property(
     *                 property="sections",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"name"},
     *                     @OA\Property(property="id",   type="integer", nullable=true, description="Existing section id to update; omit to create."),
     *                     @OA\Property(property="name", ref="#/components/schemas/TranslatedString")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Synced",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/SuccessResponse"),
     *                 @OA\Schema(@OA\Property(
     *                     property="result",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/CourseSection")
     *                 ))
     *             }
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=403, ref="#/components/responses/Forbidden"),
     *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
     *     @OA\Response(response=422, ref="#/components/responses/ValidationError")
     * )
     */
    public function sync(Course $course, CourseSectionSyncRequest $request): JsonResponse
    {
        $sections = $this->service->sync($course, $request->validated()['sections']);
        return $this->success(__('messages.updated'), CourseSectionResource::collection($sections));
    }

    /**
     * @OA\Put(
     *     path="/courses/{course}/sections/{section}",
     *     tags={"Course Sections"},
     *     summary="Update a section (admin only).",
     *     security={{"BearerAuth": {}}},
     *     @OA\Parameter(
     *         name="course", in="path", required=true,
     *         description="Course id",
     *         @OA\Schema(type="integer", minimum=1)
     *     ),
     *     @OA\Parameter(
     *         name="section", in="path", required=true,
     *         description="Section id",
     *         @OA\Schema(type="integer", minimum=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", ref="#/components/schemas/TranslatedString")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Updated",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/SuccessResponse"),
     *                 @OA\Schema(@OA\Property(property="result", ref="#/components/schemas/CourseSection"))
     *             }
     *         )
     *     ),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=403, ref="#/components/responses/Forbidden"),
     *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
     *     @OA\Response(response=422, ref="#/components/responses/ValidationError")
     * )
     */
    public function update(Course $course, CourseSection $section, Request $request): JsonResponse
    {
        abort_if($section->course_id !== $course->id, 404);
        $data = $request->validate([
            'name'    => 'required|array',
            'name.ar' => 'required|string|max:255',
            'name.en' => 'nullable|string|max:255',
        ]);
        $updated = $this->service->update($section, $data);
        return $this->success(__('messages.updated'), new CourseSectionResource($updated));
    }

    /**
     * @OA\Delete(
     *     path="/courses/{course}/sections/{section}",
     *     tags={"Course Sections"},
     *     summary="Delete a section (admin only).",
     *     security={{"BearerAuth": {}}},
     *     @OA\Parameter(
     *         name="course", in="path", required=true,
     *         description="Course id",
     *         @OA\Schema(type="integer", minimum=1)
     *     ),
     *     @OA\Parameter(
     *         name="section", in="path", required=true,
     *         description="Section id",
     *         @OA\Schema(type="integer", minimum=1)
     *     ),
     *     @OA\Response(response=200, description="Deleted", @OA\JsonContent(ref="#/components/schemas/EmptyResponse")),
     *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
     *     @OA\Response(response=403, ref="#/components/responses/Forbidden"),
     *     @OA\Response(response=404, ref="#/components/responses/NotFound")
     * )
     */
    public function destroy(Course $course, CourseSection $section): JsonResponse
    {
        abort_if($section->course_id !== $course->id, 404);
        $this->service->delete($section);
        return $this->deleted(__('messages.deleted'));
    }
}
