<?php

namespace App\Http\Controllers;

use App\Models\Caravana;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VeiculoController extends Controller
{
    public function cadastrarVeiculo(Request $request)
    {
        // Obtém o usuário autenticado
        $user = Auth::user();

        // Verifica se o usuário é um organizador
        if ($user->tipo !== 'organizador') {
            return response()->json([
                'status' => false,
                'message' => 'Apenas organizadores podem cadastrar veículos!'
            ], 403);
        }

        // Validação dos dados de entrada
        $validated = $request->validate([
            'placa' => 'required|string|unique:veiculos,placa|max:10',
            'marca' => 'required|string|max:50',
            'antt' => 'required|string|unique:veiculos,antt|max:20',
            'tipo' => 'required|in:Van,Ônibus,Micro-ônibus',
            'capacidade' => 'required|integer|min:1',
            'motorista' => 'required|string|max:100',
            'contato_motorista' => 'required|string|size:11',
            'caravana_id' => 'required|integer|exists:caravanas,id',
        ]);

        // Verifica se a caravana pertence ao organizador logado
        $caravana = Caravana::where('id', $validated['caravana_id'])
            ->where('organizador_id', $user->id)
            ->first();

        if (!$caravana) {
            return response()->json([
                'status' => false,
                'message' => 'Você só pode cadastrar veículos em suas próprias caravanas!'
            ], 403);
        }

        // Criação do veículo
        $veiculo = Veiculo::create([
            'placa' => strtoupper($validated['placa']), // Placa em maiúsculas
            'marca' => $validated['marca'],
            'antt' => $validated['antt'],
            'tipo' => $validated['tipo'],
            'capacidade' => $validated['capacidade'],
            'motorista' => $validated['motorista'],
            'contato_motorista' => $validated['contato_motorista'],
            'organizador_id' => $user->id,
            'caravana_id' => $validated['caravana_id'],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Veículo cadastrado com sucesso!',
            'data' => $veiculo
        ], 201);
    }

    public function atualizarVeiculo(Request $request, $id)
    {
        // Obtém o usuário autenticado
        $user = Auth::user();

        // Verifica se o usuário é um organizador
        if ($user->tipo !== 'organizador') {
            return response()->json([
                'status' => false,
                'message' => 'Apenas organizadores podem atualizar veículos!'
            ], 403);
        }

        // Busca o veículo e verifica se pertence ao organizador
        $veiculo = Veiculo::where('id', $id)
            ->where('organizador_id', $user->id)
            ->first();

        if (!$veiculo) {
            return response()->json([
                'status' => false,
                'message' => 'Veículo não encontrado ou você não tem permissão para editá-lo!'
            ], 404);
        }

        // Validação dos dados de entrada
        $validated = $request->validate([
            'placa' => 'string|unique:veiculos,placa,' . $id . '|max:10',
            'marca' => 'string|max:50',
            'antt' => 'string|unique:veiculos,antt,' . $id . '|max:20',
            'tipo' => 'in:Van,Ônibus,Micro-ônibus',
            'capacidade' => 'integer|min:1',
            'motorista' => 'string|max:100',
            'contato_motorista' => 'string|size:11',
            'caravana_id' => 'integer|exists:caravanas,id',
        ]);

        // Verifica se a caravana pertence ao organizador (se for alterada)
        if ($request->has('caravana_id')) {
            $caravana = Caravana::where('id', $validated['caravana_id'])
                ->where('organizador_id', $user->id)
                ->first();
            if (!$caravana) {
                return response()->json([
                    'status' => false,
                    'message' => 'Você só pode vincular o veículo a suas próprias caravanas!'
                ], 403);
            }
        }

        // Atualiza os dados do veículo
        $veiculo->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Veículo atualizado com sucesso!',
            'data' => $veiculo
        ], 200);
    }

    public function excluirVeiculo($id)
    {
        // Obtém o usuário autenticado
        $user = Auth::user();

        // Verifica se o usuário é um organizador
        if ($user->tipo !== 'organizador') {
            return response()->json([
                'status' => false,
                'message' => 'Apenas organizadores podem excluir veículos!'
            ], 403);
        }

        // Busca o veículo e verifica se pertence ao organizador
        $veiculo = Veiculo::where('id', $id)
            ->where('organizador_id', $user->id)
            ->first();

        if (!$veiculo) {
            return response()->json([
                'status' => false,
                'message' => 'Veículo não encontrado ou você não tem permissão para excluí-lo!'
            ], 404);
        }

        // Exclui o veículo
        $veiculo->delete();

        return response()->json([
            'status' => true,
            'message' => 'Veículo excluído com sucesso!'
        ], 200);
    }
}
