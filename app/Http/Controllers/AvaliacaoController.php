<?php

namespace App\Http\Controllers;

use App\Models\Avaliacao;
use App\Models\Caravana;
use App\Models\CaravanaPassageiro;
use App\Models\Passageiro;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpMyAdmin\Config\Validator;

class AvaliacaoController extends Controller
{
    /**
     * @OA\Post(
     *     path="/registrar-avaliacao",
     *     summary="Registrar uma nova avaliação",
     *     description="Esse endpoint permite que um passageiro avalie uma caravana ou um organizador, ou que um organizador avalie um passageiro.",
     *     operationId="registrarAvaliacao",
     *     tags={"Avaliação"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"nota"},
     *             @OA\Property(property="organizador_id", type="integer", description="ID do organizador (opcional)"),
     *             @OA\Property(property="passageiro_id", type="integer", description="ID do passageiro (opcional, necessário para organizador avaliar passageiro)"),
     *             @OA\Property(property="nota", type="integer", description="Nota da avaliação (1 a 5)", example=5),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Avaliação registrada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Avaliação registrada com sucesso!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação dos dados",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="errors", type="object", example={"nota": "Este campo é obrigatório."})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao registrar avaliação",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erro ao registrar avaliação: erro interno.")
     *         )
     *     )
     * )
     */
    public function registrarAvaliacao(Request $request)
    {
        try {
            $request->validate([
                'organizador_id' => 'nullable|exists:users,id',
                'passageiro_id' => 'nullable|exists:users,id',
                'caravana_id' => 'nullable|exists:caravanas,id',
                'nota' => 'required|integer|min:1|max:5',
            ]);

            DB::beginTransaction();

            $avaliador = Auth::user(); // Obtém o usuário autenticado

            if ($avaliador->id === $request->passageiro_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'O passageiro nao pode avaliar ele mesmo.'
                ]);
            }

            // Verifica se o passageiro já foi avaliado nesta caravana, pois não o passageiro não pode ser avaliado mais de uma vez
            $avaliacaoExistente = Avaliacao::where('passageiro_id', $request->passageiro_id)
                ->where('caravana_id', $request->caravana_id)
                ->first();

            if ($avaliacaoExistente) {
                return response()->json([
                    'status' => false,
                    'message' => 'O passageiro já foi avaliado nesta caravana.'
                ]);
            }

            if ($avaliador->organizador && !$request->organizador_id) {
                // sou organizador, avaliando um passageiro
                $avaliacao = Avaliacao::create([
                    'organizador_id' => $avaliador->id,
                    'passageiro_id'  => $request->passageiro_id,
                    'caravana_id'    => $request->caravana_id,
                    'passageiro'     => true,
                    'nota'           => $request->nota,
                ]);
            } else {
                // sou passageiro, avaliando um organizador
                $avaliacao = Avaliacao::create([
                    'organizador_id' => $request->organizador_id,
                    'passageiro_id'  => $avaliador->id,
                    'caravana_id'    => $request->caravana_id,
                    'organizador'    => true,
                    'nota'           => $request->nota,
                ]);
            }

            $avaliacao->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Avaliação registrada com sucesso!'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Erro ao registrar avaliação: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/caravana/{caravana_id}/listar-passageiros",
     *     summary="Listar passageiros de uma caravana",
     *     description="Retorna todos os passageiros confirmados de uma caravana, incluindo as avaliações médias dos passageiros.",
     *     operationId="listarPassageiros",
     *     tags={"Avaliação"},
     *     security={{ "bearerAuth":{} }},
     *
     *     @OA\Parameter(
     *         name="caravana_id",
     *         in="path",
     *         description="ID da caravana",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Passageiros listados com sucesso.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="nota", type="number", format="float", example=4.5),
     *                     @OA\Property(property="nome", type="string", example="João da Silva"),
     *                     @OA\Property(property="passageiro_id", type="integer", example=123),
     *                     @OA\Property(property="caravana_id", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Acesso não autorizado. Somente o organizador pode visualizar os passageiros.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Acesso não autorizado! Somente o organizador pode visualizar os passageiros.")
     *         )
     *     )
     * )
     */

    public function listarPassageiros(int $caravana_id)
    {
        $user = Auth::user();
        $caravana = Caravana::findOrFail($caravana_id);

        // Verifica se o usuário autenticado é o organizador da caravana
        if ($caravana->organizador_id !== $user->id) {
            return response()->json([
                'status'  => false,
                'message' => 'Acesso não autorizado! Somente o organizador pode visualizar os passageiros.',
            ], 403);
        }

        // Busca todas as reservas confirmadas desta caravana
        $reservas = CaravanaPassageiro::where('caravana_id', $caravana_id)
            ->where('status', 'Confirmado')
            ->get();

        $passageirosData = [];

        foreach ($reservas as $reserva) {
            // Busca o nome do passageiro
            $passageiro = User::find($reserva->passageiro_id);
            // Relação inversa: $reserva->passageiro retorna o User
            $usuario = $reserva->passageiro;

            // Tenta buscar a avaliação exata deste passageiro nesta caravana
            $avaliacao = $usuario->avaliacao()
                ->where('passageiro', true)
                ->where('caravana_id', $caravana_id)
                ->first();

            if ($avaliacao) {
                $nota = $avaliacao->nota;
            } else {
                // Se não houver avaliação direta, calcular média (pode ser null)
                $nota = $usuario->avaliacao()
                    ->where('passageiro', true)
                    ->where('caravana_id', $caravana_id)
                    ->average('nota');
            }

            $passageirosData[] = [
                'nota'          => $nota !== null ? $nota : null,
                'nome'          => $passageiro->nome,
                'passageiro_id' => $reserva->passageiro_id,
                'caravana_id'   => $caravana_id,
            ];
        }

        return response()->json([
            'status' => true,
            'data'   => $passageirosData,
        ]);
    }
}
