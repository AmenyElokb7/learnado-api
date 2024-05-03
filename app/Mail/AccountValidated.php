<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountValidated extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $username;

    /**
     * Create a new message instance.
     */
    public function __construct($email, $username)
    {
        $this->email = $email;
        $this->username = $username;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Account Validated',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.accountValidated',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    public function build()
    {
        $logoUrl = asset('assets/lernado.png');
        return $this->subject('Account Validated')
            ->markdown('emails.users.account-validated');
    }
}
