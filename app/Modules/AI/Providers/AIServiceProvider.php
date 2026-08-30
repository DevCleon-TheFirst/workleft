<?php

namespace App\Modules\AI\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\AI\Contracts\AIProviderInterface;

class AIServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(AIProviderInterface::class, function ($app) {
            $default = config('services.ai.default', 'deepseek');
            
            return match ($default) {
                'groq' => new GroqProvider(),
                default => new DeepSeekProvider(),
            };
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
