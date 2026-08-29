<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MoodEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MoodTrackerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('mood-tracker.index'))->assertRedirect(route('login'));
    }

    public function test_patient_sees_a_friendly_empty_state(): void
    {
        $patient = User::factory()->create();

        $this->actingAs($patient)
            ->get(route('mood-tracker.index'))
            ->assertOk()
            ->assertSee('How are you feeling today?')
            ->assertSee('Mood history')
            ->assertSee('Your first check-in can start right here.')
            ->assertSee("Save today's entry");
    }

    public function test_patient_can_save_todays_mood_entry(): void
    {
        $patient = User::factory()->create();

        $this->actingAs($patient)
            ->post(route('mood-tracker.store'), [
                'mood_score' => 4,
                'mood_tags' => ['calm', 'hopeful'],
                'sleep_hours' => 7.5,
                'note' => 'I felt supported today.',
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Thanks for checking in today. Your entry has been saved.');

        $entry = MoodEntry::query()->sole();
        $this->assertTrue($entry->patient->is($patient));
        $this->assertSame(4, $entry->mood_score);
        $this->assertSame(['calm', 'hopeful'], $entry->mood_tags);
        $this->assertTrue($entry->entry_date->isToday());
    }

    public function test_resaving_todays_entry_updates_in_place(): void
    {
        $patient = User::factory()->create();
        $entry = MoodEntry::factory()->for($patient, 'patient')->create([
            'entry_date' => today(), 'mood_score' => 2, 'mood_tags' => ['tired'],
        ]);

        $this->actingAs($patient)->post(route('mood-tracker.store'), [
            'mood_score' => 5,
            'mood_tags' => ['happy', 'energetic'],
            'sleep_hours' => 8,
            'note' => 'A much better day.',
        ])->assertRedirect();

        $this->assertSame(1, $patient->moodEntries()->count());
        $entry->refresh();
        $this->assertSame(5, $entry->mood_score);
        $this->assertSame(['happy', 'energetic'], $entry->mood_tags);
    }

    public function test_mood_entry_validation_rejects_invalid_values(): void
    {
        $patient = User::factory()->create();

        $this->actingAs($patient)->post(route('mood-tracker.store'), [
            'mood_score' => 6,
            'mood_tags' => ['not-a-supported-tag'],
            'sleep_hours' => 14,
        ])->assertSessionHasErrors(['mood_score', 'mood_tags.0', 'sleep_hours']);

        $this->assertSame(0, MoodEntry::query()->count());
    }

    public function test_patient_history_and_chart_use_the_last_thirty_days_in_date_order(): void
    {
        $patient = User::factory()->create();
        MoodEntry::factory()->for($patient, 'patient')->create(['entry_date' => today()->subDays(5), 'mood_score' => 2, 'note' => 'Older entry']);
        MoodEntry::factory()->for($patient, 'patient')->create(['entry_date' => today()->subDays(2), 'mood_score' => 4, 'note' => 'Recent entry']);
        MoodEntry::factory()->for($patient, 'patient')->create(['entry_date' => today()->subDays(40), 'mood_score' => 1, 'note' => 'Outside chart']);

        $response = $this->actingAs($patient)->get(route('mood-tracker.index'));

        $response->assertOk()->assertSee('Older entry')->assertSee('Recent entry')->assertSee('Outside chart');
        $this->assertSame([2, 4], $response->viewData('moodChartData')['scores']);
    }

    public function test_doctor_patient_profile_shows_mood_empty_state(): void
    {
        $appointment = Appointment::factory()->create();

        $this->actingAs($appointment->doctor, 'doctor')
            ->get(route('doctor.patients.show', $appointment->user))
            ->assertOk()
            ->assertSee("This patient hasn't logged any mood entries yet.", false);
    }

    public function test_treating_doctor_can_view_read_only_mood_history_and_chart(): void
    {
        $appointment = Appointment::factory()->create();
        MoodEntry::factory()->for($appointment->user, 'patient')->create([
            'entry_date' => today()->subDay(), 'mood_score' => 3,
            'mood_tags' => ['anxious', 'tired'], 'sleep_hours' => 6.5,
            'note' => 'A difficult evening.',
        ]);

        $response = $this->actingAs($appointment->doctor, 'doctor')
            ->get(route('doctor.patients.show', $appointment->user));

        $response->assertOk()
            ->assertSee('Mood Tracker')
            ->assertSee('A difficult evening.')
            ->assertSee('Anxious')
            ->assertDontSee(route('mood-tracker.store'));
        $this->assertSame([3], $response->viewData('moodChartData')['scores']);
    }

    public function test_unrelated_doctor_cannot_view_patient_mood_data(): void
    {
        $patient = User::factory()->create();
        MoodEntry::factory()->for($patient, 'patient')->create(['note' => 'Private mood note']);

        $this->actingAs(Doctor::factory()->create(), 'doctor')
            ->get(route('doctor.patients.show', $patient))
            ->assertForbidden();
    }
}
