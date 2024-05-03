<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GoogleMeetConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $course;
    public $googleMeetLink;
    public $pathToFile;


    /**
     * Create a new message instance.
     */
    public function __construct($course, $googleMeetLink, $pathToFile)
    {
        $this->course = $course;
        $this->googleMeetLink = $googleMeetLink;
        $this->pathToFile = $pathToFile;


    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Google Meet Confirmation',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.users.send-invitation',
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
        return $this->view('emails.users.send-invitation')
            ->subject('Invitation: ' . $this->course->name)
            ->with([
                'course' => $this->course,
                'googleMeetLink' => $this->googleMeetLink,
            ])
            ->attach($this->pathToFile, [
                'as' => 'invite.ics',
                'mime' => 'text/calendar',
            ]);
    }
}
