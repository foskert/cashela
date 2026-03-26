<?php

namespace App\Http\Resources\Api\V1\Currency;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use function Symfony\Component\Translation\t;

class CheckResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'amount_destination' => number_format((float)$this['result'], 2, '.', ''),
            'exchange_rate'      => $this['rate'],
            'base_currency'      => $this['base'],
            'amount_source'      => $this['amount'],
        ];
    }
}
