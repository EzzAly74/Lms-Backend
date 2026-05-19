<?php

namespace App\OpenApi\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="CourseSection",
 *     type="object",
 *     @OA\Property(property="id",        type="integer"),
 *     @OA\Property(property="course_id", type="integer"),
 *     @OA\Property(property="name",      type="string",  description="Localized name."),
 *     @OA\Property(property="order",     type="integer", nullable=true)
 * )
 */
class CourseSection {}
