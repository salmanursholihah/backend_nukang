<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderProgressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'percent'     => $this->percent,                           // ← TAMBAH
            'reported_at' => optional($this->reported_at)->toDateTimeString(),
            'created_at'  => optional($this->created_at)->toDateTimeString(),
            'photos'      => $this->whenLoaded('photos', function () {
                return $this->photos->map(fn($p) => [
                    'id'        => $p->id,
                    'photo_url' => $p->photo_url,
                ]);
            }, []),
        ];
    }
}
