<?php

namespace App\Jobs;

use App\Mail\FormSubmissionMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendFormEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $primaryRecipient,
        public array $extraRecipients,
        public array $payload,
    ) {
    }

    public function handle(): void
    {
        $recipients = array_values(array_unique(
            array_filter([$this->primaryRecipient, ...$this->extraRecipients])
        ));

        if (!$recipients)
            return;

        $to  = array_shift($recipients);
        $bcc = $recipients;

        Mail::to($to)
            ->bcc($bcc)
            ->send(new FormSubmissionMail($this->payload));
    }
}
