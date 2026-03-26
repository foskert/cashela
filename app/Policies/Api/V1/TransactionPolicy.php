<?php

namespace App\Policies\Api\V1;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization;
    public function index(User $user): bool
    {
         return $user->can('transactions.index', Transaction::class);
    }
    public function store(User $user): bool
    {
         return $user->can('transactions.store', Transaction::class);
    }
    public function show(User $user): bool
    {
         return $user->can('transactions.show', Transaction::class);
    }
    public function update(User $user): bool
    {
         return $user->can('transactions.update', Transaction::class);
    }
}
