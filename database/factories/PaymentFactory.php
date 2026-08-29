<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'doctor_id' => fn (array $attributes) => Appointment::find($attributes['appointment_id'])?->doctor_id,
            'clinic_id' => fn (array $attributes) => Appointment::find($attributes['appointment_id'])?->medical_center_id,
            'patient_id' => fn (array $attributes) => Appointment::find($attributes['appointment_id'])?->user_id,
            'stripe_session_id' => 'cs_test_'.fake()->unique()->regexify('[A-Za-z0-9]{32}'),
            'stripe_payment_intent_id' => null,
            'amount' => fn (array $attributes) => (float) Appointment::find($attributes['appointment_id'])?->doctor_fee_charged + (float) Appointment::find($attributes['appointment_id'])?->clinic_fee_charged,
            'currency' => 'lkr',
            'doctor_amount' => fn (array $attributes) => Appointment::find($attributes['appointment_id'])?->doctor_fee_charged,
            'clinic_amount' => fn (array $attributes) => Appointment::find($attributes['appointment_id'])?->clinic_fee_charged,
            'status' => 'pending',
            'doctor_payout_status' => 'unpaid',
            'expires_at' => now()->addMinutes(30),
        ];
    }

    public function succeeded(): static
    {
        return $this->state(fn (): array => [
            'status' => 'succeeded',
            'stripe_payment_intent_id' => 'pi_test_'.fake()->unique()->regexify('[A-Za-z0-9]{24}'),
            'payment_method' => 'card',
            'card_last_four' => '4242',
            'processed_at' => now(),
            'notifications_sent_at' => now(),
        ]);
    }

    public function paidToDoctor(): static
    {
        return $this->succeeded()->state(fn (): array => [
            'doctor_payout_status' => 'paid',
            'doctor_paid_at' => now(),
        ]);
    }
}
