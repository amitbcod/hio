<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminApprovalRequested extends Mailable
{
    use Queueable, SerializesModels;

    public $operator;
    public $statusReview;

    public function __construct($operator, $statusReview = null)
    {
        $this->operator = $operator;
        $this->statusReview = $statusReview;
    }

    public function build()
    {
        return $this->subject('HIO Admin: Approval requested for new operator')
                    ->view('emails.admin_approval_requested')
                    ->with([
                        'operator' => $this->operator,
                        'statusReview' => $this->statusReview,
                    ]);
    }
}
