<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Business;
use App\Models\OperatorCollaborationAgreement;

class AgreementConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public $business;
    public $collab;

    public function __construct(Business $business, OperatorCollaborationAgreement $collab)
    {
        $this->business = $business;
        $this->collab = $collab;
    }

    public function build()
    {
        return $this->subject('HIO: Agreement confirmed for ' . $this->business->legal_name)
                    ->view('emails.agreement_confirmed')
                    ->with([
                        'business' => $this->business,
                        'collab' => $this->collab,
                    ]);
    }
}
