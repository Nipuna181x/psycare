<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Services\AppointmentPaymentService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('payments:reconcile-pending')]
#[Description('Verify expired Stripe Checkout Sessions and release unpaid appointment slots')]
class ReconcilePendingPayments extends Command
{
    public function handle(AppointmentPaymentService $payments): int
    {
        $succeeded = 0;
        $failed = 0;

        Payment::query()
            ->where('status', 'pending')
            ->where('expires_at', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($pendingPayments) use ($payments, &$succeeded, &$failed): void {
                foreach ($pendingPayments as $payment) {
                    try {
                        $result = $payments->reconcileExpired($payment);
                        $result === 'succeeded' ? $succeeded++ : $failed++;
                    } catch (Throwable $exception) {
                        report($exception);
                        $this->warn("Could not reconcile payment {$payment->id}; it remains pending.");
                    }
                }
            });

        $this->info("Reconciled payments: {$succeeded} succeeded, {$failed} released.");

        return self::SUCCESS;
    }
}
