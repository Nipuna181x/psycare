<?php

namespace Tests\Feature\Admin;

use App\Mail\SmtpTestMail;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get(route('admin.mail-check.index'))->assertRedirect(route('admin.login'));
        $this->post(route('admin.mail-check.send'), ['email' => 'test@example.com'])->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_smtp_configuration_without_credentials_being_exposed(): void
    {
        config()->set('mail.default', 'smtp');
        config()->set('mail.mailers.smtp.host', 'smtp.example.test');
        config()->set('mail.mailers.smtp.username', 'private-user');
        config()->set('mail.mailers.smtp.password', 'private-password');
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.mail-check.index'))
            ->assertOk()
            ->assertSee('SMTP Check')
            ->assertSee('smtp.example.test')
            ->assertSee('Credentials configured')
            ->assertDontSee('private-user')
            ->assertDontSee('private-password');
    }

    public function test_admin_can_send_a_synchronous_smtp_test_message(): void
    {
        Mail::fake();
        $admin = Admin::factory()->create(['name' => 'Platform Admin']);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.mail-check.send'), ['email' => 'deliverability@example.test'])
            ->assertRedirect()
            ->assertSessionHas('smtp_success', 'Test email delivered successfully to deliverability@example.test.');

        Mail::assertSent(SmtpTestMail::class, fn (SmtpTestMail $mail): bool => $mail->hasTo('deliverability@example.test')
            && $mail->requestedBy === 'Platform Admin');

        (new SmtpTestMail('Platform Admin'))
            ->assertHasSubject('PsyCare SMTP test successful')
            ->assertSeeInHtml('SMTP test successful')
            ->assertSeeInHtml('Platform Admin');
    }

    public function test_smtp_test_recipient_must_be_a_valid_email(): void
    {
        Mail::fake();
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.mail-check.send'), ['email' => 'not-an-email'])
            ->assertSessionHasErrors('email');

        Mail::assertNothingOutgoing();
    }
}
