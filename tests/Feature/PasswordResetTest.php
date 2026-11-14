<?php

namespace Tests\Feature;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_links_to_password_recovery(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('password.request'));

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Enviar enlace de recuperación');
    }

    public function test_confirmation_message_is_the_same_for_registered_and_unknown_emails(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'ana@example.test']);

        $registeredResponse = $this->post(route('password.email'), ['email' => $user->email]);
        $unknownResponse = $this->post(route('password.email'), ['email' => 'nadie@example.test']);

        $registeredResponse->assertSessionHas(
            'status',
            'Si la cuenta existe, recibirás un enlace para restablecer tu contraseña.'
        );
        $unknownResponse->assertSessionHas(
            'status',
            'Si la cuenta existe, recibirás un enlace para restablecer tu contraseña.'
        );
        Notification::assertSentTo($user, ResetPassword::class);
        $this->assertDatabaseCount('password_reset_tokens', 1);
    }

    public function test_password_reset_link_updates_password_and_cannot_be_reused(): void
    {
        Notification::fake();
        $user = User::factory()->create([
            'email' => 'ana@example.test',
            'password' => 'Original123',
        ]);

        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHas('status');
        $token = Notification::sent($user, ResetPassword::class)->first()->token;
        $credentials = [
            'email' => $user->email,
            'token' => $token,
            'password' => 'NuevaClave123',
            'password_confirmation' => 'NuevaClave123',
        ];

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->assertOk()
            ->assertSee('Crear nueva contraseña');

        $this->post(route('password.update'), $credentials)
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');
        $user->refresh();
        $this->assertSame($user->password, $user->password_hash);
        $this->assertTrue(Hash::check('NuevaClave123', $user->password_hash));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);

        $this->post(route('password.update'), $credentials)
            ->assertSessionHasErrors('email');
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'Original123'])
            ->assertSessionHasErrors('credentials');
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'NuevaClave123'])
            ->assertRedirect(route('dashboard'));
    }

    public function test_expired_link_cannot_reset_password(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'ana@example.test']);

        $this->post(route('password.email'), ['email' => $user->email]);
        $token = Notification::sent($user, ResetPassword::class)->first()->token;
        $this->travel(31)->minutes();

        $this->post(route('password.update'), [
            'email' => $user->email,
            'token' => $token,
            'password' => 'NuevaClave123',
            'password_confirmation' => 'NuevaClave123',
        ])->assertSessionHasErrors('email');
    }

    public function test_reset_password_uses_the_same_security_rules_as_registration(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'ana@example.test']);

        $this->post(route('password.email'), ['email' => $user->email]);
        $token = Notification::sent($user, ResetPassword::class)->first()->token;

        $this->post(route('password.update'), [
            'email' => $user->email,
            'token' => $token,
            'password' => 'weakpass',
            'password_confirmation' => 'weakpass',
        ])->assertSessionHasErrors('password');
    }

    public function test_reset_token_lifetime_is_thirty_minutes(): void
    {
        $this->assertSame(30, config('auth.passwords.users.expire'));
    }

    public function test_mail_configuration_always_has_a_from_address(): void
    {
        $this->assertNotEmpty(config('mail.from.address'));
    }

    public function test_invalid_email_is_rejected_before_sending_a_reset_link(): void
    {
        Notification::fake();

        $this->post(route('password.email'), ['email' => 'not-an-email'])
            ->assertSessionHasErrors('email');

        Notification::assertNothingSent();
    }
    public function test_reset_email_is_in_spanish_and_contains_the_reset_link(): void
{
    Notification::fake();
    $user = User::factory()->create(['email' => 'ana@example.test']);

    $this->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $mail = $notification->toMail($user);

        return $mail->subject === 'Restablece tu contraseña'
            && str_contains($mail->actionUrl, $notification->token);
    });
}

public function test_mail_failure_does_not_break_the_page_nor_reveal_anything(): void
{
    Log::spy();
    Password::shouldReceive('sendResetLink')->once()->andThrow(new \RuntimeException('SMTP caído'));

    $this->post(route('password.email'), ['email' => 'ana@example.test'])
        ->assertSessionHas(
            'status',
            'Si la cuenta existe, recibirás un enlace para restablecer tu contraseña.'
        );

    Log::shouldHaveReceived('error')->once();
}
}
