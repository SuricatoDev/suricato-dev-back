<?php

namespace App\Http\Controllers;

use App\Models\Caravana;
use App\Models\CaravanaImagem;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
     *         response=404,
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

        if ($caravanas->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Nenhuma caravana encontrada!'
            ], 404);
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
     *             required={"titulo", "descricao", "categoria", "data_partida", "data_retorno", "endereco_origem", "numero_origem", "bairro_origem", "cep_origem", "cidade_origem", "estado_origem", "endereco_destino", "numero_destino", "bairro_destino", "cep_destino", "cidade_destino", "estado_destino", "numero_vagas", "valor", "imagens"},
     *             @OA\Property(property="titulo", type="string", example="Caravana para Show"),
     *             @OA\Property(property="descricao", type="string", example="Viagem para o show de uma banda famosa"),
     *             @OA\Property(property="categoria", type="string", example="Shows"),
     *             @OA\Property(property="data_partida", type="string", format="date", example="2025-06-15"),
     *             @OA\Property(property="data_retorno", type="string", format="date", example="2025-06-16"),
     *             @OA\Property(property="endereco_origem", type="string", example="Avenida Paulista"),
     *             @OA\Property(property="numero_origem", type="string", example="1000"),
     *             @OA\Property(property="bairro_origem", type="string", example="Bela Vista"),
     *             @OA\Property(property="cep_origem", type="string", example="01310-100"),
     *             @OA\Property(property="cidade_origem", type="string", example="São Paulo"),
     *             @OA\Property(property="estado_origem", type="string", example="SP"),
     *             @OA\Property(property="endereco_destino", type="string", example="Praia de Copacabana"),
     *             @OA\Property(property="numero_destino", type="string", example="200"),
     *             @OA\Property(property="bairro_destino", type="string", example="Copacabana"),
     *             @OA\Property(property="cep_destino", type="string", example="22060-001"),
     *             @OA\Property(property="cidade_destino", type="string", example="Rio de Janeiro"),
     *             @OA\Property(property="estado_destino", type="string", example="RJ"),
     *             @OA\Property(property="numero_vagas", type="integer", example=50),
     *             @OA\Property(property="valor", type="number", format="float", example=250.00),
     *             @OA\Property(
     *                 property="imagens",
     *                 type="array",
     *                 @OA\Items(
     *                     type="string",
     *                     format="binary"
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
     *                 @OA\Property(property="categoria", type="string", example="Shows"),
     *                 @OA\Property(property="data_partida", type="string", format="date", example="2025-06-15"),
     *                 @OA\Property(property="data_retorno", type="string", format="date", example="2025-06-16"),
     *                 @OA\Property(property="numero_vagas", type="integer", example=50),
     *                 @OA\Property(property="valor", type="number", format="float", example=250.00)
     *             ),
     *             @OA\Property(property="imagens", type="array", @OA\Items(
     *                 @OA\Property(property="url", type="string", format="url", example="https://s3.amazonaws.com/suricatodev/caravanas/1/imagem.jpg")
     *             ))
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
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erro de validação."),
     *             @OA\Property(property="errors", type="object")
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
        // Verifica se o usuário logado é um organizador
        $user = Auth::user();
        if (!$user->organizador) {
            return response()->json([
                'status' => false,
                'message' => 'Apenas organizadores podem criar caravanas.',
            ], 403);
        }

        // Verifica se existe o campo 'dados' e faz a validação
        $dados = $request->input('dados');
        if ($dados) {
            $dados = json_decode($dados, true); // Decodifica os dados JSON
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Dados faltando.',
            ], 400);
        }

        // Validação dos dados
        $validated = Validator::make($dados, [
            'titulo' => 'required|string',
            'descricao' => 'required|string',
            'categoria' => 'required|string',
            'data_partida' => 'required|date',
            'data_retorno' => 'required|date',
            'endereco_origem' => 'required|string',
            'numero_origem' => 'sometimes|string',
            'bairro_origem' => 'required|string',
            'cep_origem' => 'required|string',
            'cidade_origem' => 'required|string',
            'estado_origem' => 'required|string',
            'endereco_destino' => 'required|string',
            'numero_destino' => 'sometimes|string',
            'bairro_destino' => 'required|string',
            'cep_destino' => 'required|string',
            'cidade_destino' => 'required|string',
            'estado_destino' => 'required|string',
            'numero_vagas' => 'required|integer',
            'valor' => 'required|numeric',
            'organizador_id' => 'required|integer',
            'ordem' => 'required|integer',
        ])->validate();

        try {
            // Criação da caravana com dados validados
            $caravana = Caravana::create($validated);

            // Diretório base para as imagens
            $caravanaId = $caravana->id;
            $folderPath = "caravanas/{$caravanaId}/";

            // Para armazenar URLs das imagens
            $imageUrls = [];

            // Verifica se as imagens foram enviadas
            if ($request->hasFile('imagens')) {
                foreach ($request->file('imagens') as $imagem) {
                    // Nome original da imagem
                    $fileName = $imagem->getClientOriginalName();

                    // Upload da imagem para o S3
                    $path = $imagem->storeAs($folderPath, $fileName, 's3');

                    // URL pública da imagem
                    $url = Storage::disk('s3')->url($path);

                    // Registra a imagem no banco
                    CaravanaImagem::create([
                        'ordem' => $validated['ordem'],
                        'path' => $url,
                        'caravana_id' => $caravana->id,
                    ]);

                    // Adiciona a URL à lista
                    $imageUrls[] = $url;
                }
            }

            // Retorno da resposta
            return response()->json([
                'status' => true,
                'message' => 'Caravana criada com sucesso!',
                'data' => $caravana,
                'imagens' => $imageUrls,
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
     *             @OA\Property(property="endereco_origem", type="string", example="Rua A"),
     *             @OA\Property(property="numero_origem", type="string", example="123"),
     *             @OA\Property(property="bairro_origem", type="string", example="Centro"),
     *             @OA\Property(property="cep_origem", type="string", example="30123-456"),
     *             @OA\Property(property="cidade_origem", type="string", example="Belo Horizonte"),
     *             @OA\Property(property="estado_origem", type="string", example="MG"),
     *             @OA\Property(property="endereco_destino", type="string", example="Av. Paulista"),
     *             @OA\Property(property="numero_destino", type="string", example="1000"),
     *             @OA\Property(property="bairro_destino", type="string", example="Bela Vista"),
     *             @OA\Property(property="cep_destino", type="string", example="01311-000"),
     *             @OA\Property(property="cidade_destino", type="string", example="São Paulo"),
     *             @OA\Property(property="estado_destino", type="string", example="SP"),
     *             @OA\Property(property="numero_vagas", type="integer", example=40),
     *             @OA\Property(property="valor", type="number", format="float", example=300.00),
     *             @OA\Property(property="evento_id", type="integer", example=15),
     *             @OA\Property(
     *                 property="imagens",
     *                 type="array",
     *                 @OA\Items(type="string", format="url", example="https://s3.amazonaws.com/suricatodev/caravanas/1/imagem.jpg")
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
     *                 @OA\Property(property="numero_vagas", type="integer", example=40),
     *                 @OA\Property(property="valor", type="number", format="float", example=300.00),
     *                 @OA\Property(property="evento_id", type="integer", example=15)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Usuário sem permissão para editar a caravana",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Apenas organizadores podem editar caravanas.")
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
     *         response=422,
     *         description="Erro de validação nos dados enviados",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erro de validação."),
     *             @OA\Property(property="errors", type="object", example={"titulo": {"O campo título é obrigatório."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno ao atualizar a caravana",
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
            ], 403);
        }

        // Decodifica os dados JSON enviados no campo 'dados'
        $dados = json_decode($request->input('dados'), true);

        if (!$dados) {
            return response()->json([
                'status' => false,
                'message' => 'Os dados da caravana são inválidos ou não foram enviados corretamente.',
            ], 400);
        }

        // Validação dos dados de entrada
        $validated = Validator::make($dados, [
            'titulo' => 'sometimes|required|string',
            'descricao' => 'sometimes|required|string',
            'data_partida' => 'sometimes|required|date',
            'data_retorno' => 'sometimes|required|date',
            'endereco_origem' => 'sometimes|required|string',
            'numero_origem' => 'sometimes|required|string',
            'bairro_origem' => 'sometimes|required|string',
            'cep_origem' => 'sometimes|required|string',
            'cidade_origem' => 'sometimes|required|string',
            'estado_origem' => 'sometimes|required|string',
            'endereco_destino' => 'sometimes|required|string',
            'numero_destino' => 'sometimes|required|string',
            'bairro_destino' => 'sometimes|required|string',
            'cep_destino' => 'sometimes|required|string',
            'cidade_destino' => 'sometimes|required|string',
            'estado_destino' => 'sometimes|required|string',
            'numero_vagas' => 'sometimes|required|integer',
            'valor' => 'sometimes|required|numeric',
            'evento_id' => 'sometimes|required|integer',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Erro na validação dos dados.',
                'errors' => $validated->errors(),
            ], 422);
        }

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
            $caravana->update($validated->validated());

            // Atualizar imagens se houver novas enviadas
            $imageUrls = [];
            if ($request->hasFile('imagens')) {
                // Apagar imagens antigas do S3
                $imagensAntigas = CaravanaImagem::where('caravana_id', $caravana->id)->get();
                foreach ($imagensAntigas as $imagem) {
                    Storage::disk('s3')->delete(str_replace(Storage::disk('s3')->url(''), '', $imagem->path));
                    $imagem->delete();
                }

                // Diretório base para o upload das novas imagens
                $folderPath = "caravanas/{$caravana->id}/";

                foreach ($request->file('imagens') as $imagem) {
                    $fileName = $imagem->getClientOriginalName();
                    $path = $imagem->storeAs($folderPath, $fileName, 's3');
                    $url = Storage::disk('s3')->url($path);

                    // Criar nova entrada na tabela 'caravana_imagens'
                    CaravanaImagem::create([
                        'path' => $url,
                        'caravana_id' => $caravana->id,
                    ]);

                    $imageUrls[] = $url;
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Caravana atualizada com sucesso!',
                'data' => $caravana,
                'imagens' => $imageUrls,
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
            ], 403);
        }

        try {
            DB::beginTransaction(); // Inicia a transação para garantir atomicidade no banco

            $caravana = Caravana::findOrFail($id);

            // Verifica se o organizador da caravana é o mesmo que está tentando excluir
            if ($caravana->organizador_id !== $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Você não tem permissão para excluir esta caravana.',
                ], 403);
            }

            // Verifica se a caravana tem passageiros confirmados
            $passageirosConfirmados = DB::table('caravana_passageiros')
                ->where('caravana_id', $caravana->id)
                ->where('status', 'confirmado')
                ->whereNotNull('id')
                ->count();

            if ($passageirosConfirmados > 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'A caravana não pode ser excluída pois já tem passageiros confirmados.',
                ], 400);
            }

            // Buscar todas as imagens associadas à caravana
            $imagens = CaravanaImagem::where('caravana_id', $caravana->id)->get();

            // Excluir as imagens do S3
            foreach ($imagens as $imagem) {
                $path = str_replace(Storage::disk('s3')->url(''), '', $imagem->path);
                Storage::disk('s3')->delete($path);
                $imagem->delete();
            }

            // Excluir a caravana
            $caravana->delete();

            DB::commit(); // Confirma a transação, apenas se tudo der certo

            return response()->json([
                'status' => true,
                'message' => 'Caravana excluída com sucesso!',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Desfaz as alterações em caso de erro, garantindo a integridade do banco

            return response()->json([
                'status' => false,
                'message' => 'Erro ao excluir a caravana.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * @OA\Get(
     *     path="/api/filtrar-caravanas",
     *     summary="Filtrar caravanas por cidade de origem, destino, evento ou categoria",
     *     description="Permite buscar caravanas com base nos filtros fornecidos. As pesquisas podem ser independentes ou conjuntas.",
     *     tags={"Caravanas"},
     *     @OA\Parameter(
     *         name="origem",
     *         in="query",
     *         description="Cidade de origem da caravana",
     *         required=false,
     *         @OA\Schema(type="string", example="Curitiba")
     *     ),
     *     @OA\Parameter(
     *         name="destino",
     *         in="query",
     *         description="Cidade de destino da caravana",
     *         required=false,
     *         @OA\Schema(type="string", example="São Paulo")
     *     ),
     *     @OA\Parameter(
     *         name="titulo",
     *         in="query",
     *         description="Nome do evento",
     *         required=false,
     *         @OA\Schema(type="string", example="Rock in Rio")
     *     ),
     *     @OA\Parameter(
     *         name="categoria",
     *         in="query",
     *         description="Categoria do evento",
     *         required=false,
     *         @OA\Schema(type="string", example="Shows")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de caravanas filtradas",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="caravanas",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="cidade_origem", type="string", example="Curitiba"),
     *                     @OA\Property(property="cidade_destino", type="string", example="São Paulo"),
     *                     @OA\Property(property="titulo", type="string", example="Rock in Rio"),
     *                     @OA\Property(property="categoria", type="string", example="Shows"),
     *                     @OA\Property(property="data_saida", type="string", format="date", example="2025-05-15"),
     *                     @OA\Property(property="data_retorno", type="string", format="date", example="2025-05-20"),
     *                     @OA\Property(property="vagas_disponiveis", type="integer", example=10)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno ao processar a solicitação",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erro ao buscar caravanas."),
     *             @OA\Property(property="error", type="string", example="Detalhes do erro.")
     *         )
     *     )
     * )
     */

    public function filtrarCaravanas(Request $request)
    {
        $query = Caravana::query();

        if ($request->has('origem')) {
            $query->where('cidade_origem', 'like', '%' . $request->origem . '%');
        }

        if ($request->has('destino')) {
            $query->where('cidade_destino', 'like', '%' . $request->destino . '%');
        }

        if ($request->has('titulo')) {
            $query->where('titulo', 'like', '%' . $request->titulo . '%');
        }

        if ($request->has('categoria')) {
            $query->where('categoria', 'like', '%' . $request->categoria . '%');
        }

        $caravanas = $query->get();

        return response()->json([
            'status' => true,
            'caravanas' => $caravanas
        ]);
    }

    public function testeUploadS3()
    {
        // Caminho do arquivo local na pasta public
        $localPath = storage_path('app/public/porta.jpeg');

        // Verifica se o arquivo existe antes de tentar o upload
        if (!file_exists($localPath)) {
            return response()->json([
                'status' => false,
                'message' => 'Arquivo não encontrado na pasta public!',
            ], 404);
        }
        $caravanaId = 10; // ID da caravana
        $folderPath = "caravanas/{$caravanaId}/";
        $fileName = '_porta_jpeg';
        $filePath = $folderPath . $fileName;

        Storage::disk('s3')->put($filePath, file_get_contents($localPath));

        /** @var \Illuminate\Filesystem\FilesystemAdapter $s3 */
        $s3 = Storage::disk('s3');

        // Obtém a URL pública do arquivo no S3
        $url = $s3->url('porta_jpeg');

        return response()->json([
            'status' => true,
            'message' => 'Upload realizado com sucesso!',
            'url' => $url,
        ]);
    }
}
