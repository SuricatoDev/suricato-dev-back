<?php

namespace App\Http\Controllers;

use App\Models\Caravana;
use App\Models\Denuncia;
use App\Models\Organizador;
use App\Models\Passageiro;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DenunciaController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/denuncias",
     *     summary="Registrar uma nova denúncia",
     *     description="Registra uma denúncia com base no tipo de usuário (passageiro ou organizador).",
     *     tags={"Denúncias"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"denunciante_id", "topico", "descricao"},
     *             @OA\Property(property="denunciante_id", type="integer", example=1),
     *             @OA\Property(property="topico", type="string", enum={"Pagamento", "Cancelamento", "Inconsistência", "Segurança", "Outro"}, example="Pagamento"),
     *             @OA\Property(property="descricao", type="string", example="O pagamento não foi processado corretamente."),
     *             @OA\Property(property="caravana_id", type="integer", example=2, nullable=true),
     *             @OA\Property(property="organizador_id", type="integer", example=3, nullable=true),
     *             @OA\Property(property="passageiro_id", type="integer", example=4, nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Denúncia registrada com sucesso.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Denúncia registrada com sucesso!"),
     *             @OA\Property(property="denuncia", type="object", example={"id": 10, "denunciante_id": 1, "topico": "Pagamento", "descricao": "O pagamento não foi processado corretamente.", "status": "Pendente"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro de validação."),
     *             @OA\Property(property="errors", type="object", example={"topico": {"O campo tópico é obrigatório."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao registrar denúncia.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao registrar denúncia."),
     *             @OA\Property(property="error", type="string", example="Detalhes do erro.")
     *         )
     *     )
     * )
     */
    public function registrarDenuncia(Request $request)
    {
        try {
            $request->validate([
                'denunciante_id' => 'required|exists:users,id',
                'topico' => 'required|string|in:Pagamento,Cancelamento,Inconsistência,Segurança,Outro',
                'descricao' => 'required|string',
            ]);

            DB::beginTransaction();

            // Obtém o usuário denunciante
            $denunciante = User::findOrFail($request->denunciante_id);

            // Inicializa os campos como nulos
            $denunciaData = [
                'denunciante_id' => $denunciante->id,
                'topico' => $request->topico,
                'descricao' => $request->descricao,
                'status' => 'Pendente',
                'caravana_id' => null,
                'organizador_id' => null,
                'passageiro_id' => null,
            ];

            // Define o campo correto com base no tipo do usuário denunciante
            if ($denunciante->passageiro) {
                if ($request->has('caravana_id')) {
                    $caravana = Caravana::findOrFail($request->caravana_id);
                    $denunciaData['caravana_id'] = $caravana->id;
                } elseif ($request->has('organizador_id')) {
                    $organizador = Organizador::findOrFail($request->organizador_id);
                    $denunciaData['organizador_id'] = $organizador->id;
                } else {
                    throw new \Exception("Passageiros só podem denunciar caravanas ou organizadores.");
                }
            } elseif ($denunciante->organizador) {
                // Organizador pode denunciar um passageiro
                if ($request->has('passageiro_id')) {
                    $passageiro = Passageiro::findOrFail($request->passageiro_id);
                    $denunciaData['passageiro_id'] = $passageiro->id;
                } else {
                    throw new \Exception("Organizadores só podem denunciar passageiros.");
                }
            } else {
                throw new \Exception("Tipo de usuário inválido para registrar denúncia.");
            }

            // Cria a denúncia com os dados definidos
            $denuncia = Denuncia::create($denunciaData);

            DB::commit();

            return response()->json([
                'message' => 'Denúncia registrada com sucesso!',
                'denuncia' => $denuncia
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao registrar denúncia.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/denuncias/{id}",
     *     summary="Editar uma denúncia",
     *     description="Permite ao usuário editar sua própria denúncia.",
     *     tags={"Denúncias"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da denúncia a ser editada",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="topico", type="string", maxLength=255, example="Inconsistência"),
     *             @OA\Property(property="descricao", type="string", example="A caravana foi alterada sem aviso prévio."),
     *             @OA\Property(property="status", type="string", enum={"Pendente", "Em andamento", "Concluído"}, example="Em andamento")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Denúncia atualizada com sucesso.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Denúncia atualizada com sucesso!"),
     *             @OA\Property(property="denuncia", type="object", example={"id": 10, "denunciante_id": 1, "topico": "Inconsistência", "descricao": "A caravana foi alterada sem aviso prévio.", "status": "Em andamento"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Usuário não tem permissão para editar.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Você não tem permissão para editar esta denúncia.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao atualizar denúncia.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erro ao atualizar denúncia."),
     *             @OA\Property(property="error", type="string", example="Detalhes do erro.")
     *         )
     *     )
     * )
     */

    public function editarDenuncia(Request $request, $id)
    {
        try {
            $user = Auth::user(); // Obtém o usuário autenticado
            $denuncia = Denuncia::findOrFail($id);

            // Verifica se o usuário logado é o autor da denúncia
            if ($denuncia->denunciante_id !== $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Você não tem permissão para editar esta denúncia.'
                ], 403);
            }

            // Validação dos campos que podem ser atualizados
            $validated = $request->validate([
                'topico' => 'sometimes|string|max:255',
                'descricao' => 'sometimes|string',
                'status' => 'sometimes|in:Pendente, Em andamento, Concluído', // Evita status inválidos
            ]);

            // Atualiza apenas os campos válidos
            $denuncia->fill($validated);
            $denuncia->save();

            return response()->json([
                'status' => true,
                'message' => 'Denúncia atualizada com sucesso!',
                'denuncia' => $denuncia
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erro ao atualizar denúncia.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/denuncias/{id}",
     *     summary="Excluir uma denúncia",
     *     description="Permite ao usuário excluir sua própria denúncia.",
     *     tags={"Denúncias"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da denúncia a ser excluída",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Denúncia excluída com sucesso.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Denúncia excluída com sucesso!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Usuário não tem permissão para excluir.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Você não tem permissão para excluir esta denúncia.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao excluir denúncia.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erro ao excluir denúncia."),
     *             @OA\Property(property="error", type="string", example="Detalhes do erro.")
     *         )
     *     )
     * )
     */

    public function excluirDenuncia($id)
    {
        try {
            $user = Auth::user(); // Obtém o usuário autenticado
            $denuncia = Denuncia::findOrFail($id);

            // Verifica se o usuário logado é o autor da denúncia
            if ($denuncia->denunciante_id !== $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Você não tem permissão para excluir esta denúncia.'
                ], 403);
            }

            $denuncia->delete();

            return response()->json([
                'status' => true,
                'message' => 'Denúncia excluída com sucesso!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erro ao excluir denúncia.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
