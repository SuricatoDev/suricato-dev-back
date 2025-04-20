<?php

namespace App\Http\Controllers;

use App\Http\Requests\SuporteRequest;
use App\Mail\SuporteRequestMail;
use App\Models\Suporte;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SuporteController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/registrar-suporte",
     *     summary="Registrar um novo suporte",
     *     description="Esse endpoint permite que um usuário registre um suporte.",
     *     operationId="registrarSuporte",
     *     tags={"Suporte"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"descricao", "status"},
     *             @OA\Property(property="descricao", type="string", example="Problema ao acessar a caravana"),
     *             @OA\Property(property="status", type="string", enum={"Pendente", "Em andamento", "Concluido"}, example="Pendente")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Suporte registrado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Pedido de suporte enviado com sucesso!"),
     *             @OA\Property(property="suporte", type="object",
     *                 @OA\Property(property="titulo", type="string", example="Problema com a caravana"),
     *                 @OA\Property(property="descricao", type="string", example="Problema ao acessar a caravana"),
     *                 @OA\Property(property="status", type="string", enum={"Pendente", "Em andamento", "Concluido"}, example="Pendente"),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-20T12:34:56"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-20T12:34:56")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Usuário não autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Usuário não autenticado!")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Erro ao registrar suporte",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erro ao registrar o suporte!")
     *         )
     *     )
     * )
     */
    public function registrarSuporte(SuporteRequest $request)
    {
        // O SuporteRequest já valida os dados automaticamente
        $user = Auth::user();

        if (!$user) {
            // Tratar erro, o usuário não está autenticado
            return response()->json([
                'status'  => false,
                'message' => 'Usuário não autenticado!',
            ], 401);
        }

        // Criar o novo pedido de suporte
        $suporte = Suporte::create([
            'titulo'    => $request->titulo,
            'descricao' => $request->descricao,
            'status'    => 'Pendente',
            'user_id'   => $user->id,
        ]);

        // Enviar o e-mail com os dados do suporte
        Mail::to('noreply@excursionistas.com.br')->send(new SuporteRequestMail($suporte, $user));

        return response()->json([
            'status'  => true,
            'message' => 'Pedido de suporte enviado com sucesso!',
        ]);
    }
}
