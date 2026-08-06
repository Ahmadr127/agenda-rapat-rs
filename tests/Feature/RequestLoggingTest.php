<?php

namespace Tests\Feature;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicAgendaController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class RequestLoggingTest extends TestCase
{
    use RefreshDatabase;

    protected string $logPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configureTestLogging();

        Route::get('/testing/request-logging/boom', function (): never {
            throw new RuntimeException('Request logging boom');
        })->name('testing.request-logging.boom');
    }

    public function test_public_route_logs_request_completion_metadata(): void
    {
        $response = $this->get('/?filter=recent');

        $response->assertOk();

        $contents = $this->readLogContents();

        $this->assertStringContainsString('Request completed', $contents);
        $this->assertLogContextContains([
            'event' => 'request.completed',
            'method' => 'GET',
            'path' => '/',
            'route_name' => 'home',
            'controller_action' => PublicAgendaController::class.'@index',
            'response_status' => 200,
            'auth_state' => 'guest',
            'query_keys' => ['filter'],
        ], $contents);
        $this->assertMatchesRegularExpression('/"duration_ms":[0-9]+(?:\.[0-9]+)?/', $contents);
        $this->assertMatchesRegularExpression('/"request_id":"[^"]+"/', $contents);
    }

    public function test_authenticated_route_logs_authenticated_user_context(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk();

        $this->assertLogContextContains([
            'event' => 'request.completed',
            'route_name' => 'profile.edit',
            'controller_action' => ProfileController::class.'@edit',
            'response_status' => 200,
            'auth_state' => 'authenticated',
            'user_id' => $user->getAuthIdentifier(),
            'user_email' => $user->email,
        ]);
    }

    public function test_not_found_route_still_logs_request_completion(): void
    {
        $response = $this->get('/missing-page?search=term');

        $response->assertNotFound();

        $this->assertLogContextContains([
            'event' => 'request.completed',
            'path' => 'missing-page',
            'route_name' => null,
            'controller_action' => null,
            'response_status' => 404,
            'query_keys' => ['search'],
        ]);
    }

    public function test_exception_requests_log_enriched_exception_context_and_keep_standard_error_flow(): void
    {
        $response = $this->withExceptionHandling()->get('/testing/request-logging/boom');

        $response->assertStatus(500);

        $contents = $this->readLogContents();

        $this->assertStringContainsString('Request logging boom', $contents);
        $this->assertLogContextContains([
            'event' => 'request.exception',
            'route_name' => 'testing.request-logging.boom',
            'controller_action' => null,
            'method' => 'GET',
            'path' => 'testing/request-logging/boom',
        ], $contents);
        $this->assertLogContextContains([
            'event' => 'request.completed',
            'route_name' => 'testing.request-logging.boom',
            'response_status' => 500,
        ], $contents);
        $this->assertMatchesRegularExpression('/"request_id":"[^"]+"/', $contents);
    }

    public function test_logging_defaults_to_daily_rotation(): void
    {
        $config = File::get(config_path('logging.php'));

        $this->assertStringContainsString("'default' => env('LOG_CHANNEL', 'daily')", $config);
        $this->assertStringContainsString("env('LOG_STACK', 'daily')", $config);
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
