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

    public $isCourse;
    public $title;
    public $entityId;


    /**
     * Create a new message instance.
     *
     * @param $isCourse
     * @param $title
     */
    public function __construct($isCourse, $title, $entityId)
    {
        $this->isCourse = $isCourse;
        $this->title = $title;
        $this->entityId = $entityId;
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
        $url = $this->isCourse ? url("/api/get-course/{$this->entityId}") : url("/api/get-learning-path/{$this->entityId}");

        return $this->subject('Subscription Confirmation')
            ->view('emails.users.subscription')
            ->with([
                'isCourse' => $this->isCourse,
                'title' => $this->title,
                'url' => $url,
            ]);
    }
}
