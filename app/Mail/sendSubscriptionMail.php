<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class sendSubscriptionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $courseId;
    public $courseTitle;

    /**
     * Create a new message instance.
     *
     * @param int $courseId
     * @param string $courseTitle
     */
    public function __construct($courseId, $courseTitle)
    {
        $this->courseId = $courseId;
        $this->courseTitle = $courseTitle;
    }


    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Send Subscription Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.users.subscription',
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

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Subscription Confirmation')
            ->view('emails.users.subscription')
            ->with([
                'courseId' => $this->courseId,
                'courseTitle' => $this->courseTitle,
                'courseUrl' => url("/api/get-course/{$this->courseId}")
            ]);
    }
}
