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

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'job_title', 'name');
    }
}
