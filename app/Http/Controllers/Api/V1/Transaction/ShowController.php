<?php

namespace App\Http\Controllers\Api\V1\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Transaction\ShowRequest;
use App\Http\Resources\Api\V1\Transaction\TransactionResource;
use App\Models\Transaction;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;

class ShowController extends Controller
{
    #[OA\Get(
        path: "/api/v1/transactions/{id}",
        summary: "Obtener detalles de una transacción",
        tags: ["Transacciones"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "UUID de la transacción a consultar",
                required: true,
                schema: new OA\Schema(type: "string", format: "uuid", example: "019d2b07-0246-7009-a520-38f5f22cb088")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Detalles de la transacción recuperados",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Transacción consultada correctamente."),
                        new OA\Property(property: "value", ref: "#/components/schemas/TransactionResource")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Transacción no encontrada",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "La transacción solicitada no existe."),
                        new OA\Property(property: "value", type: "null")
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Error interno del servidor"
            )
        ]
    )]
    public function __invoke(ShowRequest $request, $id): JsonResponse
    {
        try {
            $products = Transaction::with('getCurrency')->findOrFail($id);
            $resource = new TransactionResource($products);
            return response()->json([
                'message' => __('product.show.message'),
                'value'   => $resource,
            ], Response::HTTP_OK);
        } catch (\Exception $ex) {
            Log::error( $ex->getMessage(), ['trace' => $ex->getTraceAsString()]);
            return response()->json([
                'message' =>  __('product.show.errors'),
                'value'   =>  $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
