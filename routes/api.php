<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CaravanaController;
use App\Http\Controllers\CaravanaPassageiroController;
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

// Rota para visualizar todas as caravanas (não é necessário estar logado)
Route::get('caravanas', [CaravanaController::class, 'index']);

//Rotas Forgot Password
Route::post('forgot-password-code', [RecoverPasswordCodeController::class, 'forgotPasswordCode']);
Route::post('reset-password-validate-code', [RecoverPasswordCodeController::class, 'resetPasswordValidateCode']);
Route::post('reset-password-code', [RecoverPasswordCodeController::class, 'resetPasswordCode']);

//Rotas Protegidas (Necessita estar logado)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);

    //Rotas Caravanas
    Route::post('caravanas', [CaravanaController::class, 'store']);
    Route::put('caravanas/{id}', [CaravanaController::class, 'update']);
    Route::delete('caravanas/{id}', [CaravanaController::class, 'destroy']);

    //Rotas Users
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);

    //Rotas para gerenciar reservas
    Route::post('caravanas/{id}/reservas', [CaravanaPassageiroController::class, 'criarReserva']);
    Route::get('caravanas/{id}/minhas-reservas/{id_reserva}', [CaravanaPassageiroController::class, 'exibirMinhasReservas']);
    Route::get('caravanas/{id}/reservas/{id_reserva}', [CaravanaPassageiroController::class, 'visualizarReserva']);
    Route::put('caravanas/{id}/reservas/{id_reserva}', [CaravanaPassageiroController::class, 'editarReserva']);
    Route::delete('caravanas/{id}/reservas/{id_reserva}', [CaravanaPassageiroController::class, 'cancelarReserva']);
});
