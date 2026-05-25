<?php

declare(strict_types=1);

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * S-07 certificate card. `id` is the *compound* "type:source_id"
 * identifier emitted by MobileCertificateRepository so the download
 * endpoint can route to the correct issuance source (exam vs.
 * evaluation) without leaking that detail to the client.
 *
 * Every cert echoes the `learner` block — `machine_code` is the
 * canonical id printed on the certificate visual, and the mobile
 * client uses it to label the card ("Issued to <machine_code>").
 */
class CertificateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $row = $this->resource;
        return [
            'id'           => (string) $row['id'],
            'type'         => (string) $row['type'],
            'course_id'    => (int) $row['course_id'],
            'course_title' => (string) $row['course_title'],
            'issued_at'    => $row['issued_at'],
            'issued_date'  => $row['issued_date'],
            'user_rating'  => $row['user_rating'] ?? null,
            'learner'      => $request->user()
                ? (new LearnerIdentityResource($request->user()))->toArray($request)
                : null,
        ];
    }
}
