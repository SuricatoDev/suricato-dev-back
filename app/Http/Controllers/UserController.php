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

/**
 *
 * @OA\Tag(
 *     name="Usuários",
 *     description="Rotas relacionadas aos Usuários"
 * )
 */

class UserController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Registrar um novo usuário",
     *     description="Registra um novo usuário como Passageiro ou Organizador.",
     *     tags={"Usuários"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password", "tipo", "endereco", "cep", "cidade_id", "telefone"},
     *             @OA\Property(property="email", type="string", format="email", example="usuario@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="123456"),
     *             @OA\Property(property="tipo", type="string", enum={"passageiro", "organizador"}, example="passageiro"),
     *             @OA\Property(property="endereco", type="string", example="Rua Exemplo, 123"),
     *             @OA\Property(property="cep", type="string", example="12345678"),
     *             @OA\Property(property="cidade_id", type="integer", example=1),
     *             @OA\Property(property="telefone", type="string", example="11987654321"),
     *
     *             @OA\Property(property="nome", type="string", example="João Silva", description="Necessário se for passageiro"),
     *             @OA\Property(property="cpf", type="string", example="12345678900", description="Necessário se for passageiro"),
     *             @OA\Property(property="rg", type="string", example="12345678-9", description="Necessário se for passageiro"),
     *             @OA\Property(property="data_nascimento", type="string", format="date", example="1990-05-15", description="Necessário se for passageiro"),
     *
     *             @OA\Property(property="razao_social", type="string", example="Empresa Exemplo LTDA", description="Necessário se for organizador"),
     *             @OA\Property(property="cnpj", type="string", example="12345678000199", description="Necessário se for organizador"),
     *             @OA\Property(property="inscricao_estadual", type="string", nullable=true, example="123456789"),
     *             @OA\Property(property="inscricao_municipal", type="string", nullable=true, example="987654321"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuário registrado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Usuário registrado com sucesso!"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="email", type="string", example="usuario@example.com"),
     *                 @OA\Property(property="tipo", type="string", example="passageiro"),
     *                 @OA\Property(property="endereco", type="string", example="Rua Exemplo, 123"),
     *                 @OA\Property(property="cep", type="string", example="12345678"),
     *                 @OA\Property(property="cidade_id", type="integer", example=1),
     *                 @OA\Property(property="telefone", type="string", example="11987654321")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dados inválidos"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao registrar o usuário"
     *     )
     * )
     */

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
    /**
     * @OA\Post(
     *     path="/api/verificar-email",
     *     summary="Verificar se um e-mail está cadastrado",
     *     description="Verifica se um e-mail já está cadastrado no sistema.",
     *     tags={"Usuários"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="usuario@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="E-mail encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="E-mail encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Campo email obrigatório",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="O campo email é obrigatório")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="E-mail não cadastrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="E-mail não cadastrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno no servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Erro interno no servidor")
     *         )
     *     )
     * )
     */

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

    /**
     * Atualiza os dados do usuário autenticado.
     *
     * @OA\Put(
     *     path="/api/users/{id}",
     *     tags={"Usuários"},
     *     summary="Atualiza os dados do usuário autenticado",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do usuário",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"password", "telefone"},
     *             @OA\Property(property="password", type="string", example="12345678"),
     *             @OA\Property(property="telefone", type="string", example="11987654321"),
     *             @OA\Property(property="endereco", type="string", example="Rua Exemplo, 123"),
     *             @OA\Property(property="cep", type="string", example="12345678"),
     *             @OA\Property(property="cidade_id", type="integer", example=1),
     *             @OA\Property(property="nome", type="string", example="João Silva"),
     *             @OA\Property(property="data_nascimento", type="string", format="date", example="1990-05-15")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Perfil atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Perfil atualizado com sucesso!"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Usuário não autorizado a editar outro perfil",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Você só pode editar seu próprio perfil.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erro de validação."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
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
                'rg' => $validated['rg'] ?? $passageiro->rg,
                'data_nascimento' => $validated['data_nascimento'] ?? $passageiro->data_nascimento,
            ]);
        } elseif ($user->tipo == 'organizador') {
            $organizador = $user->organizador; // Relação entre 'users' e 'organizador'
            $organizador->update([
                'razao_social' => $validated['razao_social'] ?? $organizador->razao_social,
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

    // Método para excluir um usuário
    /**
     * @OA\Delete(
     *     path="/api/usuarios/{id}",
     *     summary="Excluir o próprio perfil",
     *     description="Permite que um usuário autenticado exclua seu próprio perfil. Exclui também os dados associados de passageiro ou organizador.",
     *     tags={"Usuários"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do usuário a ser excluído",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Perfil excluído com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Perfil excluído com sucesso!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Erro ao excluir o perfil (tentativa de excluir outro usuário)",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erro ao excluir o perfil.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno ao excluir o perfil",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erro ao excluir o perfil."),
     *             @OA\Property(property="error", type="string", example="Detalhes do erro interno")
     *         )
     *     )
     * )
     */

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
