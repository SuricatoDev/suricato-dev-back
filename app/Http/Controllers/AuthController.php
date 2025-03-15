<?php

namespace App\Http\Controllers;

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
     *             @OA\Property(property="access_token", type="string", example="1|abcde12345"),
     *             @OA\Property(property="token_type", type="string", example="Bearer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Erro ao realizar login, usuário ou senha incorretos",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao realizar login. Verifique suas credenciais.")
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

            return response()->json([
                'message' => 'Usuário logado com sucesso!',
                'user' => $user, // Retorna os dados do usuário logado
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
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Desloga o usuário autenticado",
     *     tags={"Autenticação"},

     *     @OA\Response(
     *         response=200,
     *         description="Logout realizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logout realizado com sucesso!"),
     *             @OA\Property(property="status", type="boolean", example=true)
     *         )
     *     ),
     * )
     */


    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(
            [
                'message' => 'Logout realizado com sucesso!',
                'status' => true
            ],
            200
        );
    }

    public function me()
    {
        return Auth::user();
    }
}
