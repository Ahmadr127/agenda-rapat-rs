<?php

namespace App\Support\Logging;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use Throwable;

class RequestLogContext
{
    public const REQUEST_ID_ATTRIBUTE = 'logging.request_id';

    public const START_TIME_ATTRIBUTE = 'logging.request_started_at';

    public static function initializeRequest(Request $request): void
    {
        $request->attributes->set(
            self::REQUEST_ID_ATTRIBUTE,
            self::resolveRequestId($request),
        );

        $request->attributes->set(self::START_TIME_ATTRIBUTE, microtime(true));
    }

    public static function requestCompleted(Request $request, mixed $response): array
    {
        return array_merge(
            self::baseRequestContext($request),
            [
                'event' => 'request.completed',
                'response_status' => method_exists($response, 'getStatusCode')
                    ? $response->getStatusCode()
                    : null,
                'duration_ms' => self::durationMs($request),
            ],
        );
    }

    public static function exceptionContext(?Request $request, Throwable $exception): array
    {
        return array_merge(
            self::baseRequestContext($request),
            [
                'event' => 'request.exception',
                'session_id' => self::sessionId($request),
                'exception_class' => $exception::class,
            ],
        );
    }

    public static function authContext(
        string $event,
        ?Request $request = null,
        ?Authenticatable $user = null,
        array $extra = [],
    ): array {
        $request ??= self::currentRequest();

        return array_merge(
            self::baseRequestContext($request, $user),
            [
                'event' => $event,
                'session_id' => self::sessionId($request),
            ],
            $extra,
        );
    }

    public static function currentRequest(): ?Request
    {
        if (! app()->bound('request')) {
            return null;
        }

        $request = request();

        return $request instanceof Request ? $request : null;
    }

    public static function requestId(?Request $request): ?string
    {
        if (! $request instanceof Request) {
            return null;
        }

        $requestId = $request->attributes->get(self::REQUEST_ID_ATTRIBUTE);

        if (is_string($requestId) && $requestId !== '') {
            return $requestId;
        }

        $requestId = self::resolveRequestId($request);

        $request->attributes->set(self::REQUEST_ID_ATTRIBUTE, $requestId);

        return $requestId;
    }

    protected static function baseRequestContext(
        ?Request $request,
        ?Authenticatable $user = null,
    ): array {
        $route = $request?->route();
        $user ??= $request?->user();

        return [
            'request_id' => self::requestId($request),
            'occurred_at' => now()->toIso8601String(),
            'method' => $request?->method(),
            'path' => $request?->path(),
            'url' => $request?->url(),
            'route_name' => self::routeName($route),
            'controller_action' => self::controllerAction($route),
            'query_keys' => $request instanceof Request ? array_values(array_keys($request->query())) : [],
            'auth_state' => $user instanceof Authenticatable ? 'authenticated' : 'guest',
            'user_id' => $user?->getAuthIdentifier(),
            'user_email' => is_object($user) ? data_get($user, 'email') : null,
            'ip' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ];
    }

    protected static function controllerAction(?Route $route): ?string
    {
        $action = $route?->getActionName();

        if (! is_string($action) || $action === 'Closure' || str_contains($action, 'SerializableClosure')) {
            return null;
        }

        return $action;
    }

    protected static function routeName(?Route $route): ?string
    {
        $routeName = $route?->getName();

        if (! is_string($routeName) || $routeName === '' || str_starts_with($routeName, 'generated::')) {
            return null;
        }

        return $routeName;
    }

    protected static function durationMs(?Request $request): ?float
    {
        if (! $request instanceof Request) {
            return null;
        }

        $startedAt = $request->attributes->get(self::START_TIME_ATTRIBUTE);

        if (! is_float($startedAt) && ! is_int($startedAt)) {
            return null;
        }

        return round((microtime(true) - $startedAt) * 1000, 2);
    }

    protected static function resolveRequestId(Request $request): string
    {
        $requestId = trim((string) (
            $request->headers->get('X-Request-Id')
            ?: $request->headers->get('X-Correlation-Id')
            ?: ''
        ));

        if ($requestId !== '') {
            return Str::limit($requestId, 120, '');
        }

        return (string) Str::uuid();
    }

    protected static function sessionId(?Request $request): ?string
    {
        if (! $request instanceof Request || ! $request->hasSession()) {
            return null;
        }

        return $request->session()->getId();
    }
}
