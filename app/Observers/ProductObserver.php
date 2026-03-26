<?php
namespace App\Observers;

use App\Models\Transaction;
use App\Jobs\TransactionInternalJob;

class ProductObserver
{
    public function created(Transaction $product): void
    {
        TransactionInternalJob::dispatch($product, 'created')->afterCommit();
    }

    public function updated(Transaction $product): void
    {
        if ($product->wasChanged()) {
            TransactionInternalJob::dispatch($product, 'updated')->afterCommit();
        }
    }

}
