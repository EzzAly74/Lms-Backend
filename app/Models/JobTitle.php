<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobTitle extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function qualificationSkills(): BelongsToMany
    {
        return $this->belongsToMany(
            QualificationSkill::class,
            'job_title_qualification_skill',
            'job_title_id',
            'qualification_skill_id',
        )->withTimestamps();
    }

    /**
     * Employees holding this job title.
     *
     * The HR system of record exposes a dedicated job catalogue
     * (`/api/Job`) **and** the per-employee `jobName` field returned by
     * `/api/Employee/GetCurrentEmployees`. The `sync:employees` artisan
     * command writes that `jobName` straight into `users.job_title`,
     * while {@see \App\Services\JobTitleSyncService::syncFromHr()}
     * upserts the catalogue rows. Joining via the natural string key
     * keeps the two columns referentially aligned without an extra FK
     * and lets `withCount('users')` deliver the per-card employee count.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'job_title', 'name');
    }
}
