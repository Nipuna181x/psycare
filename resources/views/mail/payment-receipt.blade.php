<x-mail::message>
# Payment Receipt

Thank you, {{ $payment->appointment->patient_name }}. Your payment has been received and your appointment is confirmed.

**Transaction date:** {{ $payment->processed_at?->format('D, j M Y · g:i A') }}  
**Reference:** {{ $payment->stripe_payment_intent_id ?: $payment->stripe_session_id }}  
**Payment method:** {{ ucfirst($payment->payment_method ?? 'card') }}{{ $payment->card_last_four ? ' ending '.$payment->card_last_four : '' }}

<x-mail::table>
| Description | Amount |
| :-- | --: |
| Doctor session fee — Dr. {{ $payment->appointment->doctor->name }} | LKR {{ number_format((float) $payment->doctor_amount, 2) }} |
| {{ $payment->appointment->medicalCenter->name }} facility fee | LKR {{ number_format((float) $payment->clinic_amount, 2) }} |
| **Total paid** | **LKR {{ number_format((float) $payment->amount, 2) }}** |
</x-mail::table>

## Appointment details

**Doctor:** Dr. {{ $payment->appointment->doctor->name }}  
**Clinic:** {{ $payment->appointment->medicalCenter->name }}  
**Date:** {{ $payment->appointment->appointment_date->format('D, j M Y') }}  
**Time:** {{ \Illuminate\Support\Carbon::parse($payment->appointment->appointment_time)->format('g:i A') }}  
**Mode:** {{ str($payment->appointment->mode)->replace('_', ' ')->title() }}

<x-mail::button :url="route('appointments.index')">
View my appointments
</x-mail::button>

This receipt records a charge presented in LKR. Stripe may settle the platform account in its configured settlement currency, such as AUD.

Warmly,<br>
The {{ config('app.name') }} team
</x-mail::message>
