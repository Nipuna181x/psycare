<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Notifications\AppointmentReminder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('appointments:send-reminders')]
#[Description('Send 24h and 1h reminder emails for upcoming confirmed appointments')]
class SendAppointmentReminders extends Command
{
    private const TOLERANCE_MINUTES = 7;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sent24h = $this->sendWindow(hoursAhead: 24, column: 'reminder_24h_sent_at', window: '24h');
        $sent1h = $this->sendWindow(hoursAhead: 1, column: 'reminder_1h_sent_at', window: '1h');

        $this->components->info("Sent {$sent24h} 24h reminder(s) and {$sent1h} 1h reminder(s).");

        return self::SUCCESS;
    }

    private function sendWindow(int $hoursAhead, string $column, string $window): int
    {
        $target = now()->addHours($hoursAhead);
        $windowStart = (clone $target)->subMinutes(self::TOLERANCE_MINUTES);
        $windowEnd = (clone $target)->addMinutes(self::TOLERANCE_MINUTES);

        $sentCount = 0;

        Appointment::query()
            ->where('status', 'confirmed')
            ->whereNull($column)
            ->with(['user', 'doctor', 'medicalCenter'])
            ->get()
            ->filter(fn (Appointment $appointment) => $appointment->startsAt()->betweenIncluded($windowStart, $windowEnd))
            ->each(function (Appointment $appointment) use ($column, $window, &$sentCount) {
                $claimed = Appointment::where('id', $appointment->id)
                    ->whereNull($column)
                    ->update([$column => now()]);

                if ($claimed) {
                    $appointment->user?->notify((new AppointmentReminder($appointment, $window))->afterCommit());
                    $sentCount++;
                }
            });

        return $sentCount;
    }
}
