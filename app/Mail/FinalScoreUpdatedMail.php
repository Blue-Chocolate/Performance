<?php

namespace App\Mail;

use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FinalScoreUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $organization;

    public function __construct(Organization $organization)
    {
        $this->organization = $organization;
    }

    public function build()
    {
        return $this->subject('Your Final Evaluation Score')
            ->view('emails.final_score_updated');
    }
}
