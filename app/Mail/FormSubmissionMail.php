<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FormSubmissionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $payload,
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject('Новая заявка с формы обратной связи')
            ->view('emails.form_submission')
            ->with(['data' => $this->payload]);
    }
}
