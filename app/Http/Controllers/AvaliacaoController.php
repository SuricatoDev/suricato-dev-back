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

            if ($avaliador->organizador) {
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

    public function listarPassageiros($caravana_id)
    {
        $user = Auth::user();
        $caravana = Caravana::findOrFail($caravana_id);

        // Verifica se o usuário autenticado é o organizador da caravana
        if ($caravana->organizador_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Acesso não autorizado! Somente o organizador pode visualizar os passageiros.',
            ], 403); // Acesso negado
        }

        // Obtém todas as reservas confirmadas da caravana
        $reservas = CaravanaPassageiro::where('caravana_id', $caravana->id)
            ->where('status', 'Confirmado')
            ->get();

        // Array para armazenar os dados dos passageiros
        $passageirosData = [];

        foreach ($reservas as $reserva) {
            // Busca o nome do passageiro
            $passageiro = User::find($reserva->passageiro_id);
            $usuario = $reserva->passageiro;

            // Carregar as avaliações do passageiro de forma eficiente, considerando o campo 'passageiro' como true
            $media = $usuario->avaliacao()
                ->where('passageiro', true)  // Verifica se o passageiro foi avaliado
                ->average('nota');  // Calcula a média diretamente

            // Se não houver avaliação, define como null
            $passageirosData[] = [
                'nota' => $media ?: null,  // Se média for 0 ou null, retornamos null
                'nome' => $passageiro->nome,
                'passageiro_id' => $reserva->passageiro_id,
                'caravana_id' => $reserva->caravana_id,
            ];
        }

        return response()->json([
            'status' => true,
            'data' => $passageirosData,
        ]);
    }
}
