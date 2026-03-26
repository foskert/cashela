<?php
namespace App\Jobs;

use App\Models\Transaction;
use App\Services\ProductInternalService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class TransactionInternalJob implements ShouldQueue
{
    use Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    public $tries   = 5;
    public $backoff =  [60, 300, 600];

    public function __construct(
        public Transaction $transaction,
        public string $event
    ) {}

    public function handle(ProductInternalService $service): void
    {
        if (!$this->transaction->exists && $this->event !== 'deleted') {
            return;
        }
        $service->recordAudit($this->transaction, $this->event);
    }

    public function failed(Throwable $exception): void
    {
        Log::critical(__('product.audits.failed', ['id' => $this->transaction->id]), [
            'error' => $exception->getMessage()
        ]);
    }
}
