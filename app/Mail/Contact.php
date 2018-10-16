<?php

namespace App\Mail;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Arr;

class Contact extends Mailable implements ShouldQueue
{
    /**
     * @var array
     */
    private $data;

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function build()
    {
        $firstName = Arr::get($this->data, 'firstName');
        $lastName = Arr::get($this->data, 'lastName');
        $email = Arr::get($this->data, 'email');
        $comments = Arr::get($this->data, 'comments');

        $html = "
            <p><b>First Name: </b>{$firstName}</p></br>
            <p><b>Last Name: </b>{$lastName}</p></br>
            <p><b>Email: </b>{$email}</p></br>
            <p><b>Comments: </b>{$comments}</p></br>
        ";

        $this->subject("Contacting Full Heap Developer - {$firstName} {$lastName}")
            ->html($html)
            ->to('tommymay37@gmail.com');
    }
}
