<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $payment->reference() }} — Payment Receipt</title>
    <style>
        @page { margin: 30px; }
        body { margin: 0; color: #172554; font-family: DejaVu Sans, sans-serif; font-size: 11px; line-height: 1.55; }
        .sheet { border: 1px solid #bfdbfe; border-radius: 16px; overflow: hidden; }
        .header { padding: 28px 32px; background: #eff6ff; border-bottom: 1px solid #bfdbfe; }
        .brand { margin: 0; color: #1d4ed8; font-size: 13px; font-weight: bold; letter-spacing: 1.5px; text-transform: uppercase; }
        h1 { margin: 8px 0 0; color: #0f172a; font-size: 27px; font-weight: normal; }
        .payment-id { margin-top: 12px; color: #1e40af; font-size: 12px; font-weight: bold; }
        .body { padding: 28px 32px 32px; }
        .success { display: inline-block; padding: 5px 10px; border-radius: 14px; background: #dcfce7; color: #166534; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .meta { width: 100%; margin: 22px 0; border-collapse: collapse; }
        .meta td { width: 50%; padding: 8px 0; vertical-align: top; border-bottom: 1px solid #e2e8f0; }
        .label { display: block; margin-bottom: 2px; color: #64748b; font-size: 8px; font-weight: bold; letter-spacing: .7px; text-transform: uppercase; }
        .value { color: #0f172a; font-size: 11px; }
        .section-title { margin: 25px 0 8px; color: #0f172a; font-size: 13px; }
        .fees { width: 100%; border-collapse: collapse; }
        .fees th, .fees td { padding: 10px 12px; border: 1px solid #dbeafe; text-align: left; }
        .fees th { background: #eff6ff; color: #1e3a8a; font-size: 9px; text-transform: uppercase; }
        .fees .amount { text-align: right; }
        .fees .total td { background: #dbeafe; color: #0f172a; font-size: 12px; font-weight: bold; }
        .footer { margin-top: 28px; padding-top: 14px; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 9px; }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="header">
            <p class="brand">PsyCare</p>
            <h1>Payment Receipt</h1>
            <p class="payment-id">Payment ID: {{ $payment->reference() }}</p>
        </div>

        <div class="body">
            <span class="success">Payment succeeded</span>

            <table class="meta">
                <tr>
                    <td><span class="label">Patient</span><span class="value">{{ $payment->appointment->patient_name }}</span></td>
                    <td><span class="label">Transaction date</span><span class="value">{{ $payment->processed_at?->format('D, j M Y · g:i A') }}</span></td>
                </tr>
                <tr>
                    <td><span class="label">Doctor</span><span class="value">Dr. {{ $payment->appointment->doctor->name }}</span></td>
                    <td><span class="label">Clinic</span><span class="value">{{ $payment->appointment->medicalCenter->name }}</span></td>
                </tr>
                <tr>
                    <td><span class="label">Appointment</span><span class="value">{{ $payment->appointment->appointment_date->format('D, j M Y') }}, {{ \Illuminate\Support\Carbon::parse($payment->appointment->appointment_time)->format('g:i A') }}</span></td>
                    <td><span class="label">Payment method</span><span class="value">{{ ucfirst($payment->payment_method ?? 'Card') }}{{ $payment->card_last_four ? ' ending '.$payment->card_last_four : '' }}</span></td>
                </tr>
                <tr>
                    <td colspan="2"><span class="label">Stripe transaction reference</span><span class="value">{{ $payment->stripe_payment_intent_id ?: $payment->stripe_session_id }}</span></td>
                </tr>
            </table>

            <h2 class="section-title">Payment breakdown</h2>
            <table class="fees">
                <thead><tr><th>Description</th><th class="amount">Amount</th></tr></thead>
                <tbody>
                    <tr><td>Doctor session fee</td><td class="amount">LKR {{ number_format((float) $payment->doctor_amount, 2) }}</td></tr>
                    <tr><td>{{ $payment->appointment->medicalCenter->name }} facility fee</td><td class="amount">LKR {{ number_format((float) $payment->clinic_amount, 2) }}</td></tr>
                    <tr class="total"><td>Total paid</td><td class="amount">LKR {{ number_format((float) $payment->amount, 2) }}</td></tr>
                </tbody>
            </table>

            <p class="footer">This computer-generated receipt confirms a payment verified directly with Stripe. Keep the Payment ID above for support or payment enquiries.</p>
        </div>
    </div>
</body>
</html>
