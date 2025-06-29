<?php

namespace App\Http\Controllers;

use App\Models\Caravana;
use App\Models\CaravanaImagem;
use App\Models\CaravanaPassageiro;
use App\Models\Favorito;
use App\Models\Organizador;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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
     *     path="api/caravanas/{id}",
     *     summary="Detalha informações sobre uma caravana específica, incluindo o organizador e a média das avaliações do organizador.",
     *     description="Recupera informações detalhadas sobre uma caravana, incluindo as imagens, organizador e a média das avaliações que o organizador recebeu.",
     *     operationId="detalharCaravana",
     *     tags={"Caravanas"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da caravana",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Caravana detalhada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nome", type="string", example="Caravana de Verão"),
     *                 @OA\Property(property="data_inicio", type="string", format="date", example="2025-06-01"),
     *                 @OA\Property(property="data_fim", type="string", format="date", example="2025-06-15"),
     *                 @OA\Property(
     *                     property="organizador",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=2),
     *                     @OA\Property(property="razao_social", type="string", example="Empresa XYZ"),
     *                     @OA\Property(property="nome_fantasia", type="string", example="XYZ Turismo"),
     *                     @OA\Property(property="bairro", type="string", example="Centro"),
     *                     @OA\Property(property="cidade", type="string", example="São Paulo"),
     *                     @OA\Property(property="estado", type="string", example="SP"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-01T10:00:00Z")
     *                 ),
     *                 @OA\Property(
     *                     property="imagens",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="url", type="string", example="https://exemplo.com/imagem.jpg")
     *                     )
     *                 ),
     *             ),
     *             @OA\Property(
     *                 property="mediaAvaliacao",
     *                 type="number",
     *                 format="float",
     *                 example=4.5
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Caravana não encontrada",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Caravana nao encontrada!")
     *         )
     *     )
     * )
     */


    public function detalharCarvana($id)
    {
        $caravana = Caravana::with([
            'imagens',
            'organizador' => function ($query) {
                $query->select('id', 'razao_social', 'nome_fantasia', 'bairro', 'cidade', 'estado', 'created_at');
            },
            'organizador.user' => function ($query) {
                $query->select('id', 'foto_perfil');
            }
        ])->find($id);

        if (!$caravana) {
            return response()->json([
                'status' => false,
                'message' => 'Caravana nao encontrada!'
            ], 404);
        }

        // Obtém a média de todas as avaliações que o organizador recebeu
        $mediaAvaliacao = DB::table('avaliacoes')
            ->where('organizador_id', $caravana->organizador_id)
            ->where('organizador', 1)
            ->avg('nota');

        return response()->json([
            'status' => true,
            'data' => $caravana,
            'mediaAvaliacao' => $mediaAvaliacao
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/minhas-caravanas",
     *     summary="Listar minhas caravanas",
     *     description="Retorna a lista de caravanas criadas pelo organizador autenticado.",
     *     tags={"Caravanas"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de caravanas encontradas",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="titulo", type="string", example="Caravana para o Show X"),
     *                     @OA\Property(property="descricao", type="string", example="Descrição da caravana..."),
     *                     @OA\Property(property="data_partida", type="string", format="date", example="2025-05-01"),
     *                     @OA\Property(property="data_retorno", type="string", format="date", example="2025-05-02"),
     *                     @OA\Property(property="numero_vagas", type="integer", example=40),
     *                     @OA\Property(property="valor", type="number", format="float", example=120.50),
     *                     @OA\Property(property="evento_id", type="integer", example=3),
     *                     @OA\Property(
     *                         property="imagens",
     *                         type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="url", type="string", example="https://meusite.com/imagens/imagem1.jpg")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Nenhuma caravana encontrada para o organizador",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Você ainda não criou nenhuma caravana.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Não autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */

    public function listarMinhasCaravanas()
    {
        $usuario = auth()->user();

        $caravanas = Caravana::with('imagens')
            ->where('organizador_id', $usuario->id)
            ->get();

        if ($caravanas->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Você ainda não criou nenhuma caravana.'
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
     *             required={"titulo", "descricao", "categoria", "data_partida", "data_retorno", "endereco_origem", "numero_origem", "bairro_origem", "cep_origem", "cidade_origem", "estado_origem", "endereco_destino", "numero_destino", "bairro_destino", "cep_destino", "cidade_destino", "estado_destino", "numero_vagas", "valor"},
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
     *             ),
     *             @OA\Property(
     *                 property="ordem_imagens",
     *                 type="array",
     *                 @OA\Items(
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 description="Ordem das imagens, onde cada valor corresponde à posição desejada para cada imagem"
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
            'data_partida' => 'sometimes|date',
            'data_retorno' => 'sometimes|date',
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
            'organizador_id' => 'required|integer'
        ])->validate();

        $validated['vagas_disponiveis'] = $validated['numero_vagas'];

        try {
            // Criação da caravana com dados validados
            $caravana = Caravana::create($validated);

            // Diretório base para as imagens
            $caravanaId = $caravana->id;
            $folderPath = "caravanas/{$caravanaId}";

            // Para armazenar URLs das imagens
            $imageUrls = [];

            // Verifica se as imagens foram enviadas
            if ($request->hasFile('imagens')) {

                $ordens = $request->input('ordem_imagens');

                foreach ($request->file('imagens') as $index => $imagem) {
                    // Nome original da imagem
                    $fileName = $imagem->getClientOriginalName();

                    // Upload da imagem para o S3
                    $path = $imagem->storeAs($folderPath, $fileName, 's3');

                    // URL pública da imagem
                    $url = Storage::disk('s3')->url($path);

                    // Converte ordem para inteiro (ou usa índice como fallback)
                    $ordem = isset($ordens[$index]) ? (int) $ordens[$index] : $index + 1;

                    // Registra a imagem no banco
                    CaravanaImagem::create([
                        'ordem' => $ordem,
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
     *     summary="Editar uma caravana",
     *     description="Permite que um organizador edite as informações de uma caravana que ele criou, incluindo a atualização de dados gerais, imagens, ordem das imagens e remoção de imagens.",
     *     tags={"Caravanas"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da caravana a ser editada",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dados da caravana para atualização (Como item de dados no form-data)",
     *         @OA\JsonContent(
     *             @OA\Property(property="titulo", type="string", example="Caravana para o Show X"),
     *             @OA\Property(property="descricao", type="string", example="Descrição da caravana..."),
     *             @OA\Property(property="categoria", type="string", example="festa"),
     *             @OA\Property(property="data_partida", type="string", format="date", example="2025-05-01"),
     *             @OA\Property(property="data_retorno", type="string", format="date", example="2025-05-02"),
     *             @OA\Property(property="endereco_origem", type="string", example="Rua A, 123"),
     *             @OA\Property(property="numero_origem", type="string", example="123"),
     *             @OA\Property(property="bairro_origem", type="string", example="Centro"),
     *             @OA\Property(property="cep_origem", type="string", example="12345-678"),
     *             @OA\Property(property="cidade_origem", type="string", example="Sorocaba"),
     *             @OA\Property(property="estado_origem", type="string", example="SP"),
     *             @OA\Property(property="endereco_destino", type="string", example="Avenida B, 456"),
     *             @OA\Property(property="numero_destino", type="string", example="456"),
     *             @OA\Property(property="bairro_destino", type="string", example="Zona Sul"),
     *             @OA\Property(property="cep_destino", type="string", example="87654-321"),
     *             @OA\Property(property="cidade_destino", type="string", example="São Paulo"),
     *             @OA\Property(property="estado_destino", type="string", example="SP"),
     *             @OA\Property(property="numero_vagas", type="integer", example=40),
     *             @OA\Property(property="valor", type="number", format="float", example=100.50),
     *             @OA\Property(property="organizador_id", type="integer", example=1),
     *             @OA\Property(property="imagens", type="array",
     *                 @OA\Items(type="string", format="binary", description="Pode enviar um array ou files"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Caravana atualizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Caravana atualizada com sucesso!"),
     *             @OA\Property(property="data", type="object", example={
     *                 "titulo": "Caravana para o Show X",
     *                 "descricao": "Descrição da caravana...",
     *                 "categoria": "festa",
     *                 "data_partida": "2025-05-01",
     *                 "data_retorno": "2025-05-02",
     *                 "endereco_origem": "Rua A, 123",
     *                 "numero_origem": "123",
     *                 "bairro_origem": "Centro",
     *                 "cep_origem": "12345-678",
     *                 "cidade_origem": "Sorocaba",
     *                 "estado_origem": "SP",
     *                 "endereco_destino": "Avenida B, 456",
     *                 "numero_destino": "456",
     *                 "bairro_destino": "Zona Sul",
     *                 "cep_destino": "87654-321",
     *                 "cidade_destino": "São Paulo",
     *                 "estado_destino": "SP",
     *                 "numero_vagas": 40,
     *                 "valor": 100.50,
     *                 "organizador_id": 1
     *             }),
     *             @OA\Property(property="imagens", type="array", @OA\Items(type="string", example="url_da_imagem"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Usuário sem permissão para editar a caravana",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Você não tem permissão para editar esta caravana.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dados inválidos ou mal formatados",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Os dados da caravana são inválidos ou não foram enviados corretamente.")
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
        $user = Auth::user();

        if (!$user->organizador) {
            return response()->json([
                'status' => false,
                'message' => 'Apenas usuários do tipo organizador podem editar caravanas.',
            ], 403);
        }

        try {

            $dadosJson = $request->input('dados');

            if (!$dadosJson) {
                return response()->json([
                    'status' => false,
                    'message' => 'Os dados da caravana são inválidos ou não foram enviados corretamente.',
                ], 400);
            }

            $dados = json_decode($dadosJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'status' => false,
                    'message' => 'Erro ao decodificar os dados da caravana.',
                ], 400);
            }

            $validated = validator($dados, [
                'titulo' => 'sometimes|required|string',
                'descricao' => 'sometimes|required|string',
                'categoria' => 'sometimes|required|string',
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
                'organizador_id' => 'sometimes|required|integer',
            ])->validate();

            $caravana = Caravana::findOrFail($id);

            if ($caravana->organizador_id !== $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Você não tem permissão para editar esta caravana.',
                ], 403);
            }

            $caravana->update($validated);

            // Processamento das imagens
            $entries = $request->all()['imagens'] ?? [];
            $order = 0;
            $idsMantidos = [];

            foreach ($entries as $entry) {
                if (is_numeric($entry)) {
                    // Força a conversão explicita para int
                    $idEntry = (int) $entry;
                    // ID de imagem já existente
                    $img = CaravanaImagem::where('id', $idEntry)
                        ->where('caravana_id', $caravana->id)
                        ->first();

                    if ($img) {
                        $img->update(['ordem' => ++$order]);
                        $idsMantidos[] = $idEntry;
                    }
                } elseif ($entry instanceof UploadedFile) {
                    // Novo arquivo
                    $path = $entry->storeAs("caravanas/{$caravana->id}", $entry->getClientOriginalName(), 's3');
                    $url = Storage::disk('s3')->url($path);

                    $novaImg = CaravanaImagem::create([
                        'caravana_id' => $caravana->id,
                        'path' => $url,
                        'ordem' => ++$order,
                    ]);

                    $idsMantidos[] = $novaImg->id;
                }
            }

            // Força todos os ids para inteiro
            $idsMantidos = array_map('intval', $idsMantidos);

            // Remoção de imagens excluídas
            CaravanaImagem::where('caravana_id', $caravana->id)
                ->whereNotIn('id', $idsMantidos)
                ->get()
                ->each(function ($img) {
                    Storage::disk('s3')->delete(str_replace(Storage::disk('s3')->url(''), '', $img->path));
                    $img->delete();
                });

            // Recarrega e ordena as imagens
            $caravana->load(['imagens' => function ($query) {
                $query->orderBy('ordem');
            }]);

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
        $user = Auth::user();

        if (!$user->organizador) {
            return response()->json([
                'status' => false,
                'message' => 'Apenas usuários do tipo organizador podem excluir caravanas.',
            ], 403);
        }

        try {
            DB::beginTransaction();

            $caravana = Caravana::findOrFail($id);

            if ($caravana->organizador_id !== $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Você não tem permissão para excluir esta caravana.',
                ], 403);
            }

            $passageirosConfirmados = DB::table('caravana_passageiros')
                ->where('caravana_id', $caravana->id)
                ->where('status', 'confirmado')
                ->count();

            if ($passageirosConfirmados > 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'A caravana não pode ser excluída pois já possui passageiros confirmados.',
                ], 400);
            }

            // Apaga as imagens da caravana no banco e no S3
            $imagens = CaravanaImagem::where('caravana_id', $caravana->id)->get();

            foreach ($imagens as $imagem) {
                $path = parse_url($imagem->path, PHP_URL_PATH);
                $path = ltrim($path, '/');
                Storage::disk('s3')->delete($path);
                $imagem->delete();
            }

            // Apaga a caravana
            $caravana->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Caravana excluída com sucesso!',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Erro ao excluir a caravana.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/caravanas",
     *     summary="Listar ou filtrar caravanas",
     *     description="Retorna todas as caravanas se nenhum filtro for fornecido. Caso contrário, filtra com base nos parâmetros enviados.",
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
     *         description="Lista de caravanas (filtradas ou todas)",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Caravanas listadas com sucesso."),
     *             @OA\Property(
     *                 property="data",
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
     *                     @OA\Property(property="vagas_disponiveis", type="integer", example=10),
     *                     @OA\Property(
     *                         property="imagens",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="url", type="string", example="https://exemplo.com/imagem.jpg")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Nenhuma caravana encontrada",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Nenhuma caravana encontrada.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(
     *                     property="origem",
     *                     type="array",
     *                     @OA\Items(type="string", example="O campo origem não pode ter mais que 100 caracteres.")
     *                 )
     *             )
     *         )
     *     )
     * )
     */


    public function listarOuFiltrarCaravanas(Request $request)
    {
        $request->validate([
            'origem' => 'nullable|string|max:100',
            'destino' => 'nullable|string|max:100',
            'titulo' => 'nullable|string|max:100',
            'categoria' => 'nullable|string|max:100',
        ]);

        $query = Caravana::with('imagens');

        if ($request->filled('origem')) {
            $query->where('cidade_origem', 'like', '%' . $request->origem . '%');
        }

        if ($request->filled('destino')) {
            $query->where('cidade_destino', 'like', '%' . $request->destino . '%');
        }

        if ($request->filled('titulo')) {
            $query->where('titulo', 'like', '%' . $request->titulo . '%');
        }

        if ($request->filled('categoria')) {
            $query->where('categoria', 'like', '%' . $request->categoria . '%');
        }

        $caravanas = $query->get();

        if ($caravanas->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Nenhuma caravana encontrada.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Caravanas listadas com sucesso.',
            'data' => $caravanas,
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

    /**
     * @OA\Get(
     *     path="/caravanas/{passageiro_id}/historico",
     *     summary="Obtém o histórico de caravanas em que o passageiro participou",
     *     description="Recupera a lista de caravanas que o passageiro participou, incluindo detalhes da caravana, organizador, status da reserva e avaliação.",
     *     operationId="historicoCaravanas",
     *     tags={"Caravanas"},
     *     @OA\Parameter(
     *         name="passageiro_id",
     *         in="path",
     *         description="ID do passageiro",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Histórico de caravanas obtido com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Caravanas listadas com sucesso."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="caravana",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="titulo", type="string", example="Excursão para o Rock in Rio"),
     *                         @OA\Property(property="descricao", type="string", example="Viagem de ônibus para o Rock in Rio."),
     *                         @OA\Property(property="categoria", type="string", example="Shows"),
     *                         @OA\Property(property="data_partida", type="string", format="date", example="2025-09-10"),
     *                         @OA\Property(property="data_retorno", type="string", format="date", example="2025-09-12"),
     *                         @OA\Property(property="endereco_origem", type="string", example="Rua das Flores"),
     *                         @OA\Property(property="numero_origem", type="string", example="123"),
     *                         @OA\Property(property="bairro_origem", type="string", example="Centro"),
     *                         @OA\Property(property="cep_origem", type="string", example="12345-678"),
     *                         @OA\Property(property="cidade_origem", type="string", example="São Paulo"),
     *                         @OA\Property(property="estado_origem", type="string", example="SP"),
     *                         @OA\Property(property="endereco_destino", type="string", example="Av. Atlântica"),
     *                         @OA\Property(property="numero_destino", type="string", example="500"),
     *                         @OA\Property(property="bairro_destino", type="string", example="Copacabana"),
     *                         @OA\Property(property="cep_destino", type="string", example="22070-000"),
     *                         @OA\Property(property="cidade_destino", type="string", example="Rio de Janeiro"),
     *                         @OA\Property(property="estado_destino", type="string", example="RJ"),
     *                         @OA\Property(property="numero_vagas", type="integer", example=40),
     *                         @OA\Property(property="vagas_disponiveis", type="integer", example=10),
     *                         @OA\Property(property="valor", type="number", format="float", example=299.99),
     *                         @OA\Property(
     *                             property="organizador",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=2),
     *                             @OA\Property(property="razao_social", type="string", example="Turismo LTDA"),
     *                             @OA\Property(property="nome_fantasia", type="string", example="Turismo Viagens"),
     *                         ),
     *                     ),
     *                     @OA\Property(property="reserva_id", type="integer", example=15),
     *                     @OA\Property(property="status", type="string", example="Confirmado"),
     *                     @OA\Property(property="nota", type="number", format="float", example=4.5),
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Usuário não autorizado",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Não autorizado, é necessário estar logado para obter o histórico de caravanas.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Nenhuma caravana encontrada",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Nenhuma caravana encontrada.")
     *         )
     *     )
     * )
     */

    public function historicoCaravanas($passageiro_id)
    {
        $usuario = auth()->user();

        if ($usuario->id != $passageiro_id) {
            return response()->json([
                'status' => false,
                'message' => 'Não autorizado, é necessário estar logado para obter o histórico de caravanas.',
            ], 403); // HTTP 403 Forbidden
        }

        // Recupera as caravanas que o passageiro participou
        $caravanasPassageiro = CaravanaPassageiro::with('caravana.organizador', 'caravana.avaliacao')
            ->where('passageiro_id', $passageiro_id)
            ->get();

        if ($caravanasPassageiro->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Nenhuma caravana encontrada.'
            ], 404);
        }

        // Array de resposta
        $caravanas = $caravanasPassageiro->map(function ($item) use ($passageiro_id) {
            $caravana = $item->caravana;
            $avaliacao = $caravana->avaliacao
                ->where('passageiro_id', $passageiro_id)
                ->where('organizador', true)
                ->first();

            return [
                'caravana' => [
                    'id' => $caravana->id,
                    'titulo' => $caravana->titulo,
                    'descricao' => $caravana->descricao,
                    'categoria' => $caravana->categoria,
                    'data_partida' => $caravana->data_partida,
                    'data_retorno' => $caravana->data_retorno,
                    'endereco_origem' => $caravana->endereco_origem,
                    'numero_origem' => $caravana->numero_origem,
                    'bairro_origem' => $caravana->bairro_origem,
                    'cep_origem' => $caravana->cep_origem,
                    'cidade_origem' => $caravana->cidade_origem,
                    'estado_origem' => $caravana->estado_origem,
                    'endereco_destino' => $caravana->endereco_destino,
                    'numero_destino' => $caravana->numero_destino,
                    'bairro_destino' => $caravana->bairro_destino,
                    'cep_destino' => $caravana->cep_destino,
                    'cidade_destino' => $caravana->cidade_destino,
                    'estado_destino' => $caravana->estado_destino,
                    'numero_vagas' => $caravana->numero_vagas,
                    'vagas_disponiveis' => $caravana->vagas_disponiveis,
                    'valor' => $caravana->valor,
                    'organizador' => $caravana->organizador ? [
                        'id' => $caravana->organizador->id,
                        'razao_social' => $caravana->organizador->razao_social,
                        'nome_fantasia' => $caravana->organizador->nome_fantasia,
                    ] : null,
                ],
                'reserva_id' => $item->id,
                'status' => $item->status,
                'nota' => $avaliacao ? $avaliacao->nota : null,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Caravanas listadas com sucesso.',
            'data' => $caravanas,
        ]);
    }
}
