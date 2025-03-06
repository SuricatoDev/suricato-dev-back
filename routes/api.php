<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CaravanaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RecoverPasswordCodeController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

//Rota de Teste
Route::any('test', function () {
    return ['API em funcionamento' => true,
        'Projeto' => 'Excursionistas - Gestão de Caravanas',
        'API' => 'v1.0.0',
        'Data de verificação' => now(),
        'Status' => 200,
        'Autor' => 'Filipe Lamego',
        'Email' => 'filipe.lamego@fatec.sp.gov.br',
        'GitHub' => 'https://github.com/SuricatoDev',
        'Faculdade' => 'Fatec Sorocaba',
        'Curso' => 'Análise e Desenvolvimento de Sistemas'
    ];
});

//Rotas Liberadas
Route::post('login', [AuthController::class, 'login']);

Route::post('register', [UserController::class, 'register']);

Route::post('verificar-email', [UserController::class, 'verificarEmail']);

Route::get('caravanas', [CaravanaController::class, 'index']);

//Rotas Forgot Password
Route::post('forgot-password-code', [RecoverPasswordCodeController::class, 'forgotPasswordCode']);
Route::post('reset-password-validate-code', [RecoverPasswordCodeController::class, 'resetPasswordValidateCode']);
Route::post('reset-password-code', [RecoverPasswordCodeController::class, 'resetPasswordCode']);

//Rotas Protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('caravanas', [CaravanaController::class, 'store']);
});
