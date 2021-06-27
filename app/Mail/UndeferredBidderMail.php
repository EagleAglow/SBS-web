<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UndeferredBidderMail extends Mailable
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
        $this->app_bid_phone = config('extra.app_bid_phone');
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
        $app_bid_phone = $this->app_bid_phone;
        return $this->subject('Un-deferred Bidder Mail')
            ->markdown('mailtemplates.undeferredbidder')
            ->with([
                'name' => $name, 
                'url' =>  $url,
                'from_name' => $from_name,
                'app_bid_phone' => $app_bid_phone,
            ]);
    }
}
