<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * Root OpenAPI document for the 2B Academy LMS API.
 *
 * Every API controller declares its operations via `@OA\Get`, `@OA\Post`, etc.
 * `php artisan l5-swagger:generate` aggregates all annotations from `app/`
 * into `storage/api-docs/api-docs.json`, served by Swagger UI at /api/documentation.
 *
 * @OA\Info(
 *     version="1.0.0",
 *     title="2B Academy LMS API",
 *     description="RESTful API for the 2B Academy LMS. All endpoints are prefixed with /api/v1. Protected endpoints require a Sanctum bearer token (Authorization: Bearer <token>). Translatable response fields are localized via Accept-Language header (ar or en, default ar). Translatable input bodies accept {en, ar} objects.",
 *     @OA\Contact(name="2B Academy API")
 * )
 *
 * @OA\Server(url="/api/v1", description="API v1")
 *
 * @OA\SecurityScheme(
 *     securityScheme="BearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Sanctum"
 * )
 *
 * @OA\Tag(name="Auth",                  description="User & admin authentication, profile, logout")
 * @OA\Tag(name="Dashboard",             description="Admin dashboard statistics")
 * @OA\Tag(name="Users",                 description="Employee / user records (HR-synced)")
 * @OA\Tag(name="Admins",                description="Admin user management")
 * @OA\Tag(name="Roles",                 description="Spatie roles & permissions")
 * @OA\Tag(name="Categories",            description="Course categories")
 * @OA\Tag(name="Instructors",           description="Course instructors")
 * @OA\Tag(name="Qualification Skills",  description="Localized course qualification skills taxonomy")
 * @OA\Tag(name="Courses",               description="Course CRUD")
 * @OA\Tag(name="Course Sections",       description="Course content: sections")
 * @OA\Tag(name="Course Lectures",       description="Course content: lectures")
 * @OA\Tag(name="Course Exams",          description="Course content: exams")
 * @OA\Tag(name="Course Assignments",    description="Course content: assignments + submissions")
 * @OA\Tag(name="Course Sessions",       description="Offline course sessions")
 * @OA\Tag(name="Course Ratings",        description="Per-course ratings")
 * @OA\Tag(name="Lecture Questions",     description="Q&A on individual lectures")
 * @OA\Tag(name="Lecture Progress",      description="Per-user lecture progress")
 * @OA\Tag(name="Online Enrollment",     description="Enrolling employees in online courses")
 * @OA\Tag(name="User Enrollment",       description="Enrolling employees in offline courses")
 * @OA\Tag(name="Attendance",            description="Attendance recording & reporting")
 * @OA\Tag(name="Cohort Attendance",     description="Per-cohort attendance rollup (sessions + learners + per-session absentees) for the course detail drawer")
 * @OA\Tag(name="Certificates",          description="Issued course completion certificates")
 * @OA\Tag(name="Evaluation Categories", description="Categories used by the general evaluation system")
 * @OA\Tag(name="Evaluations",           description="General evaluation definitions")
 * @OA\Tag(name="Course Evaluations",    description="Per-course evaluation submissions")
 * @OA\Tag(name="Exams",                 description="User exam submissions")
 * @OA\Tag(name="Forms",                 description="Public forms + questions")
 * @OA\Tag(name="User Forms",            description="User-facing form fill flow")
 * @OA\Tag(name="Notifications",         description="Public notifications")
 * @OA\Tag(name="Articles",              description="CMS articles / blogs")
 * @OA\Tag(name="CMS",                   description="About page + testimonials")
 * @OA\Tag(name="Settings",              description="Application settings")
 * @OA\Tag(name="Progress",              description="Aggregate progress reports")
 * @OA\Tag(name="My",                    description="User-facing aggregate endpoints under /my/*")
 * @OA\Tag(name="Webhooks",              description="Inbound webhooks (HR system -> LMS)")
 * @OA\Tag(name="Enums",                 description="Reference dropdown data — localized {value,label} option sets for every backend enum (course_type, cohort_status, module_content_type, etc.). Honors Accept-Language: en|ar. The frontend pulls these instead of hardcoding option lists.")
 */
class Info
{
}
