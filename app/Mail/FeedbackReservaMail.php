<?php

namespace App\Mail;

use App\Models\CaravanaPassageiro;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FeedbackReservaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $caravanaPassageiro;
    public $caravana;
    public $dadosOrganizador;

    public function __construct(CaravanaPassageiro $caravanaPassageiro, $user, $caravana, $dadosOrganizador)
    {
        $this->caravanaPassageiro = $caravanaPassageiro;
        $this->user = $user;
        $this->caravana = $caravana;
        $this->dadosOrganizador = $dadosOrganizador;
    }

    public function build()
    {
        return $this->subject('🧡 Solicitação de inscrição realizada! - Excursionistas')
                    ->markdown('emails.emailFeedbackReserva');
    }
}
