<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CourseAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $courseTitle;
    public $firstName;

    /**
     * Create a new message instance.
     */
    public function __construct($courseTitle, $firstName)
    {
        $this->courseTitle = $courseTitle;
        $this->firstName = $firstName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Course Assigned Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.users.course_assigned',
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
        return $this->subject('Course Assigned')
            ->view('emails.users.course_assigned')
            ->with([
                'courseTitle' => $this->courseTitle,
                'userName' => $this->firstName,
            ]);
    }
}
