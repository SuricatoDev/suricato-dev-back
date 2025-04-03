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
use Illuminate\Support\Facades\Storage;

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
     *             @OA\Property(property="nome_fantasia", type="string", example="Empresa Exemplo", description="Nome fantasia da empresa organizadora (opcional)"),
     *             @OA\Property(property="cnpj", type="string", example="12.345.678/0001-90", description="CNPJ da empresa organizadora"),
     *             @OA\Property(property="inscricao_estadual", type="string", example="123456789", description="Inscrição estadual da empresa (opcional)"),
     *             @OA\Property(property="inscricao_municipal", type="string", example="987654321", description="Inscrição municipal da empresa (opcional)"),
     *             @OA\Property(property="cadastur", type="boolean", example=true, description="Cadastro no Cadastur (opcional)"),
     *             @OA\Property(property="telefone_comercial", type="string", example="1234567890", description="Telefone comercial da empresa (opcional)"),
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
     *             @OA\Property(property="message", type="string", example="Erro ao registrar organizador."),
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
                'nome_fantasia' => 'nullable|string|max:255',
                'cnpj' => 'required|string|unique:organizadores,cnpj',
                'inscricao_estadual' => 'nullable|string',
                'inscricao_municipal' => 'nullable|string',
                'cadastur' => 'nullable|boolean',
                'telefone_comercial' => 'nullable|string|max:20',
                'endereco' => 'nullable|string|max:255',
                'numero' => 'nullable|string|max:20',
                'complemento' => 'nullable|string|max:255',
                'bairro' => 'nullable|string|max:255',
                'cep' => 'required|string|max:9',
                'cidade' => 'nullable|string|max:255',
                'estado' => 'nullable|string|max:2',
                'organizador' => 'nullable|boolean',
            ]);

            DB::beginTransaction(); // Inicia a transação

            $organizador = Organizador::create([
                'id' => $id,
                'razao_social' => $request->razao_social,
                'nome_fantasia' => $request->nome_fantasia,
                'cnpj' => $request->cnpj,
                'cadastur' => $request->cadastur,
                'inscricao_estadual' => $request->inscricao_estadual,
                'inscricao_municipal' => $request->inscricao_municipal,
                'telefone_comercial' => $request->telefone_comercial,
                'endereco' => $request->endereco,
                'numero' => $request->numero,
                'complemento' => $request->complemento,
                'bairro' => $request->bairro,
                'cep' => $request->cep,
                'cidade' => $request->cidade,
                'estado' => $request->estado
            ]);

            $user = User::findOrFail($id);

            $user->update([
                'organizador' => true
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
                'message' => 'Erro ao registrar organizador.',
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
                'contato_emergencia' => 'nullable|string|max:20',
                'endereco' => 'nullable|string|max:255',
                'numero' => 'nullable|string|max:20',
                'complemento' => 'nullable|string|max:255',
                'bairro' => 'nullable|string|max:255',
                'cep' => 'required|string|max:20',
                'cidade' => 'nullable|string|max:255',
                'estado' => 'nullable|string|max:2',
                'passageiro' => 'nullable|boolean',
            ]);

            DB::beginTransaction(); // Inicia a transação

            $passageiro = Passageiro::create([
                'id' => $id,
                'cpf' => $request->cpf,
                'rg' => $request->rg,
                'contato_emergencia' => $request->contato_emergencia,
                'endereco' => $request->endereco,
                'numero' => $request->numero,
                'complemento' => $request->complemento,
                'bairro' => $request->bairro,
                'cep' => $request->cep,
                'cidade' => $request->cidade,
                'estado' => $request->estado
            ]);

            $user = User::findOrFail($id);

            $user->update([
                'passageiro' => true
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
     * @OA\Get(
     *     path="/api/user-data/{id}",
     *     summary="Obtém os dados de um usuário, incluindo informações de passageiro e organizador se aplicável.",
     *     tags={"Usuários"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do usuário",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dados do usuário retornados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="João da Silva"),
     *                 @OA\Property(property="email", type="string", format="email", example="usuario@email.com"),
     *                 @OA\Property(property="passageiro", type="boolean", example=true),
     *                 @OA\Property(property="organizador", type="boolean", example=false)
     *             ),
     *             @OA\Property(property="passageiro", type="object", nullable=true,
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="cpf", type="string", example="123.456.789-00"),
     *                 @OA\Property(property="rg", type="string", example="12.345.678-9"),
     *                 @OA\Property(property="contato_emergencia", type="string", example="+55 11 98765-4321")
     *             ),
     *             @OA\Property(property="organizador", type="object", nullable=true,
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
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuário não encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Usuário não encontrado.")
     *         )
     *     )
     * )
     */


    public function userData($id)
    {
        // Buscar o usuário pelo ID
        $user = User::findOrFail($id);

        // Buscar dados de passageiro e organizador, se existirem
        $passageiro = $user->passageiro ? Passageiro::where('id', $user->id)->first() : null;
        $organizador = $user->organizador ? Organizador::where('id', $user->id)->first() : null;

        return response()->json([
            'user' => $user,
            'passageiro' => $passageiro,
            'organizador' => $organizador,
        ], 200);
    }


    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Atualizar perfil de usuário",
     *     description="Atualiza os dados do usuário autenticado. Apenas os campos enviados serão modificados.
     *                  O usuário pode atualizar seus dados gerais e, se aplicável, os dados específicos de passageiro ou organizador.",
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
     *             @OA\Property(property="nome", type="string", example="João Silva", description="Nome do usuário"),
     *             @OA\Property(property="password", type="string", example="senha123", description="Nova senha do usuário (opcional)"),
     *             @OA\Property(property="data_nascimento", type="string", example="1990-05-15", description="Data de nascimento do usuário"),
     *             @OA\Property(property="telefone", type="string", example="11987654321", description="Telefone do usuário"),
     *             @OA\Property(property="foto_perfil", type="string", example="https://exemplo-servidor-s3.com/foto.jpg", description="URL da foto de perfil do usuário"),
     *
     *             @OA\Property(
     *                 property="passageiro",
     *                 type="object",
     *                 nullable=true,
     *                 description="Dados do passageiro (opcional, enviado apenas se o usuário quiser atualizar informações de passageiro).",
     *                 @OA\Property(property="rg", type="string", example="123456789", description="RG do passageiro"),
     *                 @OA\Property(property="contato_emergencia", type="string", example="11987654321", description="Contato de emergência"),
     *                 @OA\Property(property="endereco", type="string", example="Rua Exemplo, 123", description="Endereço do passageiro"),
     *                 @OA\Property(property="numero", type="string", example="123", description="Número do endereço do passageiro"),
     *                 @OA\Property(property="complemento", type="string", example="Apto 101", description="Complemento do endereço do passageiro"),
     *                 @OA\Property(property="bairro", type="string", example="Bairro Exemplo", description="Bairro do passageiro"),
     *                 @OA\Property(property="cep", type="string", example="98765-432", description="CEP do passageiro"),
     *                 @OA\Property(property="cidade", type="string", example="Cidade Exemplo", description="Cidade do passageiro"),
     *                 @OA\Property(property="estado", type="string", example="SP", description="Estado do passageiro")
     *             ),
     *
     *             @OA\Property(
     *                 property="organizador",
     *                 type="object",
     *                 nullable=true,
     *                 description="Dados do organizador (opcional, enviado apenas se o usuário quiser atualizar informações de organizador).",
     *                 @OA\Property(property="razao_social", type="string", example="Empresa Exemplo LTDA", description="Razão social do organizador"),
     *                 @OA\Property(property="inscricao_estadual", type="string", example="123456789", description="Inscrição estadual do organizador"),
     *                 @OA\Property(property="cadastur", type="boolean", example=true, description="Cadastro no Cadastur do organizador"),
     *                 @OA\Property(property="telefone_comercial", type="string", example="11234567890", description="Telefone comercial do organizador"),
     *                 @OA\Property(property="endereco", type="string", example="Av. Paulista, 2000", description="Endereço do organizador"),
     *                 @OA\Property(property="numero", type="string", example="2000", description="Número do endereço do organizador"),
     *                 @OA\Property(property="complemento", type="string", example="Apto 101", description="Complemento do endereço do organizador"),
     *                 @OA\Property(property="bairro", type="string", example="Bairro Exemplo", description="Bairro do organizador"),
     *                 @OA\Property(property="cep", type="string", example="01310-000", description="CEP do organizador"),
     *                 @OA\Property(property="cidade", type="string", example="São Paulo", description="Cidade do organizador"),
     *                 @OA\Property(property="estado", type="string", example="SP", description="Estado do organizador")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Perfil do usuário atualizado com sucesso.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Perfil atualizado com sucesso!"),
     *             @OA\Property(property="data", type="object", example={"campos do tipo de usuário (passageiro ou organizador)"}, description="Dados atualizados do usuário"),
     *             @OA\Property(property="tipo_usuario", type="string", example="organizador", description="Tipo de usuário atualizado (passageiro ou organizador, se aplicável)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acesso não autorizado.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Perfil diferente do usuário autenticado.")
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


    public function editarUsuario(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if ($user->id != $id) {
            return response()->json([
                'message' => 'Perfil diferente do usuário autenticado.'
            ], 403);
        }

        // Validação dos dados comuns da tabela users
        $validated = $request->validate([
            'nome' => 'sometimes|string|max:255',
            'password' => 'sometimes|string|min:6',
            'data_nascimento' => 'sometimes|date',
            'telefone' => 'sometimes|string|min:10|max:11',
        ]);

        // Atualiza os dados comuns do usuário
        if (!empty($validated)) {
            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }
            $user->update($validated);
        }

        $detalhes = null;
        $message = '';

        // Atualiza os dados de passageiro se enviados
        if ($request->has('passageiro')) {
            $this->updatePassageiro($user, $request);
            $detalhes = Passageiro::where('id', $user->id)->first();
            $message = 'Perfil de passageiro atualizado com sucesso!';
        }

        // Atualiza os dados de organizador se enviados
        if ($request->has('organizador')) {
            $this->updateOrganizador($user, $request);
            $detalhes = Organizador::where('id', $user->id)->first();
            $message = 'Perfil de organizador atualizado com sucesso!';
        }

        return response()->json([
            'status' => true,
            'data' => $user,
            'detalhes' => $detalhes,
            'message' => $message
        ]);
    }

    private function updatePassageiro($user, $request)
    {
        $validated = $request->validate([
            'passageiro.rg' => 'sometimes|string|max:20',
            'passageiro.contato_emergencia' => 'sometimes|string|max:20',
            'passageiro.endereco' => 'sometimes|string|max:255',
            'passageiro.numero' => 'sometimes|string|max:10',
            'passageiro.complemento' => 'nullable|string|max:255',
            'passageiro.bairro' => 'sometimes|string|max:100',
            'passageiro.cep' => 'sometimes|string|max:9',
            'passageiro.cidade' => 'sometimes|string|max:100',
            'passageiro.estado' => 'sometimes|string|max:2',
        ]);

        $passageiro = Passageiro::where('id', $user->id)->first();
        if ($passageiro) {
            $passageiro->update([
                'rg' => $validated['passageiro']['rg'] ?? $passageiro->rg,
                'contato_emergencia' => $validated['passageiro']['contato_emergencia'] ?? $passageiro->contato_emergencia,
                'endereco' => $validated['passageiro']['endereco'] ?? $passageiro->endereco,
                'numero' => $validated['passageiro']['numero'] ?? $passageiro->numero,
                'complemento' => $validated['passageiro']['complemento'] ?? $passageiro->complemento,
                'bairro' => $validated['passageiro']['bairro'] ?? $passageiro->bairro,
                'cep' => $validated['passageiro']['cep'] ?? $passageiro->cep,
                'cidade' => $validated['passageiro']['cidade'] ?? $passageiro->cidade,
                'estado' => $validated['passageiro']['estado'] ?? $passageiro->estado
            ]);

            return response()->json([
                'message' => 'Perfil de passageiro atualizado com sucesso!',
                'data' => $passageiro,
                'user' => $user
            ]);
        }
    }

    private function updateOrganizador($user, $request)
    {
        $validated = $request->validate([
            'organizador.razao_social' => 'sometimes|string|max:255',
            'organizador.inscricao_estadual' => 'nullable|string|max:20',
            'organizador.inscricao_municipal' => 'nullable|string|max:20',
            'organizador.cadastur' => 'nullable|string|max:50',
            'organizador.telefone_comercial' => 'sometimes|string|max:20',
            'organizador.endereco' => 'sometimes|string|max:255',
            'organizador.numero' => 'sometimes|string|max:10',
            'organizador.complemento' => 'nullable|string|max:255',
            'organizador.bairro' => 'sometimes|string|max:100',
            'organizador.cep' => 'sometimes|string|max:9',
            'organizador.cidade' => 'sometimes|string|max:100',
            'organizador.estado' => 'sometimes|string|max:2',
        ]);

        $organizador = Organizador::where('id', $user->id)->first();
        if ($organizador) {
            $organizador->update([
                'razao_social' => $validated['organizador']['razao_social'] ?? $organizador->razao_social,
                'inscricao_estadual' => $validated['organizador']['inscricao_estadual'] ?? $organizador->inscricao_estadual,
                'inscricao_municipal' => $validated['organizador']['inscricao_municipal'] ?? $organizador->inscricao_municipal,
                'cadastur' => $validated['organizador']['cadastur'] ?? $organizador->cadastur,
                'telefone_comercial' => $validated['organizador']['telefone_comercial'] ?? $organizador->telefone_comercial,
                'endereco' => $validated['organizador']['endereco'] ?? $organizador->endereco,
                'numero' => $validated['organizador']['numero'] ?? $organizador->numero,
                'complemento' => $validated['organizador']['complemento'] ?? $organizador->complemento,
                'bairro' => $validated['organizador']['bairro'] ?? $organizador->bairro,
                'cep' => $validated['organizador']['cep'] ?? $organizador->cep,
                'cidade' => $validated['organizador']['cidade'] ?? $organizador->cidade,
                'estado' => $validated['organizador']['estado'] ?? $organizador->estado
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Perfil de organizador atualizado com sucesso!',
            'data' => $organizador,
            'user' => $user
        ]);
    }

    // Método para fazer upload de foto de perfil
    /**
     * @OA\Post(
     *     path="/api/update-foto-perfil/{id}",
     *     summary="Atualizar foto de perfil",
     *     description="Permite que um usuário autenticado atualize sua foto de perfil.",
     *     tags={"Usuários"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Property(
     *                 property="foto_perfil",
     *                 type="file",
     *                 format="binary",
     *                 description="Foto de perfil em formato JPEG ou PNG",
     *                 required=true
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Foto de perfil atualizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Foto de perfil atualizada com sucesso!"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Usuário não autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Usuário não autenticado"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erro de validação"),
     *             @OA\Property(property="errors", type="object"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno no servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erro interno no servidor"),
     *         )
     *     )
     * )
     */


    public function updateFotoPerfil(Request $request)
    {
        // Valida a imagem
        $request->validate([
            'foto_perfil' => 'required|image|mimes:jpeg,png,jpg,gif', // Limites de tipo de imagem
        ]);


        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Verifica se o arquivo de imagem foi enviado
        if ($request->hasFile('foto_perfil')) {
            $file = $request->file('foto_perfil'); // Obtém o arquivo de imagem

            // Armazena a imagem no S3 com o caminho baseado no ID do usuário
            $path = $file->store("usuarios/{$user->id}", 's3');

            // Verifica a URL da imagem armazenada no S3
            $url = Storage::disk('s3')->url($path);

            // Atualiza o campo foto_perfil no banco de dados
            $user->foto_perfil = $url;
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Foto de perfil atualizada com sucesso!',
                'data' => $user, // Retorna os dados atualizados do usuário
            ]);
        }

        // Caso nenhum arquivo tenha sido enviado
        return response()->json([
            'status' => false,
            'message' => 'Nenhuma foto foi enviada.',
        ], 400);
    }


    // Método para excluir um usuário
    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
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

    public function excluirUsuario($id)
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
