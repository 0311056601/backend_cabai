<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;

class SendEmailRegisterGapoktan extends Mailable
{
    use Queueable, SerializesModels;
    protected $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data =$data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // return $this->view('view.name');
        return $this->from('noreply@aplikasicabai.co.id')->view('send_email.pendaftaran_gapoktan',[
            'data' => $this->data,
            'encrypt' => Crypt::encryptString($this->data->email),
        ])
        ->subject('Pendaftaran - Pendaftaran Memerlukan Konfirmasi');
    }
}
