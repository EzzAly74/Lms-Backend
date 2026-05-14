<?php

namespace App\Http\Controllers\apis;

use App\Http\Requests\Api\AboutRequest;
use App\Http\Requests\Api\TestimonialRequest;
use App\Http\Resources\AboutResource;
use App\Http\Resources\TestimonialResource;
use App\Models\Testimonial;
use App\Services\CmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CmsController extends ApiController
{
    public function __construct(private readonly CmsService $service) {}

    // ── About ──────────────────────────────────────────────────────────────

    public function aboutShow(): JsonResponse
    {
        return $this->success(__('messages.retrieved'), new AboutResource($this->service->getAbout()));
    }

    public function aboutUpdate(AboutRequest $request): JsonResponse
    {
        $about = $this->service->updateAbout($request->validated(), $request->file('image'));
        return $this->success(__('messages.updated'), new AboutResource($about));
    }

    // ── Testimonials ───────────────────────────────────────────────────────

    public function testimonialIndex(Request $request): JsonResponse
    {
        $testimonials = $this->service->paginateTestimonials((int) $request->get('per_page', 20));
        return $this->paginated(__('messages.retrieved'), $testimonials);
    }

    public function testimonialActiveList(): JsonResponse
    {
        return $this->success(__('messages.retrieved'),
            TestimonialResource::collection($this->service->activeTestimonials())
        );
    }

    public function testimonialShow(Testimonial $testimonial): JsonResponse
    {
        return $this->success(__('messages.retrieved'), new TestimonialResource($testimonial));
    }

    public function testimonialStore(TestimonialRequest $request): JsonResponse
    {
        $testimonial = $this->service->createTestimonial($request->validated(), $request->file('image'));
        return $this->created(__('messages.created'), new TestimonialResource($testimonial));
    }

    public function testimonialUpdate(Testimonial $testimonial, TestimonialRequest $request): JsonResponse
    {
        $testimonial = $this->service->updateTestimonial($testimonial, $request->validated(), $request->file('image'));
        return $this->success(__('messages.updated'), new TestimonialResource($testimonial));
    }

    public function testimonialDestroy(Testimonial $testimonial): JsonResponse
    {
        $this->service->deleteTestimonial($testimonial);
        return $this->deleted(__('messages.deleted'));
    }
}
