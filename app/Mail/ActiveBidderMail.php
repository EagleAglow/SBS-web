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
        $this->url = config('extra.login_url');
        $this->from_name = config('mail.from.name');
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
        $from_name = $this->from_name;
        return $this->subject('Active Bidder Mail')
            ->markdown('mailtemplates.activebidder')
            ->with([
                'name' => $name, 
                'url' =>  $url,
                'from_name' => $from_name,
            ]);
    }
}
