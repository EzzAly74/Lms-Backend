<?php

namespace Tests\Feature\Api\Mobile;

use App\Models\Course;
use App\Models\CourseExam;
use App\Models\CourseSection;
use App\Models\User;
use App\Models\UserExam;

/**
 * S-07 — certificate detail + download. The list itself is covered in
 * MyLearningApiTest; here we exercise the per-certificate lookup by its
 * compound id (`exam:{id}` / `evaluation:{id}`).
 */
class CertificateApiTest extends MobileTestCase
{
    /** Build a passed-final-exam certificate and return [user, compoundId]. */
    private function certificateFor(): array
    {
        $user   = $this->employee(['name' => 'Cert Holder']);
        $course = Course::factory()->create([
            'certificate'  => true,
            'is_evaluate'  => false,
        ]);
        $cohort = CourseSection::factory()->create(['course_id' => $course->id]);
        $exam   = CourseExam::factory()->final()->create([
            'course_id'  => $course->id,
            'section_id' => $cohort->id,
        ]);
        UserExam::factory()->create([
            'user_id'   => $user->id,
            'course_id' => $course->id,
            'exam_id'   => $exam->id,
            'status'    => 'success',
        ]);

        // Resolve the real compound id from the list endpoint (robust to
        // whatever internal id the derivation uses).
        $list = $this->withHeaders($this->headersFor($user))
                     ->getJson(self::BASE . '/mobile/my-learning/certificates');
        $compoundId = $list->json('result.0.id');

        return [$user, $compoundId];
    }

    public function test_certificate_detail_returns_payload(): void
    {
        [$user, $compoundId] = $this->certificateFor();

        $response = $this->withHeaders($this->headersFor($user))
                         ->getJson(self::BASE . '/mobile/certificates/' . $compoundId);

        $this->assertSuccess($response);
        $response->assertJsonPath('result.id', $compoundId)
                 ->assertJsonStructure(['result' => ['id', 'type', 'course_id', 'course_title', 'issued_at']]);
    }

    public function test_certificate_download_returns_base64_image(): void
    {
        [$user, $compoundId] = $this->certificateFor();

        $response = $this->withHeaders($this->headersFor($user))
                         ->getJson(self::BASE . '/mobile/certificates/' . $compoundId . '/download');

        $this->assertSuccess($response);
        $response->assertJsonPath('result.mime_type', 'image/jpeg')
                 ->assertJsonStructure(['result' => ['id', 'course_id', 'course_title', 'image_base64', 'mime_type']]);
        $this->assertNotEmpty($response->json('result.image_base64'));
    }

    public function test_certificate_detail_404_for_unowned_certificate(): void
    {
        $user = $this->employee();

        $response = $this->withHeaders($this->headersFor($user))
                         ->getJson(self::BASE . '/mobile/certificates/exam:999999');

        $this->assertError($response, 404);
    }

    public function test_certificate_not_visible_to_other_learner(): void
    {
        [, $compoundId] = $this->certificateFor();
        $other = $this->employee();

        $response = $this->withHeaders($this->headersFor($other))
                         ->getJson(self::BASE . '/mobile/certificates/' . $compoundId);

        $this->assertError($response, 404);
    }
}
