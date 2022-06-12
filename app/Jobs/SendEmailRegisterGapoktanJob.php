<?php

namespace App\Jobs;

use App\Mail\SendEmailRegisterGapoktan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmailRegisterGapoktanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $emailGapoktan,$datas;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($emailGapoktan, $datas)
    {
        $this->emailGapoktan = $emailGapoktan;
        $this->datas = $datas;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $emailGapoktan = $this->emailGapoktan;
        $datas = $this->datas;
        $email = new SendEmailRegisterGapoktan($datas);
        Mail::to($emailGapoktan)->send($email);
    }
}
