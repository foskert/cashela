<?php

namespace App\Http\Controllers\Api\V1\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Transaction\StoreRequest;
use App\Http\Resources\Api\V1\Transaction\TransactionResource;
use App\Models\Currency;
use App\Models\Transaction;
use App\Services\CurrencyService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

class StoreController extends Controller
{
    protected $currencyService;
    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    #[OA\Post(
        path: "/api/v1/transactions",
        summary: "Crear una nueva transacción (Conversión de divisas)",
        tags: ["Transacciones"],
        security: [["sanctum" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["amount", "from_currency_id", "to_currency_id"],
                properties: [
                    new OA\Property(property: "amount", type: "number", format: "float", minimum: 0.01, example: 100.00),
                    new OA\Property(property: "from_currency_id", type: "integer", description: "ID de la moneda de origen", example: 1),
                    new OA\Property(property: "to_currency_id", type: "integer", description: "ID de la moneda de destino", example: 2),
                    new OA\Property(property: "description", type: "string", nullable: true, example: "Envío de remesa familiar")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Transacción creada y pendiente de aprobación",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Transacción registrada con éxito."),
                        new OA\Property(property: "value", ref: "#/components/schemas/TransactionResource")
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Error de validación - Fondos insuficientes o IDs de moneda inválidos",
                content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse")
            ),
            new OA\Response(
                response: 500,
                description: "Error interno del servidor",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Error al procesar la conversión."),
                        new OA\Property(property: "value", type: "string", example: "Servicio de tasa de cambio no disponible")
                    ]
                )
            )
        ]
    )]
    public function __invoke(StoreRequest $request):JsonResponse
    {
        return DB::transaction(function () use ($request):JsonResponse {
            $validated = $request->validated();
            try {
                $from = Currency::findOrFail($validated['from_currency_id']);
                $to   = Currency::findOrFail($validated['to_currency_id']);

                $conversion = $this->currencyService->getConversion(
                    $validated['amount'],
                    $from->code,
                    $to->code
                );

                $transaction = Transaction::create([
                    'user_id'             => $request->user()->id,
                    'amount_source'       => $validated['amount'],
                    'amount_destination'  => round($conversion['result'], 4),
                    'exchange_rate'       => round($conversion['rate'], 6),
                    'currency_source_id'      => $validated['from_currency_id'],
                    'currency_destination_id' => $validated['to_currency_id'],
                    'status'              => 'pending',
                    'description'         => $validated['description'] ?? 'Pago internacional',
                    'reference_code'      => 'REF-' . strtoupper(str()->random(10)),
                    'expires_at'          => now()->addMinutes(2),
                    'ip_address'          => $request->ip(),
                    'payload'                 => [],
                ]);
                $transaction->load(['sourceCurrency', 'destinationCurrency']);
                $resource = new TransactionResource($transaction);
                return response()->json([
                    'message' => __('product.store.message'),
                    'value'   => $resource,
                ], Response::HTTP_CREATED);
            }  catch (\Exception $ex) {
                Log::error("Error en StoreController: " . $ex->getMessage(), [
                    'request' => $request->all(),
                    'trace'   => $ex->getTraceAsString()
                ]);
                return response()->json([
                    'message' =>  __('product.store.errors'),
                    'value'   =>  $ex->getMessage()
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });
    }
}
