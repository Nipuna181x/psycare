<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class PdfFontRegistrar
{
    /**
     * Register Noto Sans Sinhala with dompdf so PDFs can render Sinhala-language report
     * content. dompdf's bundled DejaVu Sans has no Sinhala glyphs and silently renders
     * unsupported characters as tofu boxes, which is otherwise invisible until a Sinhala
     * report is downloaded. Registration is cached by dompdf itself, so this is a cheap
     * no-op after the first call.
     */
    public static function ensureSinhalaFontIsRegistered(): void
    {
        $fontMetrics = Pdf::getDomPDF()->getFontMetrics();

        if ($fontMetrics->getFont('Noto Sans Sinhala') !== null) {
            return;
        }

        $fontMetrics->registerFont(
            ['family' => 'Noto Sans Sinhala', 'style' => 'normal', 'weight' => 'normal'],
            storage_path('fonts/NotoSansSinhala-Regular.ttf'),
        );

        $fontMetrics->registerFont(
            ['family' => 'Noto Sans Sinhala', 'style' => 'normal', 'weight' => 'bold'],
            storage_path('fonts/NotoSansSinhala-Bold.ttf'),
        );
    }
}
