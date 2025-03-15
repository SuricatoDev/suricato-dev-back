<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CepController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/cep/{cep}",
     *     summary="Buscar informações de um CEP",
     *     description="Este endpoint consulta o ViaCEP para retornar informações sobre um determinado CEP.",
     *     tags={"CEP"},
     *     @OA\Parameter(
     *         name="cep",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             example="01001000",
     *             description="CEP a ser consultado"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Informações do CEP encontradas com sucesso.",
     *         @OA\JsonContent(
     *             @OA\Property(property="cep", type="string", example="01001-000", description="CEP encontrado"),
     *             @OA\Property(property="logradouro", type="string", example="Praça da Sé", description="Logradouro do CEP"),
     *             @OA\Property(property="bairro", type="string", example="Centro", description="Bairro do CEP"),
     *             @OA\Property(property="cidade", type="string", example="São Paulo", description="Cidade do CEP"),
     *             @OA\Property(property="uf", type="string", example="SP", description="UF do CEP")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="CEP não encontrado.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="CEP não encontrado.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao buscar o CEP.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao buscar o CEP. Tente novamente.")
     *         )
     *     )
     * )
     */

    public function buscarCep($cep)
    {
        // Faz a requisição para a API do ViaCEP
        $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");

        // Verifica se a requisição foi bem-sucedida
        if ($response->failed()) {
            return response()->json([
                'message' => 'Erro ao buscar o CEP. Tente novamente.',
            ], 500);
        }

        // Converte a resposta para JSON
        $dados = $response->json();

        // Verifica se o CEP foi encontrado
        if (isset($dados['erro'])) {
            return response()->json([
                'message' => 'CEP não encontrado.',
            ], 404);
        }

        // Retorna os dados formatados
        return response()->json([
            'cep' => $dados['cep'],
            'logradouro' => $dados['logradouro'],
            'bairro' => $dados['bairro'],
            'cidade' => $dados['localidade'],
            'uf' => $dados['uf'],
        ]);
    }
}
