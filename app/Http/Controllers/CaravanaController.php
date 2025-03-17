<?php

namespace App\Http\Controllers;

use App\Models\Caravana;
use App\Models\CaravanaImagem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 *
 * @OA\Tag(
 *     name="Caravanas",
 *     description="Rotas relacionadas as Caravanas"
 * )
 */

class CaravanaController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/caravanas",
     *     summary="Listar todas as caravanas",
     *     description="Retorna uma lista de todas as caravanas cadastradas, incluindo suas imagens.",
     *     tags={"Caravanas"},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de caravanas retornada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nome", type="string", example="Caravana para Rock in Rio"),
     *                     @OA\Property(property="descricao", type="string", example="Viagem organizada para o festival"),
     *                     @OA\Property(property="data_saida", type="string", format="date", example="2025-08-30"),
     *                     @OA\Property(property="imagens", type="array", @OA\Items(type="string", example="url_da_imagem.jpg"))
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Nenhuma caravana encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Nenhuma caravana encontrada!")
     *         )
     *     )
     * )
     */

    public function listarCaravanas()
    {
        $caravanas = Caravana::with('imagens')->get();

        if (!$caravanas) {
            return response()->json([
                'status' => false,
                'message' => 'Nenhuma caravana encontrada!'
            ], 400);
        }

        return response()->json([
            'status' => true,
            'data' => $caravanas
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/caravanas",
     *     summary="Criar uma nova caravana",
     *     description="Permite que um organizador crie uma nova caravana.",
     *     tags={"Caravanas"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"titulo", "descricao", "data_partida", "data_retorno", "origem", "destino", "numero_vagas", "valor", "organizador_id", "evento_id", "imagens"},
     *             @OA\Property(property="titulo", type="string", example="Caravana para Show"),
     *             @OA\Property(property="descricao", type="string", example="Viagem para o show de uma banda famosa"),
     *             @OA\Property(property="data_partida", type="string", format="date", example="2025-06-15"),
     *             @OA\Property(property="data_retorno", type="string", format="date", example="2025-06-16"),
     *             @OA\Property(property="origem", type="string", example="São Paulo"),
     *             @OA\Property(property="destino", type="string", example="Rio de Janeiro"),
     *             @OA\Property(property="numero_vagas", type="integer", example=50),
     *             @OA\Property(property="valor", type="number", format="float", example=250.00),
     *             @OA\Property(property="organizador_id", type="integer", example=1),
     *             @OA\Property(property="evento_id", type="integer", example=10),
     *             @OA\Property(
     *                 property="imagens",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="path", type="string", format="url", example="https://meusite.com/imagem.jpg")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Caravana criada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Caravana criada com sucesso!"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="titulo", type="string", example="Caravana para Show"),
     *                 @OA\Property(property="descricao", type="string", example="Viagem para o show de uma banda famosa"),
     *                 @OA\Property(property="data_partida", type="string", format="date", example="2025-06-15"),
     *                 @OA\Property(property="data_retorno", type="string", format="date", example="2025-06-16"),
     *                 @OA\Property(property="origem", type="string", example="São Paulo"),
     *                 @OA\Property(property="destino", type="string", example="Rio de Janeiro"),
     *                 @OA\Property(property="numero_vagas", type="integer", example=50),
     *                 @OA\Property(property="valor", type="number", format="float", example=250.00),
     *                 @OA\Property(property="organizador_id", type="integer", example=1),
     *                 @OA\Property(property="evento_id", type="integer", example=10)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Apenas organizadores podem criar caravanas",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Apenas usuários do tipo organizador podem criar caravanas.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao criar a caravana",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erro ao criar a caravana."),
     *             @OA\Property(property="error", type="string", example="Mensagem de erro detalhada.")
     *         )
     *     )
     * )
     */


    public function cadastrarCaravana(Request $request)
    {
        // Verifica se o usuário logado é do tipo 'organizador'
        $user = Auth::user(); // Obtém o usuário autenticado
        if (!$user->organizador) {
            return response()->json([
                'status' => false,
                'message' => 'Apenas usuários do tipo organizador podem criar caravanas.',
            ], 403); // Status 403 - Forbidden
        }

        // Validação dos dados de entrada
        $validated = $request->validate([
            'titulo' => 'required|string',
            'descricao' => 'required|string',
            'categoria' => 'required|string',
            'data_partida' => 'required|date',
            'data_retorno' => 'required|date',
            'origem' => 'required|string',
            'destino' => 'required|string',
            'numero_vagas' => 'required|integer',
            'valor' => 'required|numeric',
            'organizador_id' => 'required|integer',
            'imagens' => 'required|array',
            'imagens.*.path' => 'required|string|url',
        ]);

        try {
            // Criação da caravana
            $caravana = Caravana::create([
                'titulo' => $validated['titulo'],
                'descricao' => $validated['descricao'],
                'categoria' => $validated['categoria'],
                'data_partida' => $validated['data_partida'],
                'data_retorno' => $validated['data_retorno'],
                'origem' => $validated['origem'],
                'destino' => $validated['destino'],
                'numero_vagas' => $validated['numero_vagas'],
                'valor' => $validated['valor'],
                'organizador_id' => $validated['organizador_id'],
            ]);

            // Criação das imagens associadas à caravana
            foreach ($validated['imagens'] as $imagem) {
                CaravanaImagem::create([
                    'path' => $imagem['path'],
                    'caravana_id' => $caravana->id,
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Caravana criada com sucesso!',
                'data' => $caravana,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erro ao criar a caravana.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/caravanas/{id}",
     *     summary="Atualizar uma caravana existente",
     *     description="Permite que um organizador edite uma caravana que ele criou.",
     *     tags={"Caravanas"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da caravana a ser atualizada",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="titulo", type="string", example="Caravana para Festival"),
     *             @OA\Property(property="descricao", type="string", example="Viagem para um grande festival de música"),
     *             @OA\Property(property="data_partida", type="string", format="date", example="2025-07-20"),
     *             @OA\Property(property="data_retorno", type="string", format="date", example="2025-07-21"),
     *             @OA\Property(property="origem", type="string", example="Belo Horizonte"),
     *             @OA\Property(property="destino", type="string", example="São Paulo"),
     *             @OA\Property(property="numero_vagas", type="integer", example=40),
     *             @OA\Property(property="valor", type="number", format="float", example=300.00),
     *             @OA\Property(property="evento_id", type="integer", example=15),
     *             @OA\Property(
     *                 property="imagens",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="path", type="string", format="url", example="https://meusite.com/nova-imagem.jpg")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Caravana atualizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Caravana atualizada com sucesso!"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="titulo", type="string", example="Caravana para Festival"),
     *                 @OA\Property(property="descricao", type="string", example="Viagem para um grande festival de música"),
     *                 @OA\Property(property="data_partida", type="string", format="date", example="2025-07-20"),
     *                 @OA\Property(property="data_retorno", type="string", format="date", example="2025-07-21"),
     *                 @OA\Property(property="origem", type="string", example="Belo Horizonte"),
     *                 @OA\Property(property="destino", type="string", example="São Paulo"),
     *                 @OA\Property(property="numero_vagas", type="integer", example=40),
     *                 @OA\Property(property="valor", type="number", format="float", example=300.00),
     *                 @OA\Property(property="evento_id", type="integer", example=15)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Apenas organizadores podem editar caravanas",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Apenas usuários do tipo organizador podem editar caravanas.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Caravana não encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Caravana não encontrada.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao atualizar a caravana",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erro ao atualizar a caravana."),
     *             @OA\Property(property="error", type="string", example="Mensagem de erro detalhada.")
     *         )
     *     )
     * )
     */


    public function editarCaravana(Request $request, $id)
    {
        // Verifica se o usuário logado é do tipo 'organizador'
        $user = Auth::user();
        if (!$user->organizador) {
            return response()->json([
                'status' => false,
                'message' => 'Apenas usuários do tipo organizador podem editar caravanas.',
            ], 403); // Status 403 - Forbidden
        }

        // Validação dos dados de entrada
        $validated = $request->validate([
            'titulo' => 'sometimes|required|string',
            'descricao' => 'sometimes|required|string',
            'data_partida' => 'sometimes|required|date',
            'data_retorno' => 'sometimes|required|date',
            'origem' => 'sometimes|required|string',
            'destino' => 'sometimes|required|string',
            'numero_vagas' => 'sometimes|required|integer',
            'valor' => 'sometimes|required|numeric',
            'evento_id' => 'sometimes|required|integer',
            'imagens' => 'sometimes|required|array',
            'imagens.*.path' => 'sometimes|required|string|url',
        ]);

        try {
            // Buscar a caravana para editar
            $caravana = Caravana::findOrFail($id);

            // Verifica se o organizador da caravana é o mesmo que está tentando editar
            if ($caravana->organizador_id !== $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Você não tem permissão para editar esta caravana.',
                ], 403);
            }

            // Atualizar os dados da caravana
            $caravana->update([
                'titulo' => $validated['titulo'] ?? $caravana->titulo,
                'descricao' => $validated['descricao'] ?? $caravana->descricao,
                'data_partida' => $validated['data_partida'] ?? $caravana->data_partida,
                'data_retorno' => $validated['data_retorno'] ?? $caravana->data_retorno,
                'origem' => $validated['origem'] ?? $caravana->origem,
                'destino' => $validated['destino'] ?? $caravana->destino,
                'numero_vagas' => $validated['numero_vagas'] ?? $caravana->numero_vagas,
                'valor' => $validated['valor'] ?? $caravana->valor,
                'evento_id' => $validated['evento_id'] ?? $caravana->evento_id,
            ]);

            // Atualizar as imagens associadas à caravana (se houver)
            if (isset($validated['imagens'])) {
                // Apaga as imagens antigas e cria novas
                CaravanaImagem::where('caravana_id', $caravana->id)->delete();
                foreach ($validated['imagens'] as $imagem) {
                    CaravanaImagem::create([
                        'path' => $imagem['path'],
                        'caravana_id' => $caravana->id,
                    ]);
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Caravana atualizada com sucesso!',
                'data' => $caravana,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erro ao atualizar a caravana.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/caravanas/{id}",
     *     summary="Excluir uma caravana",
     *     description="Permite que um organizador exclua uma caravana que ele criou, desde que não tenha passageiros confirmados.",
     *     tags={"Caravanas"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da caravana a ser excluída",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Caravana excluída com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Caravana excluída com sucesso!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Usuário sem permissão para excluir a caravana",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Você não tem permissão para excluir esta caravana.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="A caravana não pode ser excluída pois já tem passageiros confirmados",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="A caravana não pode ser excluída pois já tem passageiros confirmados.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Caravana não encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Caravana não encontrada.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao excluir a caravana",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erro ao excluir a caravana."),
     *             @OA\Property(property="error", type="string", example="Mensagem de erro detalhada.")
     *         )
     *     )
     * )
     */


    public function excluirCaravana($id)
    {
        // Verifica se o usuário logado é do tipo 'organizador'
        $user = Auth::user();
        if (!$user->organizador) {
            return response()->json([
                'status' => false,
                'message' => 'Apenas usuários do tipo organizador podem excluir caravanas.',
            ], 403); // Status 403 - Forbidden
        }

        try {
            // Buscar a caravana
            $caravana = Caravana::findOrFail($id);

            // Verifica se o organizador da caravana é o mesmo que está tentando excluir
            if ($caravana->organizador_id !== $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Você não tem permissão para excluir esta caravana.',
                ], 403); // Status 403 - Forbidden
            }

            // Verifica se a caravana tem passageiros confirmados na tabela associativa 'caravana_passageiros'
            $passageirosConfirmados = DB::table('caravana_passageiros')
                ->where('caravana_id', $caravana->id)
                ->where('status', 'confirmado')
                ->count();

            if ($passageirosConfirmados > 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'A caravana não pode ser excluída pois já tem passageiros confirmados.',
                ], 400); // Status 400 - Bad Request
            }

            // Excluir as imagens associadas à caravana
            CaravanaImagem::where('caravana_id', $caravana->id)->delete();

            // Excluir a caravana
            $caravana->delete();

            return response()->json([
                'status' => true,
                'message' => 'Caravana excluída com sucesso!',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Erro ao excluir a caravana.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
