<?php

namespace App\Http\Controllers;

use App\Models\Caravana;
use App\Models\Favorito;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoritoController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/caravanas/{id}/favoritar",
     *     summary="Favoritar uma caravana",
     *     description="Permite que um usuário autenticado (que não seja o organizador da caravana) favorite uma caravana. Um usuário não pode favoritar a mesma caravana mais de uma vez.",
     *     tags={"Favoritos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da caravana a ser favoritada",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Caravana favoritada com sucesso!",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Caravana favoritada com sucesso!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Caravana já foi favoritada pelo usuário.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Caravana já foi favoritada pelo usuário.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Usuário não autenticado.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Usuário precisa estar logado para favoritar uma caravana.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Organizador não pode favoritar sua própria caravana.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Organizadores não podem favoritar suas caravanas.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Caravana não encontrada.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Caravana] 999")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno ao tentar favoritar a caravana.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erro ao favoritar a caravana."),
     *             @OA\Property(property="error", type="string", example="Mensagem de erro detalhada")
     *         )
     *     )
     * )
     */

    public function favoritarCaravana($id)
    {
        $user = Auth::user();
        $caravana = Caravana::findOrFail($id);

        // Verifica se o usuário está logado
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Usuário precisa estar logado para favoritar uma caravana.',
            ], 401);
        }

        // Verifica se a Caravana já foi favoritada pelo usuário
        $favorito = Favorito::where('caravana_id', $caravana->id)
            ->where('user_id', $user->id)
            ->first();

        if ($favorito) {
            return response()->json([
                'status' => false,
                'message' => 'Caravana já foi favoritada pelo usuário.',
            ], 400);
        }

        try {
            $favorito = new Favorito();
            $favorito->caravana_id = $caravana->id;
            $favorito->user_id = $user->id;

            $favorito->save();

            return response()->json([
                'status' => true,
                'message' => 'Caravana favoritada com sucesso!',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erro ao favoritar a caravana.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/caravanas/{id}/desfavoritar",
     *     summary="Desfavoritar uma caravana",
     *     description="Permite que o usuário autenticado remova uma caravana de seus favoritos.",
     *     tags={"Favoritos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da caravana a ser desfavoritada",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Caravana desfavoritada com sucesso.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Caravana desfavoritada com sucesso!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Usuário não autenticado.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Usuário precisa estar logado para desfavoritar uma caravana.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Caravana não foi favoritada pelo usuário.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Caravana não foi favoritada pelo usuário.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno ao desfavoritar caravana.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erro ao desfavoritar a caravana."),
     *             @OA\Property(property="error", type="string", example="Mensagem de erro")
     *         )
     *     )
     * )
     */

    public function desfavoritarCaravana($id)
    {
        $user = Auth::user();
        $caravana = Caravana::findOrFail($id);

        // Verifica se o usuário está logado
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Usuário precisa estar logado para desfavoritar uma caravana.',
            ], 401);
        }

        $favorito = Favorito::where('caravana_id', $caravana->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$favorito) {
            return response()->json([
                'status' => false,
                'message' => 'Caravana não foi favoritada pelo usuário.',
            ], 400);
        }

        try {
            $favorito->delete();

            return response()->json([
                'status' => true,
                'message' => 'Caravana desfavoritada com sucesso!',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erro ao desfavoritar a caravana.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/users/{user_id}/favoritos",
     *     summary="Listar minhas caravanas favoritadas",
     *     description="Retorna uma lista de todas as caravanas que o usuário autenticado marcou como favoritas.",
     *     tags={"Favoritos"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de caravanas favoritadas pelo usuário retornada com sucesso.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nome", type="string", example="Caravana 1"),
     *                     @OA\Property(property="cidade_origem", type="string", example="Cidade A"),
     *                     @OA\Property(property="estado_origem", type="string", example="SP"),
     *                     @OA\Property(property="cep_origem", type="string", example="12345-000"),
     *                     @OA\Property(property="bairro_origem", type="string", example="Centro"),
     *                     @OA\Property(property="rua_origem", type="string", example="Rua das Flores"),
     *                     @OA\Property(property="numero_origem", type="string", example="100"),
     *                     @OA\Property(property="complemento_origem", type="string", example="Próximo à praça"),
     *                     @OA\Property(property="cidade_destino", type="string", example="Cidade B"),
     *                     @OA\Property(property="estado_destino", type="string", example="RJ"),
     *                     @OA\Property(property="cep_destino", type="string", example="22222-000"),
     *                     @OA\Property(property="bairro_destino", type="string", example="Bairro Azul"),
     *                     @OA\Property(property="rua_destino", type="string", example="Av. Principal"),
     *                     @OA\Property(property="numero_destino", type="string", example="200"),
     *                     @OA\Property(property="complemento_destino", type="string", example="Em frente ao parque"),
     *                     @OA\Property(property="descricao", type="string", example="Viagem para show em Cidade B"),
     *                     @OA\Property(property="capacidade", type="integer", example=45),
     *                     @OA\Property(property="organizador_id", type="integer", example=3),
     *                     @OA\Property(
     *                         property="imagens",
     *                         type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="caravana_id", type="integer", example=1),
     *                             @OA\Property(property="url", type="string", example="https://example.com/imagem.jpg"),
     *                             @OA\Property(property="principal", type="boolean", example=true)
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Usuário não autenticado.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Usuário precisa estar logado para listar seus favoritos.")
     *         )
     *     )
     * )
     */


    public function listarMeusFavoritos()
    {
        $user = Auth::user();

        // Verifica se o usuário está logado
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Usuário precisa estar logado para listar seus favoritos.',
            ], 401);
        }

        // Recupera as caravanas favoritadas pelo usuário, incluindo suas imagens
        $caravanas = Caravana::whereHas('favoritos', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with('imagens')
            ->get();

        // Retorna as caravanas favoritadas
        return response()->json([
            'status' => true,
            'data' => $caravanas
        ], 200);
    }
}
