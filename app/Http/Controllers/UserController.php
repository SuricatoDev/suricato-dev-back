<?php

namespace App\Http\Controllers;

use App\Models\Passageiro;
use App\Models\Organizador;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;
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

    public function update(Request $request, $id)
    {
        /** @var User $user */
        $user = Auth::user(); // Obtém o usuário autenticado

        // Verifica se o usuário está tentando editar outro usuário
        if ($user->id != $id) {
            return response()->json([
                'status' => false,
                'message' => 'Você só pode editar seu próprio perfil.'
            ], 403);
        }

        // Validação dos dados de entrada
        $validated = $request->validate([
            'password' => 'sometimes|string|min:6',
            'endereco' => 'sometimes|string',
            'cep' => 'sometimes|string|min:8|max:8',
            'cidade_id' => 'sometimes|integer|exists:cidades,id',
            'telefone' => 'sometimes|string|min:10|max:11',

            // Validações dinâmicas com base no tipo do usuário
            'nome' => 'required_if:tipo,passageiro|sometimes|string',
            'rg' => 'required_if:tipo,passageiro|sometimes|string',
            'data_nascimento' => 'required_if:tipo,passageiro|sometimes|date',

            'razao_social' => 'required_if:tipo,organizador|sometimes|string',
            'inscricao_estadual' => 'sometimes|string',
            'inscricao_municipal' => 'sometimes|string',
        ]);

        // Atualiza a senha se fornecida
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']); // Criptografa a senha usando Hash
        }

        // Atualiza os dados na tabela users (informações comuns)
        $user->update($validated);

        // Atualiza as informações específicas, dependendo do tipo de usuário
        if ($user->tipo == 'passageiro') {
            $passageiro = $user->passageiro; // Relação entre 'users' e 'passageiro'
            $passageiro->update([
                'nome' => $validated['nome'] ?? $passageiro->nome,
                'cpf' => $validated['cpf'] ?? $passageiro->cpf,
                'rg' => $validated['rg'] ?? $passageiro->rg,
                'data_nascimento' => $validated['data_nascimento'] ?? $passageiro->data_nascimento,
            ]);
        } elseif ($user->tipo == 'organizador') {
            $organizador = $user->organizador; // Relação entre 'users' e 'organizador'
            $organizador->update([
                'razao_social' => $validated['razao_social'] ?? $organizador->razao_social,
                'cnpj' => $validated['cnpj'] ?? $organizador->cnpj,
                'inscricao_estadual' => $validated['inscricao_estadual'] ?? $organizador->inscricao_estadual,
                'inscricao_municipal' => $validated['inscricao_municipal'] ?? $organizador->inscricao_municipal,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Perfil atualizado com sucesso!',
            'data' => $user
        ]);
    }

    public function destroy($id)
    {
        /** @var User $user */
        $user = Auth::user(); // Obtém o usuário autenticado

        // Verifica o id do usuário autenticado
        if ($user->id != $id) {
            return response()->json([
                'status' => false,
                'message' => 'Erro ao excluir o perfil.'
            ], 403);
        }

        // Inicia uma transação para garantir que todas as exclusões aconteçam com sucesso
        DB::beginTransaction();

        try {
            // Deleta as informações específicas do passageiro ou organizador
            if ($user->tipo == 'passageiro') {
                // Exclui o registro na tabela passageiros
                $user->passageiro()->delete();
            } elseif ($user->tipo == 'organizador') {
                // Exclui o registro na tabela organizadores
                $user->organizador()->delete();
            }

            // Deleta o usuário da tabela users
            $user->delete();

            // Se todas as exclusões forem feitas com sucesso, confirma a transação
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Perfil excluído com sucesso!'
            ]);
        } catch (\Exception $e) {
            // Se algum erro ocorrer, desfaz todas as exclusões realizadas na transação
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Erro ao excluir o perfil.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
