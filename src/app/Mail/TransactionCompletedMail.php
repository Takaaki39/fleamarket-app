<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Progress;
use App\Models\User;

class TransactionCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Progress $progress;
    public User $buyer;

    public function __construct(Progress $progress, User $buyer)
    {
        $this->progress = $progress;
        $this->buyer = $buyer;
    }

    public function build()
    {
        return $this->subject('取引が完了しました')
            ->view('emails.transaction_completed');
    }
}
