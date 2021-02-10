<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BulkTestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $bulkmailmsg)
    {
        $this->name = $name;
//      $this->url = $_SERVER['SERVER_ADDR'];
        $this->url = 'https://Bid.453amb.ca/login';
        $this->bulkmailmsg = $bulkmailmsg;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $name = $this->name;
        $url = $this->url;
        $bulkmailmsg = $this->bulkmailmsg;
        return $this->subject('Schedule Bid System Test Mail')
            ->markdown('mailtemplates.bulktestmail')
            ->with([
                'name' => $name,
                'url' =>  $url,
                'bulkmailmsg' => $bulkmailmsg,
            ]);
    }
}