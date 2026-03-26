<?php

namespace App\Http\Controllers\Api\V1\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Transaction\UpdateRequest;
use App\Http\Resources\Api\V1\Transaction\TransactionResource;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class UpdateController extends Controller
{
    #[OA\Put(
        path: "/api/v1/transactions/{id}",
        summary: "Actualizar estado de una transacción (Evaluación)",
        tags: ["Transacciones"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "UUID de la transacción a actualizar",
                required: true,
                schema: new OA\Schema(type: "string", format: "uuid", example: "019d2b07-0246-7009-a520-38f5f22cb088")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["status"],
                properties: [
                    new OA\Property(
                        property: "status",
                        type: "string",
                        enum: ["completed", "failed"],
                        description: "Nuevo estado de la transacción",
                        example: "completed"
                    ),
                    new OA\Property(
                        property: "description",
                        type: "string",
                        nullable: true,
                        example: "Comprobante de transferencia verificado."
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Transacción actualizada con éxito",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Transacción actualizada correctamente."),
                        new OA\Property(property: "value", ref: "#/components/schemas/TransactionResource")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Transacción no encontrada",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "La transacción no existe."),
                        new OA\Property(property: "value", type: "null")
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Datos de validación inválidos",
                content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse")
            ),
            new OA\Response(response: 500, description: "Error interno del servidor")
        ]
    )]
    public function __invoke(UpdateRequest $request, $id)  :JsonResponse
    {
        return DB::transaction(function () use ($request, $id):JsonResponse {
            try {
                $transaction = Transaction::find($id);
                if (!$transaction) {
                    return response()->json([
                        'message' => __('transaction.update.not_found'),
                        'value'   => $transaction
                    ], Response::HTTP_NOT_FOUND);
                }

                $transaction->update($request->validated());
                return response()->json([
                    'message' => __('transaction.update.message'),
                    'value'   => new TransactionResource($transaction),
                ], Response::HTTP_OK);

            } catch (\Throwable $ex) {
                Log::error( $ex->getMessage(), ['trace' => $ex->getTraceAsString()]);
                return response()->json([
                    'message' =>  __('product.update.errors'),
                    'value'   =>  $ex->getMessage()
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        });
    }
}
