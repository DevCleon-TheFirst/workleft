<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use App\Modules\Tasks\Models\Task;
use App\Observers\TaskObserver;
use App\Modules\Meetings\Models\Meeting;
use App\Observers\MeetingObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Modules\AI\Contracts\AIProviderInterface::class,
            \App\Modules\AI\Providers\DeepSeekProvider::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Task::observe(TaskObserver::class);
        Meeting::observe(MeetingObserver::class);
        Vite::prefetch(concurrency: 3);
    }
}
