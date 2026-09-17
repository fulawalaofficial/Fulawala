<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetCodeMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $code,
        public int $expiresInMinutes = 10
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject('Flower Delivery password reset code')
            ->view('emails.password-reset-code')
            ->with([
                'code' => $this->code,
                'expiresInMinutes' => $this->expiresInMinutes,
            ]);
    }
}
