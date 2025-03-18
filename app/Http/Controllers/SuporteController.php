<?php

namespace App\Http\Controllers;

use App\Models\Suporte;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SuporteController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/registrar-suporte",
     *     summary="Registrar um novo suporte",
     *     description="Esse endpoint permite que um user registre um suporte.",
     *     operationId="registrarSuporte",
     *     tags={"Suporte"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "descricao", "status"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="descricao", type="string", example="Problema ao acessar a caravana"),
     *             @OA\Property(property="status", type="string", enum={"Pendente", "Em andamento", "Concluido"}, example="Pendente")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Suporte registrado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Suporte registrado com sucesso!"),
     *             @OA\Property(property="suporte", type="object")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Erro ao registrar suporte")
     * )
     */
    public function registrarSuporte(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'descricao' => 'required|string',
                'status' => 'required|in:Pendente, Em andamento, Concluido',
            ]);

            DB::beginTransaction();

            $suporte = Suporte::create([
                'user_id' => $request->user_id,
                'descricao' => $request->descricao,
                'status' => $request->status,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Suporte registrado com sucesso!',
                'suporte' => $suporte,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Erro ao registrar suporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/listar-suportes",
     *     summary="Listar todos os suportes",
     *     description="Esse endpoint permite listar todos os suportes.",
     *     operationId="listarSuportes",
     *     tags={"Suporte"},
     *     @OA\Response(response=200, description="Lista de suportes",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="suportes", type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Erro ao obter suportes")
     * )
     */

    public function listarSuporte()
    {
        try {
            $suportes = Suporte::all();
            return response()->json([
                'status' => true,
                'suportes' => $suportes,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erro ao obter suportes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/visualizar-suporte/{id}",
     *     summary="Visualizar um suporte específico",
     *     description="Esse endpoint permite visualizar um suporte especifico.",
     *     operationId="visualizarSuporte",
     *     tags={"Suporte"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID do suporte", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detalhes do suporte",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="suporte", type="object")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Erro ao obter suporte")
     * )
     */

    public function visualizarSuporte($id)
    {
        try {
            $suporte = Suporte::findOrFail($id);
            return response()->json([
                'status' => true,
                'suporte' => $suporte,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erro ao obter suporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/editar-suporte/{id}",
     *     summary="Editar um suporte",
     *     description="Esse endpoint permite editar um suporte.",
     *     operationId="editarSuporte",
     *     tags={"Suporte"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID do suporte", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "descricao", "status"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="descricao", type="string", example="Atualização na descrição do suporte"),
     *             @OA\Property(property="status", type="string", enum={"Pendente", "Em andamento", "Concluido"}, example="Concluido")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Suporte atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Suporte atualizado com sucesso!"),
     *             @OA\Property(property="suporte", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Permissão negada"),
     *     @OA\Response(response=500, description="Erro ao atualizar suporte")
     * )
     */

    public function editarSuporte(Request $request, $id)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'descricao' => 'required|string',
                'status' => 'required|in:Pendente, Em andamento, Concluido',
            ]);

            // Armazena o id do suporte
            $suporte = Suporte::findOrFail($id);

            // Armazena o usuário autenticado
            $solicitante = auth()->user();

            //Verifica se o usuário autenticado é proprietário do suporte
            if ($suporte->user_id != $solicitante->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Você não tem permissão para editar este suporte.'
                ], 403);
            }

            $suporte->update($request->all());
            return response()->json([
                'status' => true,
                'message' => 'Suporte atualizado com sucesso!',
                'suporte' => $suporte,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erro ao atualizar suporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/excluir-suporte/{id}",
     *     summary="Excluir um suporte",
     *     description="Esse endpoint permite excluir um suporte.",
     *     operationId="excluirSuporte",
     *     tags={"Suporte"},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID do suporte", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Suporte excluído com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Suporte excluído com sucesso!")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Permissão negada"),
     *     @OA\Response(response=500, description="Erro ao excluir suporte")
     * )
     */

    public function excluirSuporte($id)
    {
        try {

            // Armazena o id do suporte
            $suporte = Suporte::findOrFail($id);

            // Armazena o usuário autenticado
            $solicitante = auth()->user();

            //Verifica se o usuário autenticado é proprietário do suporte
            if ($suporte->user_id != $solicitante->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Você não tem permissão para excluir este suporte.'
                ], 403);
            }

            $suporte->delete();
            return response()->json([
                'status' => true,
                'message' => 'Suporte excluido com sucesso!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erro ao excluir suporte: ' . $e->getMessage()
            ], 500);
        }
    }
}
