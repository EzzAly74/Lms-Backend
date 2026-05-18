<?php

namespace App\Services;

use App\Repositories\Contracts\CertificateRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class CertificateService
{
    public function __construct(
        private readonly CertificateRepositoryInterface $repo
    ) {}

    public function paginate(int $perPage = 20, ?int $courseId = null): LengthAwarePaginator
    {
        $examCerts = $this->repo->getExamCertificates($courseId)
            ->map(fn ($ue) => $this->formatExamCert($ue));

        $evalCerts = $this->repo->getEvalCertificates($courseId)
            ->map(fn ($uce) => $this->formatEvalCert($uce));

        $merged = $examCerts->merge($evalCerts)->sortByDesc('created_at')->values();
        $page   = request()->input('page', 1);
        $slice  = $merged->forPage($page, $perPage);

        return new LengthAwarePaginator(
            $slice->values(),
            $merged->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /** Return all certificate entries for a specific course (exam + eval paths). */
    public function findByCourse(int $courseId): array
    {
        $examCerts = $this->repo->getExamCertificates($courseId)
            ->map(fn ($ue) => $this->formatExamCert($ue));

        $evalCerts = $this->repo->getEvalCertificates($courseId)
            ->map(fn ($uce) => $this->formatEvalCert($uce));

        return $examCerts->merge($evalCerts)->sortByDesc('created_at')->values()->all();
    }

    private function formatExamCert($ue): array
    {
        return [
            'type'         => 'exam',
            'user'         => [
                'id'              => $ue->user?->id,
                'name'            => $ue->user?->name,
                'machine_code'    => $ue->user?->machine_code,
                'department_name' => $ue->user?->department_name,
            ],
            'course'       => [
                'id'                    => $ue->course?->id,
                'title'                 => $ue->course?->getTranslation('title', app()->getLocale()),
                'title_for_certificate' => $ue->course?->getTranslation('title_for_certificate', app()->getLocale()),
            ],
            'user_degree'  => $ue->user_degree,
            'total_degree' => $ue->exam?->degree,
            'created_at'   => $ue->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    private function formatEvalCert($uce): array
    {
        return [
            'type'         => 'evaluation',
            'user'         => [
                'id'              => $uce->user?->id,
                'name'            => $uce->user?->name,
                'machine_code'    => $uce->user?->machine_code,
                'department_name' => $uce->user?->department_name,
            ],
            'course'       => [
                'id'                    => $uce->course?->id,
                'title'                 => $uce->course?->getTranslation('title', app()->getLocale()),
                'title_for_certificate' => $uce->course?->getTranslation('title_for_certificate', app()->getLocale()),
            ],
            'user_degree'  => null,
            'total_degree' => null,
            'created_at'   => $uce->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
