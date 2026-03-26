<?php

namespace App\Policies\Api\V1;
use App\Models\Currency;
use Illuminate\Auth\Access\HandlesAuthorization;

use App\Models\User;

class CurrencyPolicy
{
    use HandlesAuthorization;
     public function index(User $user): bool
    {
         return $user->can('currency.index', Currency::class);
    }
    public function check(?User $user): bool
    {
         return $user->can('currency.check', Currency::class);
    }

}
