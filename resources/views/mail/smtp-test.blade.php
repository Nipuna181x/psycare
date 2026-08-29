<x-mail::message>
# SMTP test successful

PsyCare successfully connected to the configured mail server and delivered this test message.

**Requested by:** {{ $requestedBy }}  
**Sent at:** {{ now()->format('D, M j, Y · g:i A T') }}  
**Application:** {{ config('app.name') }}

No action is required. Transactional patient and doctor emails will use this same configured mailer.

Regards,<br>
{{ config('app.name') }} mail diagnostics
</x-mail::message>
