<?php

namespace App\Mail;

use App\Models\Suporte;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SuporteRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $suporte;
    public $user;

    public function __construct(Suporte $suporte, $user)
    {
        $this->suporte = $suporte;
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('📩 Novo Pedido de Suporte - Excursionistas')
                    ->markdown('emails.emailSuporte');
    }
}

