<?php

namespace App\Http\Controllers\Api\V1\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Transaction\IndexRequest;
use App\Http\Resources\Api\V1\Transaction\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;


class IndexController extends Controller
{
    #[OA\Get(
        path: "/api/v1/transactions",
        summary: "Listado paginado de transacciones con filtros",
        tags: ["Transacciones"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "search",
                in: "query",
                description: "Buscar por código de referencia (TX-...)",
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "paginate",
                in: "query",
                description: "Resultados por página",
                schema: new OA\Schema(type: "integer", default: 10)
            ),
            new OA\Parameter(
                name: "sort_by",
                in: "query",
                description: "Campo de ordenación",
                schema: new OA\Schema(type: "string", enum: ["id", "reference_code", "status", "created_at"], default: "created_at")
            ),
            new OA\Parameter(
                name: "order_by",
                in: "query",
                description: "Dirección de orden (asc/desc)",
                schema: new OA\Schema(type: "string", enum: ["asc", "desc"], default: "desc")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Listado de transacciones recuperado con éxito",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Transacciones listadas con éxito."),
                        new OA\Property(property: "value", ref: "#/components/schemas/TransactionListResponse")
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "No autorizado - Token inválido o ausente"
            ),
            new OA\Response(
                response: 500,
                description: "Error interno del servidor",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Error al listar las transacciones."),
                        new OA\Property(property: "value", type: "string", example: "Mensaje de error técnico")
                    ]
                )
            )
        ]
    )]
    public function __invoke(IndexRequest $request): JsonResponse
    {
        try {
            $transactions = Transaction::filter($request)
                ->orderBy(
                    (! empty($request->sortBy)?$request->sortBy:config('api.defaults.sort_by')),
                    (! empty($request->orderBy)?$request->orderBy:config('api.defaults.order_by'))
                 )
                ->paginate($request->filled('paginate') ? $request->paginate : config('api.defaults.paginate'));
            $resource = TransactionResource::collection($transactions);
          return response()->json([
                'message' => __('transaction.index.message'),
                'value'   => $resource->response()->getData(true),
            ], Response::HTTP_OK);
          } catch (\Exception $ex) {
            Log::error( $ex->getMessage(), ['trace' => $ex->getTraceAsString()]);
            return response()->json([
                'message' =>  __('transaction.index.errors'),
                'value'   =>  $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
