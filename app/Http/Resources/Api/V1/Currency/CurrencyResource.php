<?php

namespace App\Http\Resources\Api\V1\Currency;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use function Symfony\Component\Translation\t;

class CurrencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       return [
            'id'                 => $this->id,
            'code'               => $this->code,
            'name'               => $this->name,
            'symbol'             => $this->symbol,
            'is_active'          => $this->is_active,
            'created_at'         => format_date($this->created_at),
            'updated_at'         => format_date($this->updated_at),
        ];
    }
}
