<?php

namespace App\Mail;

use App\Models\CaravanaPassageiro;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConfirmarReservaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $caravana;
    public $passageiro;
    public $reserva;

    public function __construct(CaravanaPassageiro $reserva, $user, $caravana, $passageiro)
    {
        $this->reserva = $reserva;
        $this->user = $user;
        $this->caravana = $caravana;
        $this->passageiro = $passageiro;
    }

    public function build()
    {
        return $this->subject('🎉 Reserva confirmada — Prepare-se para o evento!')
                    ->markdown('emails.emailReservaConfirmada');
    }
}
