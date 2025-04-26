<?php

namespace App\Http\Controllers;

use App\Models\Organizador;
use App\Models\Passageiro;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Info(
 *      version="1.3.0",
 *      title="Documentação - API SuricatoDev (Projeto Excursionistas)",
 *      description="Esta API fornece endpoints para a gestão de caravanas, passageiros e organizadores, realizar reservas, denúncias e avaliações.
 *      🚀 **Principais funcionalidades:**
 *      - Gerenciar caravanas
 *      - Gerenciar Passageiros
 *      - Gerenciar organizadores
 *      - Realização de reservas
 *      - Denúncias e avaliações de caravanas, passageiros e organizadores
 *      🔒 **Segurança:**
 *      - Autenticação segura via token Bearer
 *      - Utiliza Laravel Sanctum para autenticação
 *      - Requer token Bearer para acesso a endpoints protegidos",
 *      @OA\Contact(
 *          name="Suporte Suricato Dev",
 *          email="filipe.lamego@fatec.sp.gov.br"
 *      ),
 *      @OA\License(
 *          name="MIT",
 *          url="https://opensource.org/licenses/MIT"
 *      )
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Bearer {token}",
 *     description="Autenticação via Bearer Token usando Laravel Sanctum"
 * )
 *
 * @OA\Tag(
 *     name="Login e Logout",
 *     description="Rotas relacionadas ao login e logout"
 * )
 */

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Autentica um usuário e retorna um token Bearer",
     *     tags={"Autenticação"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="usuario@email.com"),
     *             @OA\Property(property="password", type="string", format="password", example="123456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuário autenticado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Usuário logado com sucesso!"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="João da Silva"),
     *                 @OA\Property(property="email", type="string", format="email", example="usuario@email.com"),
     *             ),
     *             @OA\Property(property="passageiro", type="object",
     *                 @OA\Property(property="id", type="integer", nullable=true, example=null),
     *                 @OA\Property(property="cpf", type="string", nullable=true, example=null),
     *                 @OA\Property(property="rg", type="string", nullable=true, example=null),
     *                 @OA\Property(property="contato_emergencia", type="string", nullable=true, example=null),
     *             ),
     *             @OA\Property(property="organizador", type="object",
     *                 @OA\Property(property="id", type="integer", nullable=true, example=null),
     *                 @OA\Property(property="razao_social", type="string", nullable=true, example=null),
     *                 @OA\Property(property="cnpj", type="string", nullable=true, example=null),
     *                 @OA\Property(property="cadastur", type="string", nullable=true, example=null),
     *                 @OA\Property(property="inscricao_estadual", type="string", nullable=true, example=null),
     *                 @OA\Property(property="inscricao_municipal", type="string", nullable=true, example=null),
     *             ),
     *             @OA\Property(property="access_token", type="string", example="1|abcde12345"),
     *             @OA\Property(property="token_type", type="string", example="Bearer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Erro ao realizar login, usuário ou senha incorretos",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao realizar login. Usuário ou senha incorretos.")
     *         )
     *     ),
     * )
     */

    public function login(Request $request)
    {
        // Validação dos dados de entrada
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Busca o usuário pelo e-mail
        $user = User::where('email', $request->email)->first();

        // Verificação de credenciais de login
        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password
        ])) {
            // Se o login for bem-sucedido, cria o token e retorna os dados

            /** @var User $user */
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            // Armazena os dados do passageiro e organizador
            $passageiro = Passageiro::where('id', $user->id)->first();
            $organizador = Organizador::where('id', $user->id)->first();

            return response()->json([
                'message' => 'Usuário logado com sucesso!',
                'user' => $user, // Retorna os dados do usuário logado
                'passageiro' => $passageiro, // Retorna os dados do passageiro, mesmo que seja nulo
                'organizador' => $organizador, // Retorna os dados do organizador, mesmo que seja nulo
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 200);
        }

        // Se o login falhar, retorna uma mensagem de erro
        return response()->json([
            'message' => 'Erro ao realizar login. Usuário ou senha incorretos.',
        ], 401);
    }


    /**
     * @OA\Get(
     *     path="/api/confirmar-email/{token}",
     *     summary="Confirma o e-mail de um usuário a partir do token de verificação",
     *     tags={"Autenticação"},
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         description="Token de verificação enviado por e-mail",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="E-mail confirmado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="E-mail confirmado com sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Token inválido",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Token inválido")
     *         )
     *     )
     * )
     */

    public function confirmarEmail($token)
    {
        // Procurando o usuário pelo token de verificação
        $user = User::where('email_verification_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Token inválido'], 204);
        }

        // Atualizando o status do e-mail do usuário para "verificado"
        $user->email_verified_at = now();
        $user->verificado = true;
        $user->email_verification_token = null; // Apagando o token após a verificação
        $user->save();

        return response()->json(['message' => 'E-mail confirmado com sucesso'], 200);
    }

    public function me()
    {
        return Auth::user();
    }
}
