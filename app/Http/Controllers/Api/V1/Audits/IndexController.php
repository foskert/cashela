<?php

namespace App\Http\Controllers\Api\V1\Audits;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Audits\IndexRequest;
use App\Http\Resources\Api\V1\Audits\AuditResource;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;

class IndexController extends Controller
{
    #[OA\Get(
        path: "/api/v1/audits/transactions/{id}",
        summary: "Consultar historial de auditoría de una transacción",
        description: "Obtiene el registro detallado de todos los cambios realizados sobre una transacción específica (creación, cambios de estado, etc).",
        tags: ["Auditoría"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "UUID de la transacción a auditar",
                required: true,
                schema: new OA\Schema(type: "string", format: "uuid", example: "019d2b07-0246-7009-a520-38f5f22cb088")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Historial de cambios recuperado con éxito",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Auditoría de transacción consultada con éxito."),
                        new OA\Property(
                            property: "value",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/AuditResource")
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Transacción no encontrada"
            ),
            new OA\Response(
                response: 500,
                description: "Error interno del servidor"
            )
        ]
    )]
    public function __invoke(IndexRequest $request, int $id)
    {
        try {
            $product = Transaction::withTrashed()->findOrFail($id);
            $audits = $product->audits()->with('user')->get();
            return response()->json([
                'message' => __('validation.audit.message'),
                'value'   => AuditResource::collection($audits),
            ]);
        }  catch (\Exception $ex) {
            Log::error( $ex->getMessage(), ['trace' => $ex->getTraceAsString()]);
            return response()->json([
                'message' =>  __('validation.audit.errors'),
                'value'   =>  $ex->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
