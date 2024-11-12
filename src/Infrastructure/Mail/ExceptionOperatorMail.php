<?php

declare(strict_types=1);

namespace Infrastructure\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ExceptionOperatorMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(private readonly \Throwable $exception)
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $exception = $this->exception;
        $subject = blank($exception->getMessage())
            ? class_basename($exception)
            : $exception->getMessage();

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.operator-exception-mail',
            with: [
                'exception' => $this->exception,
            ],
        );
    }
}
