<?php

namespace App\Http\Controllers\Api\V1\Currency;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Currency\IndexRequest;
use App\Http\Resources\Api\V1\Currency\CurrencyResource;
use App\Models\Currency;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;


class IndexController extends Controller
{
    #[OA\Get(
        path: "/api/v1/currencies",
        summary: "Listado de monedas activas",
        tags: ["Monedas"],
        security: [["sanctum" => []]],
        description: "Retorna el listado de todas las divisas habilitadas para operaciones en Cashela.",
        responses: [
            new OA\Response(
                response: 200,
                description: "Listado de monedas recuperado con éxito",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Monedas listadas con éxito."),
                        new OA\Property(
                            property: "value",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "name", type: "string", example: "Dólar Estadounidense"),
                                    new OA\Property(property: "code", type: "string", example: "USD"),
                                    new OA\Property(property: "symbol", type: "string", example: "$"),
                                    new OA\Property(property: "exchange_rate", type: "string", example: "1.00")
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "No autorizado"
            ),
            new OA\Response(
                response: 500,
                description: "Error interno del servidor"
            )
        ]
    )]
    public function __invoke(IndexRequest $request): JsonResponse
    {
        try {
            $currency = Currency::where('is_active', true)->get();
            $resource = CurrencyResource::collection($currency);
            return response()->json([
                'message' => __('product.index.message'),
                'value'   => $resource->response()->getData(true),
            ], Response::HTTP_OK);
        } catch (\Exception $ex) {
            Log::error( $ex->getMessage(), ['trace' => $ex->getTraceAsString()]);
            return response()->json([
                'message' =>  __('product.index.errors'),
                'value'   =>  $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
