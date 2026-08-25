<style>
    @page {
        margin: 26mm 18mm 22mm 18mm;
    }

    body {
        font-family: 'Noto Sans Sinhala', 'Helvetica', sans-serif;
        font-size: 10.5pt;
        line-height: 1.55;
        color: #1e2430;
    }

    h1, h2, h3 { font-family: 'Noto Sans Sinhala', 'Helvetica', sans-serif; }

    /* Letterhead */
    .letterhead {
        border-bottom: 2.5pt solid #14343a;
        padding-bottom: 10pt;
        margin-bottom: 16pt;
    }
    .letterhead-top { width: 100%; }
    .letterhead-top td { vertical-align: top; border: none; padding: 0; }
    .clinic-name { font-size: 13pt; font-weight: bold; color: #14343a; letter-spacing: 0.3pt; }
    .doc-title { font-size: 9pt; color: #6b7684; text-transform: uppercase; letter-spacing: 1.2pt; margin-top: 2pt; }
    .letterhead-meta { text-align: right; font-size: 9pt; color: #6b7684; line-height: 1.5; }
    .letterhead-meta strong { color: #1e2430; }

    /* Patient identity strip */
    .patient-strip {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 14pt;
        background: #f4f6f5;
        border-radius: 3pt;
    }
    .patient-strip td {
        border: none;
        padding: 8pt 12pt;
        font-size: 9.5pt;
    }
    .patient-strip .label { color: #6b7684; font-size: 8pt; text-transform: uppercase; letter-spacing: 0.6pt; display: block; margin-bottom: 1pt; }
    .patient-strip .value { color: #1e2430; font-weight: bold; }

    /* Risk banner */
    .risk-banner {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 16pt;
        border-radius: 3pt;
        overflow: hidden;
    }
    .risk-banner td { border: none; padding: 10pt 14pt; vertical-align: middle; }
    .risk-banner .risk-label { font-size: 8pt; text-transform: uppercase; letter-spacing: 0.8pt; opacity: 0.85; }
    .risk-banner .risk-value { font-size: 13pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.4pt; }
    .risk-banner .risk-note { font-size: 9pt; text-align: right; }

    /* Sections */
    .section { margin-bottom: 15pt; page-break-inside: avoid; }
    .section-title {
        font-size: 10.5pt;
        font-weight: bold;
        color: #14343a;
        text-transform: uppercase;
        letter-spacing: 0.6pt;
        border-bottom: 0.75pt solid #d8dde1;
        padding-bottom: 4pt;
        margin-bottom: 8pt;
    }
    .section p { margin: 0 0 6pt; }

    .finding {
        margin-bottom: 8pt;
        padding-left: 10pt;
        border-left: 2pt solid #d8dde1;
    }
    .finding .finding-label { font-weight: bold; font-size: 10pt; color: #1e2430; }
    .finding .finding-evidence { font-size: 9.5pt; color: #4a5361; margin-top: 1pt; }
    .finding .confidence-high { color: #14343a; }
    .finding .confidence-medium { color: #7a6a2a; }
    .finding .confidence-low { color: #8a8f96; }

    .empty-note { font-size: 9.5pt; color: #8a8f96; font-style: italic; }

    ul.plain-list { margin: 0; padding-left: 14pt; }
    ul.plain-list li { margin-bottom: 5pt; font-size: 9.5pt; }

    /* Two-column layout for findings grid */
    .findings-grid { width: 100%; border-collapse: collapse; }
    .findings-grid td { border: none; vertical-align: top; width: 50%; padding: 0; }
    .findings-grid td.left { padding-right: 10pt; }
    .findings-grid td.right { padding-left: 10pt; }

    /* Alerts */
    .alert-block {
        border: 1pt solid #d9463f;
        background: #fdf0ef;
        border-radius: 3pt;
        padding: 10pt 12pt;
        margin-bottom: 14pt;
    }
    .alert-block .alert-title { font-weight: bold; color: #9c231d; font-size: 10pt; margin-bottom: 4pt; }
    .alert-block ul { margin: 4pt 0 0; padding-left: 14pt; }
    .alert-block li { font-size: 9.5pt; color: #6e2521; }

    .disclaimer-block {
        font-size: 8.5pt;
        color: #6b7684;
        border-top: 0.75pt solid #d8dde1;
        padding-top: 8pt;
        margin-top: 18pt;
    }

    table.screening-table { width: 100%; border-collapse: collapse; }
    table.screening-table th, table.screening-table td {
        border: 0.75pt solid #d8dde1;
        padding: 6pt 9pt;
        text-align: left;
        font-size: 9.5pt;
    }
    table.screening-table th { background: #f4f6f5; color: #6b7684; font-weight: bold; text-transform: uppercase; font-size: 8pt; letter-spacing: 0.4pt; }

    /* Day-by-day full report */
    .day-block { margin-bottom: 22pt; page-break-inside: avoid; }
    .day-block-header {
        width: 100%;
        border-collapse: collapse;
        background: #14343a;
        color: #fff;
        border-radius: 3pt 3pt 0 0;
    }
    .day-block-header td { border: none; padding: 8pt 12pt; }
    .day-block-header .day-date { font-size: 10.5pt; font-weight: bold; }
    .day-block-header .day-time { font-size: 8.5pt; opacity: 0.75; }
    .day-block-header .day-risk { text-align: right; font-size: 9pt; font-weight: bold; text-transform: uppercase; }
    .day-block-body { border: 0.75pt solid #d8dde1; border-top: none; padding: 12pt 14pt; border-radius: 0 0 3pt 3pt; }

    .progression-table { width: 100%; border-collapse: collapse; margin-bottom: 16pt; }
    .progression-table th, .progression-table td { border: 0.75pt solid #d8dde1; padding: 6pt 8pt; font-size: 9pt; text-align: center; }
    .progression-table th { background: #f4f6f5; color: #6b7684; text-transform: uppercase; font-size: 7.5pt; letter-spacing: 0.4pt; }
    .progression-table td.risk-cell { font-weight: bold; text-transform: uppercase; }
</style>
