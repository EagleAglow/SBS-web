<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ActiveBidderMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name)
    {
        $this->name = $name;
//      $this->url = $_SERVER['SERVER_ADDR'];
        $this->url = 'https://Bid.453amb.ca/login';
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
        return $this->subject('Active Bidder Mail')
            ->markdown('mailtemplates.activebidder')
            ->with([
                'name' => $name, 
                'url' =>  $url,
            ]);
    }
}
