<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Payment;
use App\Services\AppointmentPaymentService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use UnexpectedValueException;

/**
 * PsyCare charges through one Stripe account. Clinic ownership and doctor
 * payouts are internal ledger records only; no Stripe Connect or transfer API
 * is used. This intentionally has no webhook endpoint: payment truth comes
 * solely from server-side Checkout retrieval on return and reconciliation.
 */
class PaymentCheckoutController extends Controller
{
    public function success(Request $request, AppointmentPaymentService $payments): View|RedirectResponse
    {
        $validated = $request->validate([
            'session_id' => ['required', 'string', 'max:255', 'starts_with:cs_'],
        ]);

        $payment = Payment::query()
            ->with('appointment')
            ->where('stripe_session_id', $validated['session_id'])
            ->firstOrFail();

        abort_unless($payment->patient_id === Auth::id(), 403);

        try {
            $payments->verifyAndComplete($payment);
        } catch (UnexpectedValueException $exception) {
            report($exception);
            abort(422, 'The Stripe session did not match this appointment.');
        } catch (ConnectionException|RequestException $exception) {
            report($exception);

            return view('booking.payment-status', [
                'appointment' => $payment->appointment,
                'state' => 'unavailable',
            ]);
        }

        if ($payment->fresh()->status === 'succeeded') {
            return redirect()->route('booking.confirmed', $payment->appointment);
        }

        return view('booking.payment-status', [
            'appointment' => $payment->appointment,
            'state' => 'pending',
        ]);
    }

    public function cancel(Appointment $appointment, AppointmentPaymentService $payments): View
    {
        abort_unless($appointment->user_id === Auth::id(), 403);

        $payment = $appointment->payment()->firstOrFail();
        $payments->cancel($payment);

        return view('booking.payment-status', [
            'appointment' => $appointment,
            'state' => 'cancelled',
        ]);
    }
}
