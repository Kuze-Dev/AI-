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
        return new Envelope(
            from: $this->formEmailNotification->sender,
            to: $this->formEmailNotification->to,
            cc: $this->formEmailNotification->cc ?? [],
            bcc: $this->formEmailNotification->bcc ?? [],
            replyTo: $this->formEmailNotification->reply_to ?? [],
            subject: $this->formEmailNotification->subject,
        );
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
