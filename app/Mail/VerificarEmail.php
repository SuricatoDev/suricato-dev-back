<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificarEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    protected $link;

    public function __construct(User $user, $link)
    {
        $this->user = $user;
        $this->link = $link;
    }

    /**
     * Get the message envelope.
     */
    public function build()
    {
        return $this->subject('📩 Bem-vindo ao Excursionistas - Confirme seu e-mail')
                    ->markdown('emails.emailConfirmacao')
                    ->with([
                        'user' => $this->user,
                        'link' => $this->link
                    ]);
    }
}
