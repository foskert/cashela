<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Currency;
use App\Http\Controllers\Api\V1\Transaction;
use App\Http\Controllers\Api\V1\Audits;
use App\Http\Controllers\Api\V1\Users;
use Symfony\Component\HttpFoundation\Response;

Route::prefix('v1')->group(function () {
    Route::post('login', Users\UserController::class);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', function (Request $request) {
            $user = $request->user();

            if ($user && $user->currentAccessToken()) {
                $user->currentAccessToken()->delete();
                return response()->json(['message' => __('validation.token')], Response::HTTP_ACCEPTED);
            }else{
                return response()->json(['message' => __('validation.401')], Response::HTTP_UNAUTHORIZED);
            }
        });
        Route::prefix('currencies')->group(function () {
            Route::get('/',          Currency\IndexController::class)->name('api.currencies.index');
            Route::get('/check',    Currency\CheckController::class)->name('api.currencies.check');
        });
        Route::prefix('transactions')->group(function () {
            Route::get('/',          Transaction\IndexController::class)->name('api.transactions.index');
            Route::post('/',         Transaction\StoreController::class)->name('api.transactions.store');
            Route::get('/{id}',      Transaction\ShowController::class)->name('api.transactions.show');
            Route::put('/{id}',      Transaction\UpdateController::class)->name('api.transactions.update');
        });
    });
    Route::prefix('audits')->group(function () {
        Route::get('/transactions/{id}', Audits\IndexController::class);
    });
    Route::fallback(function () {
        return response()->json(['message' => __('validation.404')], Response::HTTP_NOT_FOUND);
    });
});
