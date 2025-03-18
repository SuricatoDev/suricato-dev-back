<?php

namespace App\Http\Controllers;

use App\Models\Avaliacao;
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
     *             @OA\Property(property="caravana_id", type="integer", description="ID da caravana (opcional)"),
     *             @OA\Property(property="organizador_id", type="integer", description="ID do organizador (opcional)"),
     *             @OA\Property(property="passageiro_id", type="integer", description="ID do passageiro (opcional, necessário para organizador avaliar passageiro)"),
     *             @OA\Property(property="nota", type="integer", description="Nota da avaliação (1 a 5)", example=5),
     *             @OA\Property(property="comentario", type="string", description="Comentário adicional sobre a avaliação", example="Excelente viagem!"),
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
                'caravana_id' => 'nullable|exists:caravanas,id',
                'organizador_id' => 'nullable|exists:users,id',
                'passageiro_id' => 'nullable|exists:users,id',
                'nota' => 'required|integer|min:1|max:5',
                'comentario' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $avaliador = Auth::user(); // Obtém o usuário autenticado

            if ($avaliador->passageiro) {
                $avaliacao = Avaliacao::create([
                    'caravana_id' => $request->caravana_id,
                    'organizador_id' => $request->organizador_id,
                    'passageiro_id' => $avaliador->id,
                    'nota' => $request->nota,
                    'comentario' => $request->comentario,
                ]);
            } else {
                $avaliacao = Avaliacao::create([
                    'caravana_id' => $request->caravana_id,
                    'organizador_id' => $avaliador->id,
                    'passageiro_id' => $request->passageiro_id,
                    'nota' => $request->nota,
                    'comentario' => $request->comentario,
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
     *     path="/listar-avaliacoes/caravana/{id}",
     *     summary="Listar avaliações de uma caravana",
     *     description="Esse endpoint permite listar as avaliações feitas para uma caravana específica.",
     *     operationId="listarAvaliacoesCaravana",
     *     tags={"Avaliação"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da caravana",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Avaliações da caravana obtidas com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="avaliacoes", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="nota", type="integer", example=5),
     *                     @OA\Property(property="comentario", type="string", example="Ótima viagem!"),
     *                     @OA\Property(property="caravana_id", type="integer", example=123),
     *                     @OA\Property(property="organizador_id", type="integer", example=456),
     *                     @OA\Property(property="passageiro_id", type="integer", example=789),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao listar avaliações da caravana",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erro ao obter avaliações da caravana.")
     *         )
     *     )
     * )
     */

    public function listaAvaliacoesCaravana($id)
    {
        try {
            $avaliacoes = Avaliacao::where('caravana_id', $id)->get();
            return response()->json([
                'status' => true,
                'avaliacoes' => $avaliacoes,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erro ao obter avaliações da caravana: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/listar-avaliacoes/organizador/{id}",
     *     summary="Listar avaliações de um organizador",
     *     description="Esse endpoint permite listar as avaliações feitas para um organizador específico.",
     *     operationId="listarAvaliacoesOrganizador",
     *     tags={"Avaliação"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do organizador",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Avaliações do organizador obtidas com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="avaliacoes", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="nota", type="integer", example=4),
     *                     @OA\Property(property="comentario", type="string", example="Muito atencioso!"),
     *                     @OA\Property(property="caravana_id", type="integer", example=123),
     *                     @OA\Property(property="organizador_id", type="integer", example=456),
     *                     @OA\Property(property="passageiro_id", type="integer", example=789),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao listar avaliações do organizador",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erro ao obter avaliações do organizador.")
     *         )
     *     )
     * )
     */
    public function listarAvaliacoesOrganizador($id)
    {
        try {
            $avaliacoes = Avaliacao::where('organizador_id', $id)->get();
            return response()->json([
                'status' => true,
                'avaliacoes' => $avaliacoes,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erro ao obter avaliações do organizador: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/listar-avaliacoes/passageiro/{id}",
     *     summary="Listar avaliações feitas por um organizador a um passageiro",
     *     description="Esse endpoint permite listar as avaliações feitas por um organizador a um passageiro específico.",
     *     operationId="listarAvaliacoesPassageiro",
     *     tags={"Avaliação"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do passageiro",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Avaliações do passageiro obtidas com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="avaliacoes", type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="nota", type="integer", example=3),
     *                     @OA\Property(property="comentario", type="string", example="Passageiro muito educado."),
     *                     @OA\Property(property="caravana_id", type="integer", example=123),
     *                     @OA\Property(property="organizador_id", type="integer", example=456),
     *                     @OA\Property(property="passageiro_id", type="integer", example=789),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao listar avaliações do passageiro",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erro ao obter avaliações do passageiro.")
     *         )
     *     )
     * )
     */

    public function listarAvaliacoesPassageiro($id)
    {
        try {
            $avaliacoes = Avaliacao::where('passageiro_id', $id)->get();
            return response()->json([
                'status' => true,
                'avaliacoes' => $avaliacoes,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erro ao obter avaliações do passageiro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/editar-avaliacao/{id}",
     *     summary="Editar uma avaliação existente",
     *     description="Esse endpoint permite editar uma avaliação já registrada.",
     *     operationId="editarAvaliacao",
     *     tags={"Avaliação"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da avaliação",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="nota", type="integer", description="Nova nota da avaliação", example=5),
     *             @OA\Property(property="comentario", type="string", description="Novo comentário da avaliação", example="Excelente! Melhorou muito."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Avaliação editada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Avaliação editada com sucesso!")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Erro ao editar avaliação",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example=" Vocé não tem permissão para editar esta avaliação.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao editar avaliação",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erro ao editar avaliação.")
     *         )
     *     )
     * )
     */

    public function editarAvaliacao(Request $request, $id)
    {
        try {
            $avaliacao = Avaliacao::findOrFail($id);

            // Armazena o usuário autenticado
            $avaliador = auth()->user();

            // Verifica se o usuário autenticado é proprietário da avaliação
            if ($avaliacao->passageiro_id !== $avaliador->id && $avaliacao->organizador_id !== $avaliador->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Você não tem permissão para excluir esta avaliação.'
                ], 403);
            }

            $avaliacao->nota = $request->nota;
            $avaliacao->comentario = $request->comentario;
            $avaliacao->save();
            return response()->json([
                'status' => true,
                'message' => 'Avaliação editada com sucesso!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erro ao editar avaliação: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/excluir-avaliacao/{id}",
     *     summary="Excluir uma avaliação",
     *     description="Esse endpoint permite excluir uma avaliação existente.",
     *     operationId="excluirAvaliacao",
     *     tags={"Avaliação"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da avaliação",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Avaliação excluída com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Avaliação excluída com sucesso!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Erro ao excluir avaliação",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example=" Vocé não tem permissão para excluir esta avaliação.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao excluir avaliação",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erro ao excluir avaliação.")
     *         )
     *     )
     * )
     */

    public function excluirAvaliacao($id)
    {
        try {
            $avaliacao = Avaliacao::findOrFail($id);

            // Armazena o usuário autenticado
            $avaliador = auth()->user();

            // Verifica se o usuário autenticado é proprietário da avaliação
            if ($avaliacao->passageiro_id !== $avaliador->id && $avaliacao->organizador_id !== $avaliador->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Você não tem permissão para excluir esta avaliação.'
                ], 403);
            }

            $avaliacao->delete();
            return response()->json([
                'status' => true,
                'message' => 'Avaliação excluida com sucesso!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erro ao excluir avaliação: ' . $e->getMessage()
            ], 500);
        }
    }
}
