<?php

namespace App\Providers;

use App\Listeners\LogAuthenticationActivity;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event subscriber classes to register.
     *
     * @var array<int, class-string>
     */
    protected $subscribe = [
        LogAuthenticationActivity::class,
    ];
}
