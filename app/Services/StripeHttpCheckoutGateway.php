<?php

namespace App\Services;

use App\Models\Appointment;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use LogicException;

class StripeHttpCheckoutGateway implements StripeCheckoutGateway
{
    private const API_BASE = 'https://api.stripe.com/v1';

    /** @return array<string, mixed> */
    public function createSession(Appointment $appointment): array
    {
        $appointment->loadMissing(['doctor', 'medicalCenter', 'user']);
        $currency = config('services.stripe.currency', 'lkr');

        return $this->request()->post(self::API_BASE.'/checkout/sessions', [
            'mode' => 'payment',
            'success_url' => route('booking.payment.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('booking.payment.cancel', $appointment),
            'client_reference_id' => (string) $appointment->id,
            'customer_email' => $appointment->patient_email ?: $appointment->user->email,
            'expires_at' => now()->addMinutes(30)->timestamp,
            'metadata' => ['appointment_id' => (string) $appointment->id],
            'payment_intent_data' => [
                'metadata' => ['appointment_id' => (string) $appointment->id],
            ],
            'line_items' => [
                [
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => $currency,
                        'unit_amount' => $this->minorUnits($appointment->doctor_fee_charged),
                        'product_data' => ['name' => 'Doctor session fee'],
                    ],
                ],
                [
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => $currency,
                        'unit_amount' => $this->minorUnits($appointment->clinic_fee_charged),
                        'product_data' => ['name' => $appointment->medicalCenter->name.' facility fee'],
                    ],
                ],
            ],
        ])->throw()->json();
    }

    /** @return array<string, mixed> */
    public function retrieveSession(string $sessionId): array
    {
        return $this->request()->get(self::API_BASE.'/checkout/sessions/'.urlencode($sessionId), [
            'expand' => ['payment_intent.payment_method'],
        ])->throw()->json();
    }

    /** @return array<string, mixed> */
    public function expireSession(string $sessionId): array
    {
        return $this->request()->post(self::API_BASE.'/checkout/sessions/'.urlencode($sessionId).'/expire')
            ->throw()
            ->json();
    }

    private function request(): PendingRequest
    {
        $secret = config('services.stripe.secret');

        if (! is_string($secret) || $secret === '') {
            throw new LogicException('Stripe is not configured. Set STRIPE_SECRET.');
        }

        return Http::withBasicAuth($secret, '')
            ->asForm()
            ->acceptJson()
            ->timeout((int) config('services.stripe.timeout', 15))
            ->retry(2, 200);
    }

    private function minorUnits(string|float|int|null $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }
}
