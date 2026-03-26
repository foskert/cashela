<?php

namespace App\Policies\Api\V1;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    public function index(User $user): bool
    {
        return $user->can('products.index');
    }

    public function show(User $user, Transaction $product): bool
    {
        return $user->can('products.show');
    }

    public function create(User $user): bool
    {
        return $user->can('products.store');
    }

    public function update(User $user, Transaction $product): bool
    {
        return $user->can('products.update');
    }


}
