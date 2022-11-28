<?php

declare(strict_types=1);

namespace Domain\Form\Mail;

use Domain\Form\Models\FormEmailNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FormEmailNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly FormEmailNotification $formEmailNotification
    ) {
    }

    public function envelope(): Envelope
    {
        $envelop = new Envelope(
            from: $this->formEmailNotification->sender,
            to:explode(',', $this->formEmailNotification->recipient),
            subject: 'Form Submission for '.$this->formEmailNotification->form->name,
        );

        if ($this->formEmailNotification->cc !== null) {
            $envelop->cc(explode(',', $this->formEmailNotification->cc));
        }

        if ($this->formEmailNotification->bcc !== null) {
            $envelop->bcc(explode(',', $this->formEmailNotification->bcc));
        }

        if ($this->formEmailNotification->reply_to !== null) {
            $envelop->replyTo($this->formEmailNotification->reply_to);
        }

        return $envelop;
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->formEmailNotification->template,
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
