<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AvaliacaoController;
use App\Http\Controllers\CaravanaController;
use App\Http\Controllers\CaravanaPassageiroController;
use App\Http\Controllers\CepController;
use App\Http\Controllers\CNPJController;
use App\Http\Controllers\DenunciaController;
use App\Http\Controllers\FavoritoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RecoverPasswordCodeController;
use App\Http\Controllers\SuporteController;
use App\Models\Favorito;
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
Route::get('caravanas/{id}', [CaravanaController::class, 'detalharCarvana']);
Route::get('caravanas', [CaravanaController::class, 'listarOuFiltrarCaravanas']);

//Rota para confirmar email
Route::get('confirmar-email/{token}', [AuthController::class, 'confirmarEmail']);

//Rota de teste upload
Route::post('teste-upload', [CaravanaController::class, 'testeUploadS3']);

//Rotas Forgot Password
Route::post('forgot-password-code', [RecoverPasswordCodeController::class, 'forgotPasswordCode']);
Route::post('reset-password-validate-code', [RecoverPasswordCodeController::class, 'resetPasswordValidateCode']);
Route::post('reset-password-code', [RecoverPasswordCodeController::class, 'resetPasswordCode']);

//Rotas Protegidas (Necessita estar logado)
Route::middleware('auth:sanctum')->group(function () {

    //Rota de teste de autenticação
    Route::get('me', [AuthController::class, 'me']);

    //Rotas Caravanas
    Route::post('caravanas', [CaravanaController::class, 'cadastrarCaravana']);
    Route::put('caravanas/{id}', [CaravanaController::class, 'editarCaravana']);
    Route::delete('caravanas/{id}', [CaravanaController::class, 'excluirCaravana']);
    Route::get('minhas-caravanas', [CaravanaController::class, 'listarMinhasCaravanas']);
    Route::get('caravanas/{passageiro_id}/historico', [CaravanaController::class, 'historicoCaravanas']);

    //Rotas Users
    Route::post('register-organizador/{id}', [UserController::class, 'registerOrganizador']);
    Route::post('register-passageiro/{id}', [UserController::class, 'registerPassageiro']);
    Route::get('user-data/{id}', [UserController::class, 'userData']);
    Route::put('users/{id}', [UserController::class, 'editarUsuario']);
    Route::delete('users/{id}', [UserController::class, 'excluirUsuario']);

    //Rota para atualizar a foto de perfil
    Route::post('update-foto-perfil/{id}', [UserController::class, 'updateFotoPerfil']);

    //Rotas Favoritos
    Route::get('users/{id}/favoritos', [FavoritoController::class, 'listarMeusFavoritos']);
    Route::post('caravanas/{id}/favoritar', [FavoritoController::class, 'favoritarCaravana']);
    Route::delete('caravanas/{id}/desfavoritar', [FavoritoController::class, 'desfavoritarCaravana']);

    //Rota para envio de e-mail de suporte
    Route::post('suporte', [SuporteController::class, 'registrarSuporte']);

    //Rota via CEP
    Route::get('cep/{cep}', [CepController::class, 'buscarCep']);

    //Rota Brasil API
    Route::get('cnpj/{cnpj}', [CNPJController::class, 'buscarCnpj']);

    //Rotas para gerenciar reservas
    Route::post('caravanas/{id}/reservas', [CaravanaPassageiroController::class, 'criarReserva']);
    Route::get('caravana/{id}/listar-reservas', [CaravanaPassageiroController::class, 'listarReservas']);
    Route::put('caravana/{id}/reserva/{reserva_id}', [CaravanaPassageiroController::class, 'editarReserva']);
    Route::delete('caravana/{id}/reserva/{reserva_id}', [CaravanaPassageiroController::class, 'cancelarReserva']);

    //Rotas para gerenciar Suporte
    Route::post('registrar-suporte', [SuporteController::class, 'registrarSuporte']);
    Route::get('listar-suporte', [SuporteController::class, 'listarSuporte']);
    Route::get('visualizar-suporte/{id}', [SuporteController::class, 'visualizarSuporte']);
    Route::put('editar-suporte/{id}', [SuporteController::class, 'editarSuporte']);
    Route::delete('excluir-suporte/{id}', [SuporteController::class, 'excluirSuporte']);

    //Rotas para gerenciar Avaliações
    Route::post('registrar-avaliacao', [AvaliacaoController::class, 'registrarAvaliacao']);
    Route::get('caravana/{caravana_id}/listar-passageiros', [AvaliacaoController::class, 'listarPassageiros']);
});
