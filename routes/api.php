<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

//Rota de Teste
Route::any('ping', function () {
    return ['pong' => true];
});

//Rotas Liberadas
Route::post('login', [AuthController::class, 'login']);

Route::post('register', [UserController::class, 'register']);

//Rotas Protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('me', [AuthController::class, 'me']);
    Route::get('logout', [AuthController::class, 'logout']);
});

