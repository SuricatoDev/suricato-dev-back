<?php

namespace App\Mail;

use App\Http\Controllers\CaravanaPassageiroController;
use App\Models\CaravanaPassageiro;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservaRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $caravanaPassageiro;
    public $caravana;
    public $dadosOrganizador;
    public $telefonePassageiro;

    public function __construct(CaravanaPassageiro $caravanaPassageiro, $user, $caravana, $dadosOrganizador, $telefonePassageiro)
    {
        $this->caravanaPassageiro = $caravanaPassageiro;
        $this->user = $user;
        $this->caravana = $caravana;
        $this->dadosOrganizador = $dadosOrganizador;
        $this->telefonePassageiro = $telefonePassageiro;
    }

    public function build()
    {
        return $this->subject('📩 Nova solicitação de reserva - Excursionistas')
                    ->markdown('emails.emailReserva');
    }
}
