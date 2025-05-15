<?php

namespace App\Http\Controllers;

use App\Mail\ConfirmarReservaMail;
use App\Mail\FeedbackReservaMail;
use App\Mail\ReservaRequestMail;
use App\Models\Caravana;
use App\Models\CaravanaPassageiro;
use App\Models\Organizador;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

/**
 *
 * @OA\Tag(
 *     name="Reservas",
 *     description="Rotas relacionadas ao gerenciamento de reservas"
 * )
 */

class CaravanaPassageiroController extends Controller
{
    /**
     * @OA\Get(
     *     path="/caravana/{id}/listar-reservas",
     *     summary="Visualizar todas as reservas de uma caravana, somente para o organizador",
     *     description="Este método permite que o organizador visualize todas as reservas de sua caravana.",
     *     operationId="listarReservas",
     *     tags={"Reservas"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da caravana",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reservas da caravana encontradas com sucesso",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="passageiro_id", type="integer", description="ID do passageiro"),
     *                 @OA\Property(property="nome", type="string", description="Nome do passageiro"),
     *                 @OA\Property(property="status", type="string", enum={"pendente", "confirmada", "cancelada"}, description="Status da reserva"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", description="Data de criação da reserva")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acesso não autorizado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Caravana não encontrada"
     *     ),
     * )
     */


