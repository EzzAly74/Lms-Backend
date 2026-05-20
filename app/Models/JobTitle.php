<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
}
