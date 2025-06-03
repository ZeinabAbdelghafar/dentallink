<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $subject;
    public $text;
    public $url;

    public function __construct($user, $subject, $text, $url)
    {
        $this->user = $user;
        $this->subject = $subject;
        $this->text = $text;
        $this->url = $url;
    }

    public function build()
    {
        return $this->subject($this->subject)
            ->markdown('emails.verification', [
                'user' => $this->user,
                'text' => $this->text,
                'url' => $this->url,
            ]);
    }
}