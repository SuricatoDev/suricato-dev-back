<?php

namespace App\Http\Controllers;

use App\Models\Caravana;
use App\Models\Denuncia;
use App\Models\Organizador;
use App\Models\Passageiro;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DenunciaController extends Controller
{

    // public function registrarDenuncia(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'denunciante_id' => 'required|exists:users,id',
    //             'topico' => 'required|string|in:Pagamento,Cancelamento,Inconsistência,Segurança,Outro',
    //             'descricao' => 'required|string',
    //         ]);

    //         DB::beginTransaction();

    //         // Obtém o usuário denunciante
    //         $denunciante = User::findOrFail($request->denunciante_id);

    //         // Inicializa os campos como nulos
    //         $denunciaData = [
    //             'denunciante_id' => $denunciante->id,
    //             'topico' => $request->topico,
    //             'descricao' => $request->descricao,
    //             'status' => 'Pendente',
    //             'caravana_id' => null,
    //             'organizador_id' => null,
    //             'passageiro_id' => null,
    //         ];

    //         // Define o campo correto com base no tipo do usuário denunciante
    //         if ($denunciante->passageiro) {
    //             if ($request->has('caravana_id')) {
    //                 $caravana = Caravana::findOrFail($request->caravana_id);
    //                 $denunciaData['caravana_id'] = $caravana->id;
    //             } elseif ($request->has('organizador_id')) {
    //                 $organizador = Organizador::findOrFail($request->organizador_id);
    //                 $denunciaData['organizador_id'] = $organizador->id;
    //             } else {
    //                 throw new \Exception("Passageiros só podem denunciar caravanas ou organizadores.");
    //             }
    //         } elseif ($denunciante->organizador) {
    //             // Organizador pode denunciar um passageiro
    //             if ($request->has('passageiro_id')) {
    //                 $passageiro = Passageiro::findOrFail($request->passageiro_id);
    //                 $denunciaData['passageiro_id'] = $passageiro->id;
    //             } else {
    //                 throw new \Exception("Organizadores só podem denunciar passageiros.");
    //             }
    //         } else {
    //             throw new \Exception("Tipo de usuário inválido para registrar denúncia.");
    //         }

    //         // Cria a denúncia com os dados definidos
    //         $denuncia = Denuncia::create($denunciaData);

    //         DB::commit();

    //         return response()->json([
    //             'message' => 'Denúncia registrada com sucesso!',
    //             'denuncia' => $denuncia
    //         ], 201);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'message' => 'Erro ao registrar denúncia.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }



    // public function editarDenuncia(Request $request, $id)
    // {
    //     try {
    //         $user = Auth::user(); // Obtém o usuário autenticado
    //         $denuncia = Denuncia::findOrFail($id);

    //         // Verifica se o usuário logado é o autor da denúncia
    //         if ($denuncia->denunciante_id !== $user->id) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Você não tem permissão para editar esta denúncia.'
    //             ], 403);
    //         }

    //         // Validação dos campos que podem ser atualizados
    //         $validated = $request->validate([
    //             'topico' => 'sometimes|string|max:255',
    //             'descricao' => 'sometimes|string',
    //             'status' => 'sometimes|in:Pendente, Em andamento, Concluído', // Evita status inválidos
    //         ]);

    //         // Atualiza apenas os campos válidos
    //         $denuncia->fill($validated);
    //         $denuncia->save();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Denúncia atualizada com sucesso!',
    //             'denuncia' => $denuncia
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Erro ao atualizar denúncia.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }



    // public function excluirDenuncia($id)
    // {
    //     try {
    //         $user = Auth::user(); // Obtém o usuário autenticado
    //         $denuncia = Denuncia::findOrFail($id);

    //         // Verifica se o usuário logado é o autor da denúncia
    //         if ($denuncia->denunciante_id !== $user->id) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Você não tem permissão para excluir esta denúncia.'
    //             ], 403);
    //         }

    //         $denuncia->delete();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Denúncia excluída com sucesso!'
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Erro ao excluir denúncia.',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }
}
