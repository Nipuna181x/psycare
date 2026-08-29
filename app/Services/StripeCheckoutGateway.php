<?php

namespace App\Services;

use App\Models\Appointment;

interface StripeCheckoutGateway
{
    /** @return array<string, mixed> */
    public function createSession(Appointment $appointment): array;

    /** @return array<string, mixed> */
    public function retrieveSession(string $sessionId): array;

    /** @return array<string, mixed> */
    public function expireSession(string $sessionId): array;
}
