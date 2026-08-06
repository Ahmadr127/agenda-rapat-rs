<?php

namespace App\Listeners;

use App\Support\Logging\RequestLogContext;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class LogAuthenticationActivity
{
    public function handleLogin(Login $event): void
    {
        Log::info('Authentication login recorded', RequestLogContext::authContext(
            event: 'auth.login',
            user: $event->user,
            extra: [
                'guard' => $event->guard,
                'remember' => $event->remember,
            ],
        ));
    }

    public function handleLogout(Logout $event): void
    {
        Log::info('Authentication logout recorded', RequestLogContext::authContext(
            event: 'auth.logout',
            user: $event->user,
            extra: [
                'guard' => $event->guard,
            ],
        ));
    }

    public function handleFailed(Failed $event): void
    {
        Log::warning('Authentication failure recorded', RequestLogContext::authContext(
            event: 'auth.failed',
            extra: array_merge(
                [
                    'guard' => $event->guard,
                ],
                $this->identifierContext($event->credentials),
            ),
        ));
    }

    public function handleLockout(Lockout $event): void
    {
        $request = $event->request;
        $throttleKey = method_exists($request, 'throttleKey')
            ? $request->throttleKey()
            : null;

        Log::warning('Authentication lockout recorded', RequestLogContext::authContext(
            event: 'auth.lockout',
            request: $request,
            extra: array_merge(
                [
                    'throttle_key' => $throttleKey,
                    'available_in_seconds' => $throttleKey ? RateLimiter::availableIn($throttleKey) : null,
                ],
                $this->identifierContext([], $request),
            ),
        ));
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'handleLogin',
            Logout::class => 'handleLogout',
            Failed::class => 'handleFailed',
            Lockout::class => 'handleLockout',
        ];
    }

    protected function identifierContext(array $credentials, ?Request $request = null): array
    {
        $request ??= RequestLogContext::currentRequest();
        $identifierFields = ['email', 'username', 'login'];

        foreach ($identifierFields as $field) {
            $value = $credentials[$field] ?? $request?->input($field);

            if (is_scalar($value) && $value !== '') {
                return [
                    'auth_identifier_field' => $field,
                    'auth_identifier' => (string) $value,
                ];
            }
        }

        foreach ($credentials as $field => $value) {
            if (in_array($field, ['password', 'password_confirmation', 'remember', '_token'], true)) {
                continue;
            }

            if (is_scalar($value) && $value !== '') {
                return [
                    'auth_identifier_field' => (string) $field,
                    'auth_identifier' => (string) $value,
                ];
            }
        }

        return [
            'auth_identifier_field' => null,
            'auth_identifier' => null,
        ];
    }
}
