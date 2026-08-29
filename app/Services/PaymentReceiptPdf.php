<?php

namespace App\Services;

use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class PaymentReceiptPdf
{
    /**
     * Render a succeeded patient payment as a downloadable receipt.
     */
    public function download(Payment $payment): Response
    {
        $payment->loadMissing(['appointment.doctor', 'appointment.medicalCenter']);
        PdfFontRegistrar::ensureSinhalaFontIsRegistered();

        return Pdf::loadView('payments.receipt-pdf', [
            'payment' => $payment,
        ])->download(mb_strtolower($payment->reference()).'-receipt.pdf');
    }
}
