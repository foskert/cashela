<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Transaction;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\User\UserDashboardController;

Route::get('/', function () {
    return view('welcome');
});


Route::middleware(['auth', 'verified'])->group(function () {
    Route::middleware(['role:user'])->group(function () {
        Route::get('/dashboard',     [UserDashboardController::class, 'index'])->name('dashboard');
        Route::get('/check',         [UserDashboardController::class, 'check'])->name('check');
        Route::get('/request',       [UserDashboardController::class, 'request'])->name('request');
        Route::get('/operations',    [UserDashboardController::class, 'operations'])->name('operations');
    });

    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/dashboard',     [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/admin/operations',    [AdminDashboardController::class, 'operations'])->name('admin.operations');
        Route::get('/admin/evaluation',    [AdminDashboardController::class, 'evaluation'])->name('admin.evaluation');
    });
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
