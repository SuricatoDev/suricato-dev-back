<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CaravanaController;
use App\Http\Controllers\CaravanaPassageiroController;
use App\Http\Controllers\CepController;
use App\Http\Controllers\DenunciaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RecoverPasswordCodeController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

//Rota de Teste
Route::any('test', function () {
    return [
        'API em funcionamento' => true,
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
Route::post('logout', [AuthController::class, 'logout']);
Route::post('register', [UserController::class, 'register']);
Route::post('verificar-email', [UserController::class, 'verificarEmail']);

// Rota para visualizar todas as caravanas (não é necessário estar logado)
Route::get('caravanas', [CaravanaController::class, 'listarCaravanas']);

//Rotas Forgot Password
Route::post('forgot-password-code', [RecoverPasswordCodeController::class, 'forgotPasswordCode']);
Route::post('reset-password-validate-code', [RecoverPasswordCodeController::class, 'resetPasswordValidateCode']);
Route::post('reset-password-code', [RecoverPasswordCodeController::class, 'resetPasswordCode']);

//Rotas Protegidas (Necessita estar logado)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [AuthController::class, 'me']);

    //Rotas Caravanas
    Route::post('caravanas', [CaravanaController::class, 'cadastrarCaravana']);
    Route::put('caravanas/{id}', [CaravanaController::class, 'editarCaravana']);
    Route::delete('caravanas/{id}', [CaravanaController::class, 'excluirCaravana']);

    //Rotas Users
    Route::post('register-organizador/{id}', [UserController::class, 'registerOrganizador']);
    Route::post('register-passageiro/{id}', [UserController::class, 'registerPassageiro']);
    Route::put('users/{id}', [UserController::class, 'editarUsuario']);
    Route::delete('users/{id}', [UserController::class, 'excluirUsuario']);

    //Rota via CEP
    Route::get('/cep/{cep}', [CepController::class, 'buscarCep']);

    //Rotas para gerenciar reservas
    Route::post('caravanas/{id}/reservas', [CaravanaPassageiroController::class, 'criarReserva']);
    Route::get('caravanas/{id}/minhas-reservas/{reserva_id}', [CaravanaPassageiroController::class, 'exibirMinhasReservas']);
    Route::get('caravanas/{id}/reservas/{reserva_id}', [CaravanaPassageiroController::class, 'visualizarReserva']);
    Route::put('caravanas/{id}/reservas/{reserva_id}', [CaravanaPassageiroController::class, 'editarReserva']);
    Route::delete('caravanas/{id}/reservas/{reserva_id}', [CaravanaPassageiroController::class, 'cancelarReserva']);

    //Rotas para gerenciar Denuncias
    Route::post('registrar-denuncia', [DenunciaController::class, 'registrarDenuncia']);
    Route::put('editar-denuncia/{id}', [DenunciaController::class, 'editarDenuncia']);
    Route::delete('excluir-denuncia/{id}', [DenunciaController::class, 'excluirDenuncia']);
});
