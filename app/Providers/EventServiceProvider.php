<?php

namespace RecognitionGame\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'RecognitionGame\Events\Event' => [
            'RecognitionGame\Listeners\EventListener',
        ],
        'RecognitionGame\Events\NewGameLoading' => [
            'RecognitionGame\Listeners\NewGameLoadingListener',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
