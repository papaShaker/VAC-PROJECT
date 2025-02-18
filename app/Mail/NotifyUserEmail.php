<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotifyUserEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $days_string;
    public $link;
    public $confirmation_message;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $days_string, $link, $confirmation_message)
    {
        $this->user = $user;
        $this->days_string = $days_string;
        $this->link = $link;
        $this->confirmation_message = $confirmation_message;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'SGVH-Homerti | Nueva solicitud',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.notifyUser', //Point to the Blade template
            with: [
                'user' => $this->user,
                'days_string' => $this->days_string,
                'link' => $this->link,
                'confirmation_message' => $this->confirmation_message
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
