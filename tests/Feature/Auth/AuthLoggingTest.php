<?php

namespace Tests\Feature\Auth;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthLoggingTest extends TestCase
{
    use RefreshDatabase;

    protected string $logPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configureTestLogging();
    }

    public function test_successful_login_logs_authentication_activity(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);

        $contents = $this->readLogContents();

        $this->assertStringContainsString('Authentication login recorded', $contents);
        $this->assertLogContextContains([
            'event' => 'auth.login',
            'guard' => 'web',
            'route_name' => null,
            'controller_action' => AuthenticatedSessionController::class.'@store',
            'auth_state' => 'authenticated',
            'user_id' => $user->getAuthIdentifier(),
            'user_email' => $user->email,
        ], $contents);
        $this->assertMatchesRegularExpression('/"session_id":"[^"]+"/', $contents);
    }

    public function test_logout_logs_authentication_activity(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();

        $contents = $this->readLogContents();

        $this->assertStringContainsString('Authentication logout recorded', $contents);
        $this->assertLogContextContains([
            'event' => 'auth.logout',
            'guard' => 'web',
            'route_name' => 'logout',
            'controller_action' => AuthenticatedSessionController::class.'@destroy',
            'user_id' => $user->getAuthIdentifier(),
            'user_email' => $user->email,
        ], $contents);
        $this->assertMatchesRegularExpression('/"session_id":"[^"]+"/', $contents);
    }

    public function test_invalid_password_attempt_logs_failed_authentication_activity(): void
    {
        $user = User::factory()->create();

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $this->assertGuest();

        $contents = $this->readLogContents();

        $this->assertStringContainsString('Authentication failure recorded', $contents);
        $this->assertLogContextContains([
            'event' => 'auth.failed',
            'guard' => 'web',
            'route_name' => null,
            'controller_action' => AuthenticatedSessionController::class.'@store',
            'auth_identifier_field' => 'email',
            'auth_identifier' => $user->email,
            'user_id' => null,
        ], $contents);
    }

    public function test_rate_limited_login_attempt_logs_lockout_activity(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 6) as $attempt) {
            $response = $this->from('/login')->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);

            $response->assertRedirect('/login');
        }

        $contents = $this->readLogContents();

        $this->assertStringContainsString('Authentication lockout recorded', $contents);
        $this->assertLogContextContains([
            'event' => 'auth.lockout',
            'route_name' => null,
            'controller_action' => AuthenticatedSessionController::class.'@store',
            'auth_identifier_field' => 'email',
            'auth_identifier' => $user->email,
        ], $contents);
        $this->assertMatchesRegularExpression('/"throttle_key":"[^"]+"/', $contents);
        $this->assertMatchesRegularExpression('/"available_in_seconds":[0-9]+/', $contents);
    }

    protected function configureTestLogging(): void
    {
        $uuid = (string) Str::uuid();
        $basePath = storage_path("logs/{$uuid}.log");

        $this->logPath = Str::replaceLast(
            '.log',
            '-'.now()->format('Y-m-d').'.log',
            $basePath,
        );

        File::delete($this->logPath);

        config()->set('logging.default', 'daily');
        config()->set('logging.channels.stack.channels', ['daily']);
        config()->set('logging.channels.daily.path', $basePath);
        config()->set('logging.channels.daily.days', 1);

        Log::setDefaultDriver('daily');
        Log::forgetChannel('daily');
        Log::forgetChannel('stack');
    }

    protected function readLogContents(): string
    {
        clearstatcache();

        return File::exists($this->logPath)
            ? File::get($this->logPath)
            : '';
    }

    protected function assertLogContextContains(array $context, ?string $contents = null): void
    {
        $contents ??= $this->readLogContents();

        foreach ($context as $key => $value) {
            $fragment = '"'.$key.'":'.json_encode($value, JSON_UNESCAPED_SLASHES);

            $this->assertStringContainsString($fragment, $contents);
        }
    }
}
