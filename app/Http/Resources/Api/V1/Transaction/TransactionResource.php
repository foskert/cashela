<?php

namespace App\Http\Resources\Api\V1\Transaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'reference_code' => $this->reference_code,
            'status'         => $this->status,
            'description'    => $this->description,
            'source' => [
                'amount'   => number_format($this->amount_source, 2),
                'currency' => $this->sourceCurrency->code ?? null,
                'symbol'   => strtoupper($this->sourceCurrency->symbol ?? ''),
            ],
            'destination' => [
                'amount'   => number_format($this->amount_destination, 2),
                'currency' => $this->destinationCurrency->code ?? null,
                'symbol'   => strtoupper($this->destinationCurrency->symbol ?? ''),
            ],
            'exchange' => [
                'rate'       => number_format($this->exchange_rate, 6),
                'expires_at' => $this->expires_at instanceof Carbon
                                ? $this->expires_at->toDateTimeString()
                                : ($this->expires_at ? Carbon::parse($this->expires_at)->toDateTimeString() : null),
            ],
            'meta' => [
                'ip_address' => $this->ip_address,
                'created_at' => $this->created_at instanceof Carbon
                                ? $this->created_at->format('Y-m-d H:i:s')
                                : Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
            ],
            'created_at' => format_date($this->created_at),
            'updated_at' => format_date($this->updated_at),
        ];
    }
}
