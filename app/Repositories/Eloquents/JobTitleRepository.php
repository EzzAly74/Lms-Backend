<?php

namespace App\Repositories\Eloquents;

use App\Models\JobTitle;
use App\Repositories\Contracts\JobTitleRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class JobTitleRepository extends BaseRepository implements JobTitleRepositoryInterface
{
    public function __construct(JobTitle $model)
    {
        parent::__construct($model);
    }

    public function list(int $perPage, ?string $search): LengthAwarePaginator
    {
        $learnersSubQuery = DB::table('users_courses')
            ->selectRaw('COUNT(DISTINCT users_courses.user_id)')
            ->join('course_qualification_skills', 'users_courses.course_id', '=', 'course_qualification_skills.course_id')
            ->join('job_title_qualification_skill', 'course_qualification_skills.qualification_skill_id', '=', 'job_title_qualification_skill.qualification_skill_id')
            ->whereColumn('job_title_qualification_skill.job_title_id', 'job_titles.id');

        return $this->model->newQuery()
            ->when($search, fn ($q) => $q->where('name', 'LIKE', "%{$search}%"))
            ->withCount(['qualificationSkills', 'users as employees_count'])
            ->addSelect(['job_titles.*', DB::raw("({$learnersSubQuery->toSql()}) as learners_count")])
            ->mergeBindings($learnersSubQuery)
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function allForSelect(): Collection
    {
        return $this->model->newQuery()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();
    }

    public function syncQualifications(JobTitle $jobTitle, array $qualIds): void
    {
        $jobTitle->qualificationSkills()->sync($qualIds);
    }
}
