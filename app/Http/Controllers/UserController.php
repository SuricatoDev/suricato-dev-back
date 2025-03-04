<?php

namespace App\Http\Controllers;

use App\Models\Passageiro;
use App\Models\Organizador;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function register(Request $request)
    {
        // Validações básicas e específicas para Passageiros ou Organizadores
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'tipo' => 'required|in:passageiro,organizador',

            // Validações gerais
            'endereco' => 'required',
            'cep' => 'required',
            'cidade_id' => 'required|exists:cidades,id',
            'telefone' => 'required',

            // Validações específicas para Passageiro
            'nome' => 'required_if:tipo,passageiro',
            'cpf' => 'required_if:tipo,passageiro|unique:passageiros,cpf',
            'rg' => 'required_if:tipo,passageiro',
            'data_nascimento' => 'required_if:tipo,passageiro|date',

            // Validações específicas para Organizador
            'razao_social' => 'required_if:tipo,organizador',
            'cnpj' => 'required_if:tipo,organizador|unique:organizadores,cnpj',
            'inscricao_estadual' => 'nullable',
            'inscricao_municipal' => 'nullable',
        ]);

        DB::beginTransaction();

        try {
            // Cria o usuário básico
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'endereco' => $request->endereco,
                'cep' => $request->cep,
                'cidade_id' => $request->cidade_id,
                'telefone' => $request->telefone,
                'tipo' => $request->tipo,
                'ativo' => true,
            ]);

            // Verifica o tipo de usuário para criar o registro correspondente
            if ($request->tipo == 'passageiro') {

                Passageiro::create([
                    'user_id' => $user->id,
                    'nome' => $request->nome,
                    'cpf' => $request->cpf,
                    'rg' => $request->rg,
                    'data_nascimento' => $request->data_nascimento,
                ]);
            } elseif ($request->tipo == 'organizador') {

                Organizador::create([
                    'user_id' => $user->id,
                    'razao_social' => $request->razao_social,
                    'cnpj' => $request->cnpj,
                    'inscricao_estadual' => $request->inscricao_estadual,
                    'inscricao_municipal' => $request->inscricao_municipal,
                ]);
            }

            // Commit da transação
            DB::commit();

            return response()->json([
                'message' => 'Usuário registrado com sucesso!',
                'user' => $user,
            ], 201);
        } catch (Exception $e) {
            //Rollback em caso de erro
            DB::rollBack();

            return response()->json([
                'message' => 'Erro ao registrar o usuário',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Rota para verificação de email
    public function verificarEmail(Request $request)
    {
        try {
            $email = $request->input('email');

            if (!$email) {
                return response()->json(['error' => 'O campo email é obrigatório'], 400);
            }

            $existe = DB::table('users')->where('email', $email)->exists();

            if ($existe) {
                return response()->json(['message' => 'E-mail encontrado'], 200);
            } else {
                return response()->json(['error' => 'E-mail não cadastrado'], 404);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao acessar o banco: ' . $e->getMessage());
            return response()->json(['error' => 'Erro interno no servidor'], 500);
        }
    }
}
