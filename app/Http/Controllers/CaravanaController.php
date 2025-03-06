<?php

namespace App\Http\Controllers;

use App\Models\Caravana;
use App\Models\CaravanaImagem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CaravanaController extends Controller
{
    public function index()
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

    public function store(Request $request)
    {
        // Verifica se o usuário logado é do tipo 'organizador'
        $user = Auth::user(); // Obtém o usuário autenticado
        if ($user->tipo !== 'organizador') {
            return response()->json([
                'status' => false,
                'message' => 'Apenas usuários do tipo organizador podem criar caravanas.',
            ], 403); // Status 403 - Forbidden
        }

        // Validação dos dados de entrada
        $validated = $request->validate([
            'titulo' => 'required|string',
            'descricao' => 'required|string',
            'data_partida' => 'required|date',
            'data_retorno' => 'required|date',
            'origem' => 'required|string',
            'destino' => 'required|string',
            'numero_vagas' => 'required|integer',
            'valor' => 'required|numeric',
            'organizador_id' => 'required|integer',
            'evento_id' => 'required|integer',
            'imagens' => 'required|array',
            'imagens.*.path' => 'required|string|url',
        ]);

        try {
            // Criação da caravana
            $caravana = Caravana::create([
                'titulo' => $validated['titulo'],
                'descricao' => $validated['descricao'],
                'data_partida' => $validated['data_partida'],
                'data_retorno' => $validated['data_retorno'],
                'origem' => $validated['origem'],
                'destino' => $validated['destino'],
                'numero_vagas' => $validated['numero_vagas'],
                'valor' => $validated['valor'],
                'organizador_id' => $validated['organizador_id'],
                'evento_id' => $validated['evento_id'],
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
}
