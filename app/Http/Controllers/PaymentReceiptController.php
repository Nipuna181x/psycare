<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PaymentReceiptPdf;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PaymentReceiptController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Payment $payment, PaymentReceiptPdf $receiptPdf): Response
    {
        abort_unless($payment->patient_id === Auth::id(), 403);
        abort_unless($payment->status === 'succeeded', 404);

        return $receiptPdf->download($payment);
    }
}
