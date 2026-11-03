<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasPaymentCompleteScope
{
    public function scopePaymentComplete(Builder $query): Builder
    {
        $paidTransaction = static fn (Builder $paymentQuery) => $paymentQuery->paymentComplete();

        return $query->whereHas('bookingRef', static function (Builder $referenceQuery) use ($paidTransaction): void {
            $referenceQuery->where(static function (Builder $query) use ($paidTransaction): void {
                $query->whereHas('paymentTransactions', $paidTransaction)
                    ->orWhereHas('paymentTransaction', $paidTransaction);
            });
        });
    }

    public function scopeCustomerVisible(Builder $query): Builder
    {
        return $query->where(function (Builder $visibleQuery): void {
            $visibleQuery->paymentComplete()
                ->orWhere(function (Builder $codQuery): void {
                    $codQuery->whereRaw('LOWER(payment_method) = ?', ['cod'])
                        ->whereHas('bookingRef', static function (Builder $referenceQuery): void {
                            $referenceQuery->whereDoesntHave('paymentTransactions')
                                ->whereDoesntHave('paymentTransaction');
                        });
                });
        });
    }
}
