<?php

namespace App\Notifications;

use App\Models\FinancialSettlement;
use App\Models\FinancialSettlementTransfer;
use Illuminate\Notifications\Notification;

class FinancialSettlementTransferNotification extends Notification
{
    public function __construct(
        private readonly FinancialSettlement $settlement,
        private readonly FinancialSettlementTransfer $transfer,
        private readonly string $action,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $initiated = $this->action === 'initiated';
        $recipientUrl = $this->settlement->settlement_type === 'hio_sla'
            ? route('admin.accounting-settlements.show', $this->settlement)
            : route('operator.payouts.show', $this->settlement);

        return [
            'type' => 'financial_settlement_transfer_'.$this->action,
            'title' => $initiated ? 'Payment transfer initiated' : 'Payment receipt confirmed',
            'message' => $initiated
                ? "Transfer {$this->transfer->transfer_reference} for settlement {$this->settlement->reference} is awaiting receipt confirmation."
                : "Transfer {$this->transfer->transfer_reference} for settlement {$this->settlement->reference} was verified by the beneficiary.",
            'financial_settlement_id' => $this->settlement->id,
            'transfer_id' => $this->transfer->id,
            'settlement_reference' => $this->settlement->reference,
            'transfer_reference' => $this->transfer->transfer_reference,
            'amount' => $this->transfer->amount,
            'currency' => $this->transfer->currency,
            'url' => $initiated ? $recipientUrl : route('mpo.accounting-settlements.show', $this->settlement),
        ];
    }
}
