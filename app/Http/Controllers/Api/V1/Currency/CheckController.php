<?php

namespace App\Http\Controllers\Api\V1\Currency;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Currency\CheckRequest;
use App\Http\Resources\Api\V1\Currency\CheckResource;
use App\Models\Currency;
use App\Services\CurrencyService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;


class CheckController extends Controller
{
    protected $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    #[OA\Get(
        path: "/api/v1/currencies/check",
        summary: "Cotizar conversión de moneda",
        description: "Calcula el monto de destino y la tasa de cambio actual sin crear una transacción.",
        tags: ["Monedas"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "from_currency_id",
                in: "query",
                required: true,
                description: "ID de la moneda de origen",
                schema: new OA\Schema(type: "integer", example: 1)
            ),
            new OA\Parameter(
                name: "to_currency_id",
                in: "query",
                required: true,
                description: "ID de la moneda de destino",
                schema: new OA\Schema(type: "integer", example: 2)
            ),
            new OA\Parameter(
                name: "amount",
                in: "query",
                required: true,
                description: "Monto a convertir",
                schema: new OA\Schema(type: "number", format: "float", example: 100.00)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Cotización calculada con éxito",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Cotización realizada con éxito."),
                        new OA\Property(
                            property: "value",
                            properties: [
                                new OA\Property(property: "amount", type: "number", example: 100.00),
                                new OA\Property(property: "rate", type: "number", example: 45.15),
                                new OA\Property(property: "result", type: "number", example: 4515.00),
                                new OA\Property(property: "from", type: "string", example: "USD"),
                                new OA\Property(property: "to", type: "string", example: "VES")
                            ],
                            type: "object"
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Error de validación (Parámetros faltantes o monedas inválidas)"
            ),
            new OA\Response(
                response: 500,
                description: "Error al conectar con el servicio de tasas"
            )
        ]
    )]
    public function __invoke(CheckRequest $request): JsonResponse
    {
        $validated = $request->validated();
        try {
            $from = Currency::findOrFail($validated['from_currency_id']);
            $to   = Currency::findOrFail($validated['to_currency_id']);
            $conversion = $this->currencyService->getConversion(
                $validated['amount'],
                $from->code,
                $to->code
            );
            return response()->json([
                'message' => __('product.index.message'),
                'value'   => new CheckResource($conversion),
            ], Response::HTTP_OK);

        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json([
                'message' => __('product.index.errors'),
                'value'   => $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
