<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Email extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

    public $mailData = [];

    public function __construct($mailData = [])
    {
        $this->mailData = $mailData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailData["subject"],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        //https://laravel.com/docs/11.x/mail#configuring-the-view
        // php artisan make:mail WelcomeEmail
        if($this->mailData["type"] == "registration") {
            // Vérifier si nous utilisons le nouveau système de code ou l'ancien système de token
            if(isset($this->mailData["verification_code"])) {
                return new Content(
                    view: 'emails.registration_code',
                    with: [
                        'subject' => $this->mailData["subject"],
                        'lastname' => $this->mailData["lastname"],
                        'firstname' => $this->mailData["firstname"],
                        'verification_code' => $this->mailData["verification_code"],
                        'url' => $this->mailData["url"]
                    ],
                );
            } else {
                return new Content(
                    view: 'emails.registration',
                    with: [
                        'subject' => $this->mailData["subject"],
                        'lastname' => $this->mailData["lastname"],
                        'firstname' => $this->mailData["firstname"],
                        'token' => $this->mailData["token"],
                        'url' => $this->mailData["url"]
                    ],
                );
            }
        } elseif($this->mailData["type"] == "forgotten_password") {
            // Vérifier si nous utilisons le nouveau système de code ou l'ancien système de token
            if(isset($this->mailData["reset_code"])) {
                return new Content(
                    view: 'emails.forgotten_password_code',
                    with: [
                        'subject' => $this->mailData["subject"],
                        'lastname' => $this->mailData["lastname"],
                        'firstname' => $this->mailData["firstname"],
                        'reset_code' => $this->mailData["reset_code"],
                        'url' => $this->mailData["url"]
                    ],
                );
            } else {
                return new Content(
                    view: 'emails.forgotten_password',
                    with: [
                        'subject' => $this->mailData["subject"],
                        'lastname' => $this->mailData["lastname"],
                        'firstname' => $this->mailData["firstname"],
                        'token' => $this->mailData["token"],
                        'url' => $this->mailData["url"]
                    ],
                );
            }
        } elseif($this->mailData["type"] == "password_recovery") {
            return new Content(
                view: 'emails.password_recovery',
                with: [
                    'subject' => $this->mailData["subject"],
                    'lastname' => $this->mailData["lastname"],
                    'firstname' => $this->mailData["firstname"],
                    'url' => $this->mailData["url"]
                ],
            );
        }
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