    public function listarReservas($id)
    {
        $user = Auth::user();  // Obtém o usuário autenticado
        $caravana = Caravana::findOrFail($id);  // Encontra a caravana pelo ID

        // Verifica se o usuário autenticado é o organizador da caravana
        if ($caravana->organizador_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Acesso não autorizado! Somente o organizador pode visualizar as reservas.',
            ], 403); // Acesso negado
        }

        // Array para receber apenas os dados que o Paulinho pediu
        $passageirosData = [];

        // Obtém todas as reservas da caravana
        $reservas = CaravanaPassageiro::where('caravana_id', $caravana->id)->get();

        // Percorre todas as reservas para obter os dados do passageiro
        foreach ($reservas as $reserva) {
            $usuario = User::find($reserva->passageiro_id); // Busca o passageiro pelo ID

            $passageirosData[] = [
                'passageiro_id' => $reserva->passageiro_id,
                'reserva_id' => $reserva->id,
                'nome' => $usuario ? $usuario->nome : null, // Se não encontrar, deixa como null
                'status' => $reserva->status,
            ];
        }

        // Retorna as reservas em formato JSON
        return response()->json([
            'status' => true,
            'data' => $passageirosData
        ]);
    }

    /**
     * Criação de reserva para o passageiro
     * @OA\Post(
     *     path="/api/caravanas/{id}/reservas",
     *     summary="Reserva de uma caravana",
     *     operationId="criarReserva",
     *     tags={"Reservas"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da caravana",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"passageiro_id"},
     *             @OA\Property(property="passageiro_id", type="integer", description="ID do passageiro")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Apenas passageiros podem reservar"
     *      ),
     *     @OA\Response(
     *         response=403,
     *         description="Acesso não autorizado (Você não pode reservar sua própria caravana!)"
     *      ),
     *     @OA\Response(
     *         response=200,
     *         description="Reserva realizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Reserva realizada com sucesso!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Reserva não pode ser realizada",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Número de vagas excedido!")
     *         )
     *     )
     * )
     */
    public function criarReserva(Request $request, $id)
    {
        // Validação dos dados
        $request->validate([
            'passageiro_id' => 'required|exists:users,id',
        ]);

        $caravana = Caravana::findOrFail($id);
        $user = Auth::user();

        // Verificar se o usuário logado é do tipo passageiro
        if (!$user->passageiro) {
            return response()->json([
                'status' => false,
                'message' => 'Apenas passageiros podem fazer reservas!'
            ], 400);  // Status 400 para requisição mal formulada
        }

        // Verifica se o usuário logado é o organizador da caravana
        if ($user->id == $caravana->organizador_id) {
            return response()->json([
                'status' => false,
                'message' => 'Você não pode fazer uma reserva nesta caravana!'
            ], 403);  // Status 403 para acesso negado
        }

        // Verifica se ainda há vagas disponíveis
        if ($caravana->vagas_disponiveis <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'Não há vagas disponíveis!'
            ], 404);  // Not Found
        }

        // Verifica se o passageiro já tem uma reserva ativa (Pendente ou Ativo) na caravana
        $caravanaPassageiro = CaravanaPassageiro::where('caravana_id', $id)
            ->where('passageiro_id', $user->id)
            ->whereIn('status', ['Pendente', 'Confirmado'])
            ->exists();

        if ($caravanaPassageiro) {
            return response()->json([
                'status' => false,
                'message' => 'Você já possui uma reserva ativa nesta caravana.',
            ], 409); // Conflito
        }


        // Criação da reserva
        $caravanaPassageiro = CaravanaPassageiro::create([
            'data' => $request->input('data', now()), // Define 'data' como a data atual se não for fornecida
            'caravana_id' => $id,
            'passageiro_id' => $user->id,
            'status' => 'Pendente'
        ]);

        // Obtém o organizador da caravana para enviar o email
        $dadosOrganizador = Organizador::where('id', $caravana->organizador_id)->first();

        // Obtém o telefone do passageiro para posterior formatação
        $telefonePassageiro = $this->formatarTelefone($user->telefone); // Formata o telefone;

        // Envia o email para o organizador
        Mail::to($dadosOrganizador->user->email)->send(
            new ReservaRequestMail($caravanaPassageiro, $user, $caravana, $dadosOrganizador, $telefonePassageiro)
        );

        // Envia o email para o passageiro com as instruções
        Mail::to($user->email)->send(
            new FeedbackReservaMail($caravanaPassageiro, $user, $caravana, $dadosOrganizador)
        );

        return response()->json([
            'status' => true,
            'message' => 'Reserva realizada com sucesso!',
            'data' => $caravanaPassageiro,
        ], 201);  // Status 201 para criado
    }

    private function formatarTelefone($telefone)
    {
        $numero = preg_replace('/\D/', '', $telefone);

        if (strlen($numero) === 11) {
            return "(" . substr($numero, 0, 2) . ") " . substr($numero, 2, 5) . "-" . substr($numero, 7);
        }

        if (strlen($numero) === 10) {
            return "(" . substr($numero, 0, 2) . ") " . substr($numero, 2, 4) . "-" . substr($numero, 6);
        }

        return $telefone;
    }

    /**
     * @OA\Put(
     *     path="/caravana/{id}/reserva/{id_reserva}",
     *     summary="Atualizar status de uma reserva",
     *     description="Permite que o organizador da caravana atualize o status de uma reserva.",
     *     operationId="editarReserva",
     *     tags={"Reservas"},
     *     security={{ "bearerAuth":{} }},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da caravana",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="id_reserva",
     *         in="path",
     *         description="ID da reserva",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"Pendente", "Confirmado", "Cancelado"},
     *                 description="Novo status da reserva."
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Status da reserva atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Status da reserva atualizado com sucesso!"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="status", type="string", example="Confirmado"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-10T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-10T12:30:00Z")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Requisição inválida (usuário não possui perfil de organizador ou reserva cancelada)",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Apenas os organizadores podem editar uma reserva! ou Reserva cancelada não pode ser alterada!")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Acesso não autorizado - Organizador logado não é o criador da caravana",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Acesso não autorizado!")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Caravana ou reserva não encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Caravana ou reserva não encontrada!")
     *         )
     *     )
     * )
     */


    public function editarReserva(Request $request, $id, $reserva_id)
    {
        $caravana = Caravana::findOrFail($id);
        $user = Auth::user();

        // Verifica se o usuário é organizador
        if ($user->organizador === false) {
            return response()->json([
                'status' => false,
                'message' => 'Apenas os organizadores podem editar uma reserva!'
            ], 400);
        }

        // Busca a reserva
        $reserva = CaravanaPassageiro::findOrFail($reserva_id);

        // Verifica se a reserva já foi cancelada
        if ($reserva->status === 'Cancelado') {
            return response()->json([
                'status' => false,
                'message' => 'Reserva cancelada não pode ser alterada!'
            ], 400);
        }

        // Verifica se o usuário é o organizador da caravana
        if ($user->id !== $caravana->organizador_id) {
            return response()->json([
                'status' => false,
                'message' => 'Acesso não autorizado!',
            ], 403);
        }

        // Validação do novo status
        $validated = $request->validate([
            'status' => 'required|in:Pendente,Confirmado,Cancelado',
        ]);

        // Reduz o número de vagas da caravana
        $caravana->decrement('vagas_disponiveis', 1);  // Decrementa 1 vaga

        // Atualiza o status da reserva
        $reserva->update([
            'status' => $validated['status'],
        ]);

        // Obtém o email do passageiro
        $passageiro = User::find($reserva->passageiro_id);

        // Envia um email ao usuário informado que a reserva foi confirmada
        if ($validated['status'] === 'Confirmado') {
            Mail::to($passageiro->email)->send(new ConfirmarReservaMail($reserva, $user, $caravana, $passageiro));
        }

        return response()->json([
            'status' => true,
            'message' => 'Status da reserva atualizado com sucesso!',
            'data' => [
                'status' => $reserva->status,
                'created_at' => $reserva->created_at,
                'updated_at' => $reserva->updated_at,
            ],
        ]);
    }

    /**
     * Cancelar a reserva de uma caravana. O passageiro poderá cancelar sua reserva.
     * @OA\Delete(
     *     path="/api/caravana/{id}/reserva/{id_reserva}",
     *     summary="Cancelar uma reserva",
     *     operationId="cancelarReserva",
     *     tags={"Reservas"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID da caravana",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="id_reserva",
     *         in="path",
     *         required=true,
     *         description="ID da reserva",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reserva cancelada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Reserva cancelada com sucesso!"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="passageiro_id", type="integer", example=5),
     *                 @OA\Property(property="status", type="string", example="Cancelado"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-10T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-10T12:30:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Acesso não autorizado (usuário não é o passageiro)",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Você não pode cancelar esta reserva!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Reserva não encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Reserva não encontrada!")
     *         )
     *     )
     * )
     */

    public function cancelarReserva($id, $id_reserva)
    {
        $caravana = Caravana::findOrFail($id);
        $user = Auth::user();

        // Verifica se o usuário é o passageiro da reserva
        $reserva = CaravanaPassageiro::findOrFail($id_reserva);
        if ($reserva->passageiro_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Você não pode cancelar esta reserva!',
            ], 403);
        }

        // Cancela a reserva alterando seu status para "Cancelado"
        $reserva->update([
            'status' => 'Cancelado',
        ]);

        // Reverte a quantidade de vagas na caravana
        $caravana->increment('vagas_disponiveis', 1); // Incrementa 1 vaga'

        return response()->json([
            'status' => true,
            'message' => 'Reserva cancelada com sucesso!',
            'data' => $reserva
        ]);
    }
}
