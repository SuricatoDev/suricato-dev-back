<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CepController extends Controller
{
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
