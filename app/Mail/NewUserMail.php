<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewUserMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $email, $token)
    {
        $this->name = $name;
        $this->email = $email;
        $this->url = config('url');
        $this->from_name = config('mail.from.name');
        $this->token = $token;
}

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $name = $this->name;
//        $url = $this->url;

//        $url = url(route('password.reset', [ 'token' => $this->token,

        $from_name = $this->from_name;
        $email = $this->email;
        $token = $this->token;
        $url= url(config('url').route('password.reset', ['email' => $email, $token ]));

        return $this->subject('New User Mail')
            ->markdown('mailtemplates.newuser')
            ->with([
                'name' => $name, 
                'url' =>  $url,
                'from_name' => $from_name,
            ]);
    }
}
