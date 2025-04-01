<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CNPJController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/cnpj/{cnpj}",
     *     summary="Buscar informações de um CNPJ",
     *     tags={"CNPJ"},
     *     @OA\Parameter(
     *         name="cnpj",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             example="61084018000103",
     *             description="CNPJ a ser consultado"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Informações do CNPJ encontradas com sucesso.",
     *         @OA\JsonContent(
     *             @OA\Property(property="razao_social", type="string", example="Empresa Exemplo LTDA", description="Razão social do CNPJ"),
     *             @OA\Property(property="nome_fantasia", type="string", example="Empresa Exemplo", description="Nome fantasia do CNPJ"),
     *             @OA\Property(property="inscricao_estadual", type="string", example="123456789", description="Inscrição estadual do CNPJ"),
     *             @OA\Property(property="inscricao_municipal", type="string", example="987654321", description="Inscrição municipal do CNPJ"),
     *             @OA\Property(property="cep", type="string", example="12345-678", description="CEP do CNPJ"),
     *             @OA\Property(property="endereco", type="string", example="Rua Exemplo, 123", description="Endereço do CNPJ"),
     *             @OA\Property(property="numero", type="string", example="123", description="Número do endereço do CNPJ"),
     *             @OA\Property(property="message", type="string", example="CNPJ encontrado com sucesso.", description="Mensagem de sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="CNPJ não encontrado.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="CNPJ não encontrado.", description="Mensagem de erro")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao buscar o CNPJ.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao buscar o CNPJ. Tente novamente.", description="Mensagem de erro")
     *         )
     *     )
     * )
     */


    // Método para consultar o cnpj via Brasil API

    public function buscarCnpj($cnpj)
    {
        // Faz a requisição para a API do Brasil API
        $response = Http::get("https://brasilapi.com.br/api/cnpj/v1/{$cnpj}");

        // Verifica se a requisição foi bem-sucedida
        if ($response->failed()) {
            return response()->json([
                'message' => 'Erro ao buscar o CNPJ. Tente novamente.',
            ], 500);
        }

        // Converte a resposta para JSON
        $cnpj = $response->json();

        // Verifica se o CNPJ foi encontrado
        if (isset($dados['erro'])) {
            return response()->json([
                'message' => 'CNPJ não encontrado.',
            ], 404);
        }

        // Retorna os dados formatados
        return response()->json([
            'dados' => $cnpj,
            'message' => 'CNPJ encontrado com sucesso.',
        ], 200);
    }
}
