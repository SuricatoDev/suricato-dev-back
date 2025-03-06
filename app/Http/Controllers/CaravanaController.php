<?php

namespace App\Http\Controllers;

use App\Models\Caravana;
use App\Models\CaravanaImagem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function update(Request $request, $id)
    {
        // Verifica se o usuário logado é do tipo 'organizador'
        $user = Auth::user();
        if ($user->tipo !== 'organizador') {
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

    public function destroy($id)
    {
        // Verifica se o usuário logado é do tipo 'organizador'
        $user = Auth::user();
        if ($user->tipo !== 'organizador') {
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
