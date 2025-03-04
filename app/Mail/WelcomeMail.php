<?php

namespace App\Mail;

class WelcomeMail extends BaseMail
{
    public $subject;
    public $view;
    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->subject = 'Welcome to JunkHop';
        $this->view = 'emails.welcome';
        $this->data = [
            'user' => $user,
        ];
    }
}
