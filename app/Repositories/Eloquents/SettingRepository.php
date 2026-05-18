<?php

namespace App\Repositories\Eloquents;

use App\Models\Setting;
use App\Repositories\Contracts\SettingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SettingRepository extends BaseRepository implements SettingRepositoryInterface
{
    public function __construct(Setting $model)
    {
        parent::__construct($model);
    }

    public function all(): Collection
    {
        return $this->model->newQuery()->get();
    }

    public function updateByKey(string $key, mixed $value): void
    {
        $this->model->newQuery()->where('key', $key)->update(['value' => $value]);
    }
}
