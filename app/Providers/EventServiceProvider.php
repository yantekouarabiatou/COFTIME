<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\ModelActivityEvent;
use App\Listeners\SendActivityNotification;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ModelActivityEvent::class => [
            SendActivityNotification::class,
        ],
    ];

    public function boot()
    {
        parent::boot();
    }
}
