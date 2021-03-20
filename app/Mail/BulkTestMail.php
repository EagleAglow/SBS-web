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
        $this->url = config('extra.login_url');
        $this->bulkmailmsg = $bulkmailmsg;
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
        $bulkmailmsg = $this->bulkmailmsg;
        $from_name = $this->from_name;
        return $this->subject(config('mail.from.name') . ' Test Mail')   
            ->markdown('mailtemplates.bulktestmail')
            ->with([
                'name' => $name,
                'url' =>  $url,
                'bulkmailmsg' => $bulkmailmsg,
                'from_name' => $from_name,
            ]);
    }
}