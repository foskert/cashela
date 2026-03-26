<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class CurrencyService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey  = config('services.exchangerate.key');
        $this->baseUrl = config('services.exchangerate.url');
    }

    public function getConversion(float $amount, string $from, string $to): array
    {
       // https://api.fastforex.io/convert?api_key=20eab128ec-536c23b285-tcdvgr&from=USD&to=VES&amount=10
        $url = "{$this->baseUrl}/convert?api_key={$this->apiKey}&from={$from}&to={$to}&amount={$amount}";
        $response = Http::get($url);
        $data = $response->json();

        if ($response->failed() || !isset($data['result'])) {
            throw new Exception("Estructura de API inesperada o error de conexión.");
        }

          $convertedAmount = collect($data['result'])->except('rate')->first();

        return [
            'base'   => $data['base'] ?? $from,
            'amount' => $data['amount'] ?? $amount,
            'rate'   => $data['result']['rate'] ?? 0,
            'result' => $convertedAmount ?? 0,
        ];
    }


}
