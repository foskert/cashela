<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Currency\IndexRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

class UserDashboardController extends Controller
{
    public function index(IndexRequest $request)
    {
        $request = Request::create(route('api.currencies.index'), 'GET');
        $response = Route::dispatch($request);
        $currencies = [];
        if ($response->isSuccessful()) {
            $content = json_decode($response->getContent(), true);
            $currencies = $content['value']['data'] ?? [];
        }
        return view('dashboard', compact('currencies'));
    }
    public function check(Request $request)
    {
        $queryParams = [
            'amount'        => $request->query('amount'),
            'from_currency' => $request->query('from_currency_id'),
            'to_currency'   => $request->query('to_currency_id'),
        ];
        $internalRequest = Request::create(
            route('api.currencies.check'),
            'GET',
            $queryParams
        );
        $response = Route::dispatch($internalRequest);
        return response()->json(
            json_decode($response->getContent(), true),
            $response->getStatusCode()
        );
    }

    public function request(Request $request)
    {
        $params = [
            'amount'           => $request->query('amount'),
            'from_currency_id' => $request->query('from_currency_id'),
            'to_currency_id'   => $request->query('to_currency_id'),
        ];
        $internalRequest = Request::create(route('api.transactions.store'), 'POST', $params);
        auth()->shouldUse('api');
        $internalRequest->setUserResolver(fn () => auth()->user());
        $internalRequest->headers->set('Accept', 'application/json');

        $response = Route::dispatch($internalRequest);

        return response()->json(
            json_decode($response->getContent(), true),
            $response->getStatusCode()
        );
    }
    public function operations(Request $request)
    {

        $internalRequest = Request::create(route('api.transactions.index'), 'GET', $request->query());
        auth()->shouldUse('api');
        $internalRequest->setUserResolver(fn () => auth()->user());
        $internalRequest->headers->set('Accept', 'application/json');
        $response = Route::dispatch($internalRequest);
        return response()->json(
            json_decode($response->getContent(), true),
            $response->getStatusCode()
        );

    }
}
