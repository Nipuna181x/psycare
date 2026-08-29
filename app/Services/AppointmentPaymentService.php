<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Throwable;
use UnexpectedValueException;

class AppointmentPaymentService
{
    public function __construct(
        private readonly StripeCheckoutGateway $stripe,
        private readonly PaymentCompletionNotifier $notifier,
    ) {}

    /** @return array{payment: Payment, checkout_url: string} */
    public function start(Appointment $appointment): array
    {
        try {
            $session = $this->stripe->createSession($appointment);
            $sessionId = $session['id'] ?? null;
            $checkoutUrl = $session['url'] ?? null;

            if (! is_string($sessionId) || ! is_string($checkoutUrl)) {
                throw new UnexpectedValueException('Stripe did not return a Checkout Session ID and URL.');
            }

            $payment = Payment::query()->create([
                'appointment_id' => $appointment->id,
                'doctor_id' => $appointment->doctor_id,
                'clinic_id' => $appointment->medical_center_id,
                'patient_id' => $appointment->user_id,
                'stripe_session_id' => $sessionId,
                'amount' => $this->appointmentTotal($appointment),
                'currency' => config('services.stripe.currency', 'lkr'),
                'doctor_amount' => $appointment->doctor_fee_charged,
                'clinic_amount' => $appointment->clinic_fee_charged,
                'status' => 'pending',
                'doctor_payout_status' => 'unpaid',
                'expires_at' => isset($session['expires_at'])
                    ? now()->setTimestamp((int) $session['expires_at'])
                    : now()->addMinutes(30),
            ]);

            return ['payment' => $payment, 'checkout_url' => $checkoutUrl];
        } catch (Throwable $exception) {
            $this->cancelAppointmentWithoutPayment($appointment);

            throw $exception;
        }
    }

    /**
     * Retrieve from Stripe and complete only when Stripe itself says the
     * Checkout Session is paid. The browser's query parameter is never trusted.
     */
    public function verifyAndComplete(Payment $payment): bool
    {
        return $this->completeFromStripeSession($payment, $this->stripe->retrieveSession($payment->stripe_session_id));
    }

    /** @param array<string, mixed> $session */
    public function completeFromStripeSession(Payment $payment, array $session): bool
    {
        $this->assertSessionBelongsToPayment($payment, $session);

        if (($session['payment_status'] ?? null) !== 'paid') {
            return false;
        }

        $processed = DB::transaction(function () use ($payment, $session): bool {
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($lockedPayment->status === 'succeeded') {
                return false;
            }

            if ($lockedPayment->status !== 'pending') {
                return false;
            }

            $appointment = Appointment::query()->with('availabilitySlot')->lockForUpdate()->findOrFail($lockedPayment->appointment_id);
            $paymentIntent = $session['payment_intent'] ?? null;
            $paymentMethod = is_array($paymentIntent) ? ($paymentIntent['payment_method'] ?? null) : null;

            $lockedPayment->update([
                'stripe_payment_intent_id' => is_array($paymentIntent) ? ($paymentIntent['id'] ?? null) : $paymentIntent,
                'status' => 'succeeded',
                'payment_method' => is_array($paymentMethod) ? ($paymentMethod['type'] ?? null) : null,
                'card_last_four' => is_array($paymentMethod) ? data_get($paymentMethod, 'card.last4') : null,
                'processed_at' => now(),
                'notifications_sent_at' => now(),
            ]);

            $appointment->update(['status' => 'confirmed']);
            $appointment->availabilitySlot?->update([
                'is_booked' => true,
                'appointment_id' => $appointment->id,
            ]);

            return true;
        }, attempts: 3);

        if ($processed) {
            $this->notifier->send($payment->fresh(['appointment.doctor', 'appointment.medicalCenter', 'appointment.user']));
        }

        return $processed;
    }

    public function cancel(Payment $payment): bool
    {
        return DB::transaction(function () use ($payment): bool {
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($lockedPayment->status !== 'pending') {
                return false;
            }

            $lockedPayment->update(['status' => 'failed']);
            $appointment = Appointment::query()->with('availabilitySlot')->lockForUpdate()->findOrFail($lockedPayment->appointment_id);
            $appointment->update(['status' => 'cancelled']);

            if ($appointment->availabilitySlot?->appointment_id === $appointment->id) {
                $appointment->availabilitySlot->update(['is_booked' => false, 'appointment_id' => null]);
            }

            return true;
        }, attempts: 3);
    }

    public function reconcileExpired(Payment $payment): string
    {
        $session = $this->stripe->retrieveSession($payment->stripe_session_id);

        if (($session['payment_status'] ?? null) === 'paid') {
            $this->completeFromStripeSession($payment, $session);

            return 'succeeded';
        }

        if (($session['status'] ?? null) === 'open') {
            $this->stripe->expireSession($payment->stripe_session_id);
        }

        $this->cancel($payment);

        return 'failed';
    }

    /** @param array<string, mixed> $session */
    private function assertSessionBelongsToPayment(Payment $payment, array $session): void
    {
        $expectedMinorAmount = (int) round(((float) $payment->amount) * 100);

        if (($session['id'] ?? null) !== $payment->stripe_session_id
            || (int) data_get($session, 'metadata.appointment_id') !== $payment->appointment_id
            || (int) ($session['amount_total'] ?? -1) !== $expectedMinorAmount
            || mb_strtolower((string) ($session['currency'] ?? '')) !== mb_strtolower($payment->currency)) {
            throw new UnexpectedValueException('Stripe Checkout Session does not match the payment ledger record.');
        }
    }

    private function appointmentTotal(Appointment $appointment): float
    {
        return (float) $appointment->doctor_fee_charged + (float) $appointment->clinic_fee_charged;
    }

    private function cancelAppointmentWithoutPayment(Appointment $appointment): void
    {
        DB::transaction(function () use ($appointment): void {
            $lockedAppointment = Appointment::query()->with('availabilitySlot')->lockForUpdate()->find($appointment->id);

            if (! $lockedAppointment || $lockedAppointment->status !== 'pending_payment') {
                return;
            }

            $lockedAppointment->update(['status' => 'cancelled']);

            if ($lockedAppointment->availabilitySlot?->appointment_id === $lockedAppointment->id) {
                $lockedAppointment->availabilitySlot->update(['is_booked' => false, 'appointment_id' => null]);
            }
        });
    }
}
