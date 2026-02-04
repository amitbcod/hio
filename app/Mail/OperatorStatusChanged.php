<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Operator;

class OperatorStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public $operator;
    public $oldStatus;
    public $newStatus;

    public function __construct(Operator $operator, $oldStatus, $newStatus)
    {
        $this->operator = $operator;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function build()
    {
        return $this->subject('Your account status has changed')
                    ->view('emails.operator.status_changed')
                    ->with([
                        'operator' => $this->operator,
                        'oldStatus' => $this->oldStatus,
                        'newStatus' => $this->newStatus,
                    ]);
    }
}
