<?php

declare(strict_types=1);

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * S-02 filter chip. `id` is `null` for the synthetic "All" row so the
 * mobile client can stay declarative ("if id === null → no category
 * filter").
 */
class AcademyCategoryChipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $row = $this->resource;
        return [
            'id'     => $row['id'] === null ? null : (int) $row['id'],
            'name'   => (string) $row['name'],
            'count'  => (int) $row['count'],
            'is_all' => (bool) $row['is_all'],
        ];
    }
}
