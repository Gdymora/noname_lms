<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TildaWebhookController;
use App\Http\Controllers\Api\YoomoneyP2PController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Вебхук для Тильды (POST запрос)
Route::post('/webhooks/tilda', [TildaWebhookController::class, 'handle']);
Route::post('/webhooks/yoomoney-p2p', [YoomoneyP2PController::class, 'handle']);