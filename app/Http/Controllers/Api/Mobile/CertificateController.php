<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Resources\Mobile\CertificateResource;
use App\Models\Course;
use App\Services\Mobile\MobileCertificateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Mobile S-07 — Certificates. The list is served by MyLearningController;
 * this controller owns the per-certificate detail / download.
 *
 * 📱 MOBILE — Employee/Learner mobile app. Grouped under the single
 * `Mobile` Swagger tag (registered globally in App\OpenApi\Info).
 */
class CertificateController extends MobileBaseController
{
    public function __construct(private readonly MobileCertificateService $certificates) {}

    /**
     * @OA\Get(
     *     path="/mobile/certificates/{compoundId}",
     *     tags={"Mobile"},
     *     summary="📱 [MOBILE · S-07] Single certificate detail",
     *     description="📱 **MOBILE** · Screen **S-07 — Certificates · detail tap** · Audience: Employee/Learner mobile app · Looks up a derived certificate by its compound id (`exam:123` / `evaluation:456`).",
     *     @OA\Parameter(ref="#/components/parameters/MobileAuthorization"),
     *     @OA\Parameter(ref="#/components/parameters/EmployeeCode"),
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguage"),
     *     @OA\Parameter(name="compoundId", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(Request $request, string $compoundId): JsonResponse
    {
        $row = $this->certificates->findById($request->user(), $compoundId, app()->getLocale());
        if ($row === null) {
            return $this->notFound(__('messages.mobile.certificate_not_found'));
        }

        return $this->success(
            __('messages.mobile.certificate_download_ready'),
            new CertificateResource($row),
        );
    }

    /**
     * @OA\Get(
     *     path="/mobile/certificates/{compoundId}/download",
     *     tags={"Mobile"},
     *     summary="📱 [MOBILE · S-07] Download certificate image",
     *     description="📱 **MOBILE** · Screen **S-07 — Certificates · Download** · Audience: Employee/Learner mobile app · Returns the rendered base64 JPEG (same template used by the legacy web view).",
     *     @OA\Parameter(ref="#/components/parameters/MobileAuthorization"),
     *     @OA\Parameter(ref="#/components/parameters/EmployeeCode"),
     *     @OA\Parameter(ref="#/components/parameters/AcceptLanguage"),
     *     @OA\Parameter(name="compoundId", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="OK"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function download(Request $request, string $compoundId): JsonResponse
    {
        $user = $request->user();
        $row  = $this->certificates->findById($user, $compoundId, app()->getLocale());
        if ($row === null) {
            return $this->notFound(__('messages.mobile.certificate_not_found'));
        }

        // Re-use the legacy template renderer from HelperTrait so the
        // mobile / web certificate visuals stay byte-identical. The
        // rendered base64 image is what UserDashboardService returns
        // today — we just expose it through the new mobile envelope.
        $course = Course::findOrFail($row['course_id']);
        $generator = new class {
            use \App\Http\Traits\HelperTrait;
        };
        $image = $generator->generateCertificate(
            $course->getTranslation('title_for_certificate', app()->getLocale())
                ?: $course->getTranslation('title', app()->getLocale()),
            $user->name,
        );

        return $this->success(
            __('messages.mobile.certificate_download_ready'),
            [
                'id'           => $row['id'],
                'course_id'    => $row['course_id'],
                'course_title' => $row['course_title'],
                'issued_at'    => $row['issued_at'],
                'image_base64' => $image,
                'mime_type'    => 'image/jpeg',
            ],
        );
    }
}
