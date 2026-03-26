<?php

namespace App\Http\Controllers\Api\V1;

use OpenApi\Attributes as OA;
#[OA\Info(
    title: "Cashela API",
    version: "1.0.0",
    description: "API para el sistema de conversiones y auditoría de Cashela"
)]
#[OA\Server(
    url: "http://localhost:8000",
    description: "Servidor Local Docker"
)]

// 1. Esquema para Errores de Validación
#[OA\Schema(
    schema: "ValidationErrorResponse",
    properties: [
        new OA\Property(property: "message", type: "string", example: "The status field is required."),
        new OA\Property(
            property: "errors",
            type: "object",
            additionalProperties: new OA\AdditionalProperties(
                type: "array",
                items: new OA\Items(type: "string")
            )
        )
    ]
)]

// 2. Esquema para Auditoría
#[OA\Schema(
    schema: "AuditResource",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "event", type: "string", example: "created"),
        new OA\Property(property: "auditable_type", type: "string", example: "App\\Models\\Transaction"),
        new OA\Property(property: "old_values", type: "object", nullable: true),
        new OA\Property(property: "new_values", type: "object", nullable: true),
        new OA\Property(property: "user_id", type: "integer", example: 1, nullable: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time")
    ]
)]

// 3. Esquema para Listado de Transacciones (El que te dio error recién)
#[OA\Schema(
    schema: "TransactionListResponse",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "source_amount", type: "number", format: "float", example: 100.00),
        new OA\Property(property: "target_amount", type: "number", format: "float", example: 3600.00),
        new OA\Property(property: "exchange_rate", type: "number", format: "float", example: 36.00),
        new OA\Property(property: "created_at", type: "string", format: "date-time")
    ]
)]

// 4. Esquema para Monedas (Por si acaso lo pide el listado)
#[OA\Schema(
    schema: "CurrencyResource",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "code", type: "string", example: "USD"),
        new OA\Property(property: "name", type: "string", example: "United States Dollar"),
        new OA\Property(property: "symbol", type: "string", example: "$")
    ]
)]
#[OA\Schema(
    schema: "TransactionResource",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "source_amount", type: "number", format: "float", example: 100.00),
        new OA\Property(property: "target_amount", type: "number", format: "float", example: 36.50),
        new OA\Property(property: "exchange_rate", type: "number", format: "float", example: 0.365),
        new OA\Property(property: "source_currency", ref: "#/components/schemas/CurrencyResource"),
        new OA\Property(property: "target_currency", ref: "#/components/schemas/CurrencyResource"),
        new OA\Property(property: "created_at", type: "string", format: "date-time")
    ]
)]
class SwaggerConfig {}
