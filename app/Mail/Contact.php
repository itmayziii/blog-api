<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class Contact extends Mailable
{
    public function build()
    {
        $this->from('tommymay37@gmail.com')
            ->html('<h1>I think you will enjoy looking at this Gif Tommy</h1>');
    }
}
