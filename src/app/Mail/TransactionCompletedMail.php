<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Transaction;
use App\Models\User;

class TransactionCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Transaction $transaction;
    public User $buyer;

    public function __construct(Transaction $transaction, User $buyer)
    {
        $this->transaction = $transaction;
        $this->buyer = $buyer;
    }

    public function build()
    {
        return $this->subject('取引が完了しました')
            ->view('emails.transaction_completed');
    }
}
