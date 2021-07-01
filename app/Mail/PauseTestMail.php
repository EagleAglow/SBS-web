<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PauseTestMail extends Mailable
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
        $from_name = $this->from_name;
        $app_bid_phone = $this->app_bid_phone;
        return $this->subject('Bidding Pause Test Mail')
            ->markdown('mailtemplates.pausetest')
            ->with([
                'name' => $name,
                'from_name' => $from_name,
                'app_bid_phone' => $app_bid_phone,
            ]);
    }
}