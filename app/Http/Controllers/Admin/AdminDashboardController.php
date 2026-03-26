<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


class AdminDashboardController extends Controller
{
    public function index() {
        return view('admin.dashboard');
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
   public function evaluation(Request $request)
    {
        $id = $request->query('id');
        $stateValue = $request->query('state');
        $params = [
            'status' => $stateValue,
        ];
        $internalRequest = Request::create(
            route('api.transactions.update', ['id' => $id]),
            'PUT',
            $params
        );
        auth()->shouldUse('api');
        $internalRequest->setUserResolver(fn () => auth()->user());
        $internalRequest->headers->set('Accept', 'application/json');
        $internalRequest->headers->set('Content-Type', 'application/json');
        $response = Route::dispatch($internalRequest);
        return response()->json(
            json_decode($response->getContent(), true),
            $response->getStatusCode()
        );
    }
}
