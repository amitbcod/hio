<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Business;
use App\Models\Operator;

class BusinessApproved extends Mailable
{
    use Queueable, SerializesModels;

    public $business;
    public $operator;

    public function __construct(Business $business, Operator $operator)
    {
        $this->business = $business;
        $this->operator = $operator;
    }

    public function build()
    {
        return $this->subject('Your business is now active on HolidaysIO')
                    ->view('emails.business_approved')
                    ->with([
                        'business' => $this->business,
                        'operator' => $this->operator,
                    ]);
    }
}
