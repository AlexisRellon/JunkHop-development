<?php

namespace App\Mail;

class CollectionConfirmationMail extends BaseMail
{
    public $subject;
    public $view;
    public $data;

    /**
     * Create a new message instance.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Collection $collection
     * @return void
     */
    public function __construct($user, $collection)
    {
        $this->subject = 'Your JunkHop Collection is Confirmed';
        $this->view = 'emails.collection-confirmation';
        $this->data = [
            'user' => $user,
            'collection' => $collection,
        ];
    }
}
