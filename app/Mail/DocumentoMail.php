<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $nombreAlumno,
        public string $tipoDocumento,
        public string $base64Pdf
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Tu {$this->tipoDocumento} — Duoc UC",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.documento',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn() => base64_decode($this->base64Pdf),
                str_replace(' ', '_', $this->tipoDocumento) . '.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
