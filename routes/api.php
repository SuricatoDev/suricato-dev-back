<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('tokens/create', function (Request $request) {
    $token = $request->user()->createToken($request->token_name);

    return [
        'token' => $token->plainTextToken,
    ];
});

Route::post('login', [AuthController::class, 'login']);

Route::post('register', [UserController::class, 'register']);

Route::get('pagina1', function () {
    return 'Pagina 1';
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('me', [AuthController::class, 'me']);
    Route::get('logout', [AuthController::class, 'logout']);
});

//Rota de Teste
Route::any('ping', function () {
    return ['pong' => true];
});
