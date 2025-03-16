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
     *     description="Cria um novo usuário no sistema, com dados básicos, validando as informações fornecidas.",
     *     tags={"Usuários"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nome", "data_nascimento", "telefone", "email", "password"},
     *             @OA\Property(property="nome", type="string", example="João Silva", description="Nome completo do usuário"),
     *             @OA\Property(property="data_nascimento", type="string", format="date", example="1990-01-01", description="Data de nascimento do usuário"),
     *             @OA\Property(property="telefone", type="string", example="11987654321", description="Telefone de contato do usuário"),
     *             @OA\Property(property="email", type="string", example="joao.silva@email.com", description="E-mail do usuário"),
     *             @OA\Property(property="password", type="string", example="senha123", description="Senha do usuário (mínimo de 6 caracteres)"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuário registrado com sucesso.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Usuário registrado com sucesso!"),
     *             @OA\Property(property="user", type="object", description="Dados do usuário registrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação ou e-mail já cadastrado.",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="E-mail ja cadastrado")
     *         )
     *     )
     * )
     */

    public function register(Request $request)
    {
        if (User::where('email', $request->email)->exists()) {
            return response()->json(['error' => 'E-mail ja cadastrado'], 422);
        }

        $request->validate([
            'nome' => 'required|string|max:255',
            'data_nascimento' => 'required|date',
            'telefone' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'nome' => $request->nome,
            'data_nascimento' => $request->data_nascimento,
            'telefone' => $request->telefone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'ativo' => true,
        ]);

        return response()->json([
            'message' => 'Usuário registrado com sucesso!',
            'user' => $user,
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/register-organizador/{id}",
     *     summary="Registrar organizador",
     *     description="Registra um novo organizador no sistema, associando-o a um usuário existente.",
     *     tags={"Organizador"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do usuário que será registrado como organizador",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"razao_social", "cnpj"},
     *             @OA\Property(property="razao_social", type="string", example="Empresa Exemplo LTDA", description="Razão social da empresa organizadora"),
     *             @OA\Property(property="cnpj", type="string", example="12.345.678/0001-90", description="CNPJ da empresa organizadora"),
     *             @OA\Property(property="inscricao_estadual", type="string", example="123456789", description="Inscrição estadual da empresa (opcional)"),
     *             @OA\Property(property="inscricao_municipal", type="string", example="987654321", description="Inscrição municipal da empresa (opcional)"),
     *             @OA\Property(property="cadastur", type="boolean", example=true, description="Cadastro no Cadastur (opcional)"),
     *             @OA\Property(property="endereco", type="string", example="Avenida Exemplo, 456", description="Endereço da empresa"),
     *             @OA\Property(property="numero", type="string", example="456", description="Número do endereço"),
     *             @OA\Property(property="complemento", type="string", example="Sala 202", description="Complemento do endereço"),
     *             @OA\Property(property="bairro", type="string", example="Bairro Exemplo", description="Bairro da empresa"),
     *             @OA\Property(property="cep", type="string", example="98765-432", description="CEP da empresa"),
     *             @OA\Property(property="cidade", type="string", example="Cidade Exemplo", description="Cidade da empresa"),
     *             @OA\Property(property="estado", type="string", example="SP", description="Estado da empresa"),
     *             @OA\Property(property="organizador", type="boolean", example=true, description="Indica se o usuário deve ser registrado como organizador"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Organizador registrado com sucesso.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Organizador registrado com sucesso!"),
     *             @OA\Property(property="organizador", type="object", description="Dados do organizador registrado"),
     *             @OA\Property(property="user", type="object", description="Dados do usuário associado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao registrar organizador.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Usuário já cadastrado como organizador."),
     *             @OA\Property(property="error", type="string", example="Descrição do erro")
     *         )
     *     )
     * )
     */


    public function registerOrganizador(Request $request, $id)
    {
        try {
            $request->validate([
                'razao_social' => 'required|string|max:255',
                'cnpj' => 'required|string|unique:organizadores,cnpj',
                'inscricao_estadual' => 'nullable|string',
                'inscricao_municipal' => 'nullable|string',
                'cadastur' => 'nullable|boolean',
                'endereco' => 'nullable|string|max:255',
                'numero' => 'nullable|string|max:20',
                'complemento' => 'nullable|string|max:255',
                'bairro' => 'nullable|string|max:255',
                'cep' => 'nullable|string|max:9',
                'cidade' => 'nullable|string|max:255',
                'estado' => 'nullable|string|max:2',
                'organizador' => 'nullable|boolean',
            ]);

            DB::beginTransaction(); // Inicia a transação

            $organizador = Organizador::create([
                'id' => $id,
                'razao_social' => $request->razao_social,
                'cnpj' => $request->cnpj,
                'cadastur' => $request->cadastur,
                'inscricao_estadual' => $request->inscricao_estadual,
                'inscricao_municipal' => $request->inscricao_municipal,
            ]);

            $user = User::findOrFail($id);
            $user->update([
                'endereco' => $request->endereco,
                'numero' => $request->numero,
                'complemento' => $request->complemento,
                'bairro' => $request->bairro,
                'cep' => $request->cep,
                'cidade' => $request->cidade,
                'estado' => $request->estado,
                'organizador' => $request->organizador
            ]);

            DB::commit(); // Confirma a transação

            return response()->json([
                'message' => 'Organizador registrado com sucesso!',
                'organizador' => $organizador,
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Desfaz a transação em caso de erro
            return response()->json([
                'message' => 'Usuário já cadastrado como organizador.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/register-passageiro/{id}",
     *     summary="Registrar passageiro",
     *     description="Registra um novo passageiro no sistema, associando-o a um usuário existente.",
     *     tags={"Passageiro"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do usuário que será registrado como passageiro",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"cpf", "rg"},
     *             @OA\Property(property="cpf", type="string", example="123.456.789-00", description="CPF do passageiro"),
     *             @OA\Property(property="rg", type="string", example="12.345.678-9", description="RG do passageiro"),
     *             @OA\Property(property="endereco", type="string", example="Rua Exemplo, 123", description="Endereço do passageiro"),
     *             @OA\Property(property="numero", type="string", example="123", description="Número do endereço"),
     *             @OA\Property(property="complemento", type="string", example="Apto 101", description="Complemento do endereço"),
     *             @OA\Property(property="bairro", type="string", example="Centro", description="Bairro do passageiro"),
     *             @OA\Property(property="cep", type="string", example="12345-678", description="CEP do passageiro"),
     *             @OA\Property(property="cidade", type="string", example="Cidade Exemplo", description="Cidade do passageiro"),
     *             @OA\Property(property="estado", type="string", example="SP", description="Estado do passageiro"),
     *             @OA\Property(property="passageiro", type="boolean", example=true, description="Indica se o usuário é passageiro"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Passageiro registrado com sucesso.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Passageiro registrado com sucesso!"),
     *             @OA\Property(property="passageiro", type="object", description="Dados do passageiro registrado"),
     *             @OA\Property(property="user", type="object", description="Dados do usuário associado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao registrar passageiro.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao registrar passageiro."),
     *             @OA\Property(property="error", type="string", example="Descrição do erro")
     *         )
     *     )
     * )
     */


    public function registerPassageiro(Request $request, $id)
    {
        try {
            $request->validate([
                'cpf' => 'required|string|unique:passageiros,cpf|max:11',
                'rg' => 'required|string|max:14',
                'endereco' => 'nullable|string|max:255',
                'numero' => 'nullable|string|max:20',
                'complemento' => 'nullable|string|max:255',
                'bairro' => 'nullable|string|max:255',
                'cep' => 'nullable|string|max:20',
                'cidade' => 'nullable|string|max:255',
                'estado' => 'nullable|string|max:2',
                'passageiro' => 'nullable|boolean',
            ]);

            DB::beginTransaction(); // Inicia a transação

            $passageiro = Passageiro::create([
                'id' => $id,
                'cpf' => $request->cpf,
                'rg' => $request->rg,
            ]);

            $user = User::findOrFail($id);
            $user->update([
                'endereco' => $request->endereco,
                'numero' => $request->numero,
                'complemento' => $request->complemento,
                'bairro' => $request->bairro,
                'cep' => $request->cep,
                'cidade' => $request->cidade,
                'estado' => $request->estado,
                'passageiro' => $request->passageiro
            ]);

            DB::commit(); // Confirma a transação

            return response()->json([
                'message' => 'Passageiro registrado com sucesso!',
                'passageiro' => $passageiro,
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack(); // Desfaz a transação em caso de erro
            return response()->json([
                'message' => 'Erro ao registrar passageiro.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/verificar-email",
     *     summary="Verificar se um e-mail está cadastrado",
     *     description="Verifica se um e-mail já está cadastrado no sistema.",
     *     tags={"Autenticação"},
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
     * @OA\Put(
     *     path="/api/usuarios/{id}",
     *     summary="Atualizar perfil de usuário",
     *     description="Atualiza os dados do usuário, incluindo informações pessoais e específicas, dependendo do tipo de usuário (passageiro ou organizador).",
     *     tags={"Usuários"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do usuário",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nome"},
     *             @OA\Property(property="nome", type="string", example="João Silva", description="Nome do usuário"),
     *             @OA\Property(property="password", type="string", example="senha123", description="Nova senha do usuário (opcional)"),
     *             @OA\Property(property="data_nascimento", type="string", example="1990-05-15", description="Data de nascimento do usuário"),
     *             @OA\Property(property="endereco", type="string", example="Rua Exemplo, 123", description="Endereço do usuário"),
     *             @OA\Property(property="numero", type="string", example="123", description="Número do endereço"),
     *             @OA\Property(property="complemento", type="string", example="Apto 202", description="Complemento do endereço"),
     *             @OA\Property(property="bairro", type="string", example="Bairro Exemplo", description="Bairro do usuário"),
     *             @OA\Property(property="cep", type="string", example="98765-432", description="CEP do usuário"),
     *             @OA\Property(property="cidade", type="string", example="Cidade Exemplo", description="Cidade do usuário"),
     *             @OA\Property(property="estado", type="string", example="SP", description="Estado do usuário"),
     *             @OA\Property(property="telefone", type="string", example="11987654321", description="Telefone do usuário"),
     *             @OA\Property(property="foto_perfil", type="string", example="https://exemplo-servidor-s3.com/foto.jpg", description="URL da foto de perfil do usuário"),
     *             @OA\Property(property="razao_social", type="string", example="Empresa Exemplo LTDA", description="Razão social do organizador (opcional)"),
     *             @OA\Property(property="inscricao_estadual", type="string", example="123456789", description="Inscrição estadual do organizador (opcional)"),
     *             @OA\Property(property="inscricao_municipal", type="string", example="987654321", description="Inscrição municipal do organizador (opcional)"),
     *             @OA\Property(property="cadastur", type="boolean", example=true, description="Cadastro no Cadastur do organizador (opcional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Perfil do usuário atualizado com sucesso.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Perfil atualizado com sucesso!"),
     *             @OA\Property(property="data", type="object", example={"nome": "João Silva", "data_nascimento": "1990-05-15", "endereco": "Rua Exemplo, 123", "numero": "123", "complemento": "Apto 202", "bairro": "Bairro Exemplo", "cep": "98765-432", "cidade": "Cidade Exemplo", "estado": "SP", "telefone": "11987654321", "foto_perfil": "https://exemplo-servidor-s3.com/foto.jpg", "razao_social": "Empresa Exemplo LTDA", "inscricao_estadual": "123456789", "inscricao_municipal": "987654321"}, description="Dados atualizados do usuário"),
     *             @OA\Property(property="tipo_usuario", type="string", example="organizador", description="Tipo de usuário (passageiro ou organizador)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acesso não autorizado.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Acesso não autorizado.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao atualizar perfil do usuário.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao atualizar perfil."),
     *             @OA\Property(property="error", type="string", example="Descrição do erro")
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
            'nome' => 'sometimes|string|max:255',
            'password' => 'sometimes|string|min:6',
            'data_nascimento' => 'sometimes|date',
            'endereco' => 'sometimes|string',
            'numero' => 'sometimes|string',
            'complemento' => 'sometimes|string',
            'bairro' => 'sometimes|string',
            'cep' => 'sometimes|string|min:8|max:8',
            'cidade' => 'sometimes|string',
            'estado' => 'sometimes|string|min:2|max:2',
            'telefone' => 'sometimes|string|min:10|max:11',
            'foto_perfil' => 'sometimes|string',

            // Validações dinâmicas com base no tipo do usuário passageiro
            'rg' => 'sometimes|string',
            'numero_emergencia' => 'sometimes|string|max:11',
            'razao_social' => 'sometimes|string',
            'inscricao_estadual' => 'sometimes|string',
            'inscricao_municipal' => 'sometimes|string',
            'cadastur' => 'sometimes|boolean',
        ]);

        // Atualiza a senha se fornecida
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']); // Criptografa a senha usando Hash
        }

        // Atualiza os dados na tabela users (informações comuns)
        $user->update($validated);

        // Variável para armazenar o tipo de usuário atualizado
        $tipoUsuario = null;

        // Atualiza as informações específicas, dependendo do tipo de usuário
        if ($user->passageiro == true) {
            // Verifica se o passageiro existe antes de atualizar
            $passageiro = $user->passageiro; // Relação entre 'users' e 'passageiro'
            if ($passageiro) {
                $passageiro->update([
                    'rg' => $validated['rg'] ?? $passageiro->rg,
                    'numero_emergencia' => $validated['numero_emergencia'] ?? $passageiro->numero_emergencia
                ]);
                $tipoUsuario = 'passageiro';
            } else {
                // Caso o passageiro não exista, pode-se lançar um erro ou apenas retornar como tipo 'passageiro' mas sem dados.
                $tipoUsuario = 'passageiro';
            }
        } elseif ($user->organizador == true) {
            // Verifica se o organizador existe antes de atualizar
            $organizador = $user->organizador; // Relação entre 'users' e 'organizador'
            if ($organizador) {
                $organizador->update([
                    'razao_social' => $validated['razao_social'] ?? $organizador->razao_social,
                    'inscricao_estadual' => $validated['inscricao_estadual'] ?? $organizador->inscricao_estadual,
                    'inscricao_municipal' => $validated['inscricao_municipal'] ?? $organizador->inscricao_municipal,
                    'cadastur' => $validated['cadastur'] ?? $organizador->cadastur
                ]);
                $tipoUsuario = 'organizador';
            } else {
                $tipoUsuario = 'organizador';
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Perfil atualizado com sucesso!',
            'data' => $user,
            'tipo_usuario' => $tipoUsuario,
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
