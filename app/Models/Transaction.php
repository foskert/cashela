<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Models\User;

/**
 * @property int $id
 * @property string $name
 * @property string $symbol
 * @property float $exchange_rate
 */
 class Transaction extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'user_id',
        'amount_source',
        'amount_destination',
        'exchange_rate',
        'currency_source_id',
        'currency_destination_id',
        'status',
        'description',
        'reference_code',
        'expires_at',
        'ip_address',
        'created_at',
        'user_id',

    ];

    protected function casts(): array
    {
        return [
            'amount_source' => 'decimal:4',
            'amount_destination' => 'decimal:4',
            'exchange_rate' => 'decimal:6',
            'created_at' => 'datetime:Y-m-d H:i:s',
        ];
    }
    public function sourceCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_source_id');
    }

    public function destinationCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_destination_id');
    }

    protected function formattedSourceAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->sourceCurrency->symbol . ' ' . number_format($this->amount_source, 2)
        );
    }

    public function scopeFilter(Builder $query, Request $request): Builder
    {
        /** @var User|null $user */
        $user = $request->user();
        if ($user) {
            $query->when($user->hasRole('user'), function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
            $query->when($user->hasRole('admin'), function ($q) {
                $q->where('status', 'pending');
            });
        }
        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->input('search');
            $q->where('reference_code', 'LIKE', "%{$search}%");
        });
        return $query;
    }
}
