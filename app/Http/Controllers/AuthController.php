<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validação dos dados de entrada
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Verificação de credenciais de login
        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password
        ])) {
            // Se o login for bem-sucedido, cria o token e retorna os dados
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Usuário logado com sucesso!',
                'user' => $user, // Retorna os dados do usuário logado
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 200);  // Status 200 para sucesso
        }

        // Se o login falhar, retorna uma mensagem de erro
        return response()->json([
            'message' => 'Erro ao realizar login. Verifique suas credenciais.',
        ], 401);  // Status 401 para erro de autenticação
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(
            [
                'message' => 'Successfully logged out',
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
