<?php

namespace App\Repositories\Eloquents;

use App\Models\Course;
use App\Repositories\Contracts\CourseRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CourseRepository extends BaseRepository implements CourseRepositoryInterface
{
    public function __construct(Course $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(
        int     $perPage,
        ?string $search,
        ?int    $categoryId,
        ?bool   $active,
        ?string $courseType,
        ?string $status = null,
    ): LengthAwarePaginator {
        $locale = app()->getLocale();

        return $this->model->newQuery()
            // Only the columns the list actually needs — avoids re-decoding
            // very large `description` JSON blobs on every list call.
            ->select([
                'courses.id',
                'courses.title',
                'courses.course_type',
                'courses.category_id',
                'courses.active',
                'courses.certificate',
                'courses.image',
                'courses.created_at',
                'courses.updated_at',
            ])
            ->with([
                'category:id,name',
                'instructors:id,name',
            ])
            ->withCount([
                'users as users_count',
                'sessions as sessions_count',
            ])
            ->when($search, fn ($q) => $q->where(function ($inner) use ($search, $locale) {
                // Translatable columns are stored as JSON. Match BOTH the
                // active locale and English so admins can search either.
                $inner->where("title->{$locale}", 'LIKE', "%{$search}%")
                    ->orWhere('title->en', 'LIKE', "%{$search}%")
                    ->orWhere('title->ar', 'LIKE', "%{$search}%");
            }))
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->when(!is_null($active), fn ($q) => $q->where('active', $active))
            ->when($courseType, fn ($q) => $q->where('course_type', $courseType))
            ->when($status, fn ($q) => $this->applyStatusFilter($q, $status))
            ->latest('id')
            ->paginate($perPage);
    }

    /**
     * Return all admin tab counts in a single aggregate query so the list
     * page doesn't have to fire one paginated request per status.
     *
     * @return array{all: int, active: int, inactive: int, pending: int, upcoming: int}
     */
    public function tabCounts(): array
    {
        $row = $this->model->newQuery()
            ->selectRaw('
                COUNT(*)                                             AS all_count,
                COUNT(CASE WHEN active = 1 THEN 1 END)               AS active_count,
                COUNT(CASE WHEN active = 0 THEN 1 END)               AS inactive_count
            ')
            ->first();

        $all      = (int) ($row->all_count      ?? 0);
        $active   = (int) ($row->active_count   ?? 0);
        $inactive = (int) ($row->inactive_count ?? 0);

        return [
            'all'      => $all,
            'active'   => $active,
            'inactive' => $inactive,
            // The admin UI shows these tabs but the schema has no dedicated
            // column for them — surface zeros so the contract stays stable
            // until a future workflow column is introduced.
            'pending'  => 0,
            'upcoming' => 0,
        ];
    }

    public function allActive(): Collection
    {
        return $this->model->newQuery()
            ->active()
            ->with('category:id,name')
            ->latest()
            ->get();
    }

    public function findWithRelations(int $id): Course
    {
        return $this->model->newQuery()
            ->with([
                'category:id,name',
                'instructors:id,name,image',
                'qualificationSkills:id,name',
                'sections',
                'exams:id,course_id,title,degree,is_final',
            ])
            ->withCount([
                'users as users_count',
                'sessions as sessions_count',
            ])
            ->findOrFail($id);
    }

    public function findWithBasicRelations(int $id): Course
    {
        return $this->model->newQuery()
            ->with([
                'category:id,name',
                'instructors:id,name',
                'qualificationSkills:id,name',
            ])
            ->findOrFail($id);
    }

    public function activePluckedTitles(): Collection
    {
        return $this->model->newQuery()
            ->active()
            ->orderBy('id')
            ->pluck('title', 'id');
    }

    /**
     * Apply the admin tab status filter without leaking the mapping into
     * the controller layer.
     */
    private function applyStatusFilter($query, string $status)
    {
        return match ($status) {
            'active'             => $query->where('active', true),
            'inactive',
            'pending',
            'upcoming'           => $query->where('active', false),
            default              => $query,
        };
    }
}
