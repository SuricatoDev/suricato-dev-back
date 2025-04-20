<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordCodeRequest;
use App\Http\Requests\ResetPasswordValidateCodeRequest;
use App\Mail\SendEmailForgetPasswordCode;
use App\Models\User;
use App\Services\ResetPasswordValidateCodeService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\HasApiTokens;

class RecoverPasswordCodeController extends Controller
{
    use HasApiTokens;

    /**
     * Enviar código de recuperação de senha para o e-mail do usuário.
     *
     * Este método verifica se existe um usuário com o e-mail fornecido no banco de dados.
     * Se o usuário for encontrado, gera um código de recuperação de senha, salva-o no banco de dados
     * e o envia para o e-mail do usuário. Se o usuário não for encontrado, retorna uma resposta de erro.
     * Se ocorrer algum erro durante o processo, registra o erro e retorna uma resposta de erro.
     *
     * @param ForgotPasswordRequest $request O request contendo o e-mail do usuário
     * @return \Illuminate\Http\JsonResponse Resposta indicando sucesso ou falha
     */

    public function forgotPasswordCode(ForgotPasswordRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            Log::warning('Tentativa recuperar senha com e-mail não cadastrado.', ['email' => $request->email]);

            return response()->json([
                'status' => false,
                'message' => 'E-mail não encontrado!',
            ], 400);
        }

        try {
            $userPasswordResets = DB::table('password_reset_tokens')->where('email', $request->email);

            if ($userPasswordResets) {
                $userPasswordResets->delete();
            }

            $code = mt_rand(100000, 999999);
            $token = Hash::make($code);

            DB::table('password_reset_tokens')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => Carbon::now(),
            ]);

            $tenMinutesLater = Carbon::now('America/Sao_Paulo')->addMinutes(10);
            $formattedTime = $tenMinutesLater->format('H:i');
            $formattedDate = $tenMinutesLater->format('d/m/Y');

            Mail::to($user->email)->send(new SendEmailForgetPasswordCode($user, $code, $formattedDate, $formattedTime));

            Log::info('Recuperar senha.', ['email' => $request->email]);

            return response()->json([
                'status' => true,
                'message' => 'Enviado e-mail com instruções para recuperar a senha. Acesse a sua caixa de e-mail para recuperar a senha!',
            ], 200);
        } catch (Exception $e) {
            Log::warning('Erro recuperar senha.', ['email' => $request->email, 'error' => $e->getMessage()]);

            return response()->json([
                'status' => false,
                'message' => 'Erro recuperar senha. Tente mais tarde!',
            ], 400);
        }
    }

    /**
     * Validar o código de recuperação de senha enviado pelo usuário.
     *
     * Este método valida o código de recuperação de senha enviado pelo usuário.
     * Utiliza o serviço ResetPasswordValidateCodeService para validar o código. Se o código for válido,
     * retorna uma resposta de sucesso. Caso contrário, retorna uma resposta de erro.
     *
     * @param ResetPasswordValidateCodeRequest $request O request contendo o e-mail e o código de recuperação de senha
     * @param ResetPasswordValidateCodeService $ResetPasswordValidateCodeService O serviço utilizado para validar o código de recuperação de senha
     * Injeção de Dependência: o Laravel automaticamente resolve e injeta uma instância dessa classe no método quando é chamado.
     * @return \Illuminate\Http\JsonResponse Resposta indicando sucesso ou falha na validação do código
     */

    public function resetPasswordValidateCode(ResetPasswordValidateCodeRequest $request, ResetPasswordValidateCodeService $resetPasswordValidateCode): JsonResponse
    {
        try {
            $validationResult = $resetPasswordValidateCode->resetPasswordValidateCode($request->email, $request->code);

            if (!$validationResult['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $validationResult['message'],
                ], 400);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                Log::notice('Usuário não encontrado.', ['email' => $request->email]);

                return response()->json([
                    'status' => false,
                    'message' => 'Usuário não encontrado!',
                ], 400);
            }

            Log::info('Código recuperar senha válido.', ['email' => $request->email]);

            return response()->json([
                'status' => true,
                'message' => 'Código recuperar senha válido!',
            ], 200);
        } catch (Exception $e) {
            Log::warning('Erro validar código recuperar senha.', ['email' => $request->email, 'error' => $e->getMessage()]);

            return response()->json([
                'status' => false,
                'message' => 'Código inválido!',
            ], 400);
        }
    }

    /**
     * Resetar a senha do usuário com base no código de recuperação.
     *
     * Este método resetar a senha do usuário com base no código de recuperação enviado pelo usuário.
     * Utiliza o serviço ResetPasswordValidateCodeService para validar o código. Se o código for válido, atualiza a senha
     * do usuário no banco de dados e retorna uma resposta de sucesso com o token de acesso JWT.
     * Caso contrário, retorna uma resposta de erro.
     *
     * @param ResetPasswordCodeRequest $request O request contendo o e-mail, o código de recuperação de senha e a nova senha
     * @param ResetPasswordValidateCodeService $resetPasswordValidateCode O serviço utilizado para validar o código de recuperação de senha
     * Injeção de Dependência: o Laravel automaticamente resolve e injeta uma instância dessa classe no método quando é chamado.
     * @return \Illuminate\Http\JsonResponse Resposta indicando sucesso ou falha na resetar da senha do usuário
     */

    public function resetPasswordCode(ResetPasswordCodeRequest $request, ResetPasswordValidateCodeService $resetPasswordValidateCode): JsonResponse
    {
        try {
            $validationResult = $resetPasswordValidateCode->resetPasswordValidateCode($request->email, $request->code);

            if (!$validationResult['status']) {
                return response()->json([
                    'status' => false,
                    'message' => $validationResult['message'],
                ], 400);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                Log::notice('Usuário não encontrado.', ['email' => $request->email]);

                return response()->json([
                    'status' => false,
                    'message' => 'Usuário não encontrado!',
                ], 400);
            }

            $user->update([
                'password' => Hash::make($request->password),
            ]);

            // Gerar token de acesso utilizando Laravel Sanctum
            $token = $user->createToken('api-token')->plainTextToken;

            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            Log::info('Senha atualizada com sucesso.', ['email' => $request->email]);

            return response()->json([
                'status' => true,
                'user' => $user,
                'token' => $token, // Token no formato Bearer
                'message' => 'Senha atualizada com sucesso!',
            ], 200);
        } catch (Exception $e) {
            Log::warning('Senha não atualizada.', ['email' => $request->email, 'error' => $e->getMessage()]);

            return response()->json([
                'status' => false,
                'message' => 'Senha não atualizada!',
            ], 400);
        }
    }
}
