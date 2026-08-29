<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SendSmtpTestRequest;
use App\Mail\SmtpTestMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class MailCheckController extends Controller
{
    public function index(): View
    {
        $smtp = config('mail.mailers.smtp');

        return view('admin.mail-check.index', [
            'mailer' => config('mail.default'),
            'host' => $smtp['host'] ?? null,
            'port' => $smtp['port'] ?? null,
            'security' => $this->securityLabel($smtp),
            'fromAddress' => config('mail.from.address'),
            'authenticationConfigured' => filled($smtp['username'] ?? null) && filled($smtp['password'] ?? null),
            'smtpConfigured' => config('mail.default') === 'smtp'
                && filled($smtp['host'] ?? null)
                && filter_var(config('mail.from.address'), FILTER_VALIDATE_EMAIL),
        ]);
    }

    public function send(SendSmtpTestRequest $request): RedirectResponse
    {
        try {
            Mail::mailer('smtp')->to($request->validated('email'))->send(new SmtpTestMail(
                requestedBy: $request->user('admin')->name,
            ));
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->with('smtp_error', 'SMTP delivery failed. Check the mail host, port, credentials, sender address, and application log.');
        }

        return back()->with('smtp_success', 'Test email delivered successfully to '.$request->validated('email').'.');
    }

    /** @param array<string, mixed> $smtp */
    private function securityLabel(array $smtp): string
    {
        if (($smtp['scheme'] ?? null) === 'smtps' || (int) ($smtp['port'] ?? 0) === 465) {
            return 'SMTPS';
        }

        return (int) ($smtp['port'] ?? 0) === 587 ? 'STARTTLS (automatic)' : 'SMTP / automatic TLS';
    }
}
