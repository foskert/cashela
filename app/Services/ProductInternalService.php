<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Audit;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
class ProductInternalService
{
    public function recordAudit(Transaction $transaction, string $event): void
    {
        $newValues = $transaction->getAttributes();
        $oldValues = null;
        switch ($event) {
            case 'created':
                $oldValues = null;
                $newValues = $transaction->getAttributes();
                break;
            case 'updated':
                $oldValues = array_intersect_key($transaction->getRawOriginal(), $transaction->getChanges());
                $newValues = $transaction->getChanges();
                break;

            case 'deleted':
                $oldValues = $transaction->getRawOriginal();
                break;
        }
        if ($event === 'updated' && empty($newValues)) {
        return;
    }
        Audit::create([
            'event'          => $event,
            'auditable_id'   => $transaction->id,
            'auditable_type' => get_class($transaction),
            'old_values'     => $oldValues,
            'new_values'     => $newValues,
            'user_id'        => 1,
            'url'            => 'v1',
            'ip_address'     => '127.0.0.1',
        ]);

        Log::info(__('product.audits.message', ['id' => $transaction->id]));
    }
}
