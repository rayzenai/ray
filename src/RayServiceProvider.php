<?php

namespace RayzenAI\Ray;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ray.php', 'ray');

        $this->app->singleton(RayDebugService::class, fn () => new RayDebugService);
    }

    public function boot(): void
    {
        if (! $this->app->environment('local')) {
            return;
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'ray');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/ray.php' => config_path('ray.php'),
            ], 'ray-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/ray'),
            ], 'ray-views');
        }

        $this->registerQueryLogging();
        $this->registerRoutes();
    }

    protected function registerQueryLogging(): void
    {
        $threshold = config('ray.slow_query_threshold', 100);

        DB::listen(function ($query) use ($threshold) {
            if ($query->time < $threshold) {
                return;
            }

            $color = match (true) {
                $query->time >= 500 => 'red',
                $query->time >= 200 => 'orange',
                default => 'yellow',
            };

            $trace = collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 50))
                ->filter(fn ($frame) => isset($frame['file']) && ! str_contains($frame['file'], '/vendor/'))
                ->take(5)
                ->map(fn ($frame) => sprintf(
                    '%s:%d',
                    str_replace(base_path() . '/', '', $frame['file']),
                    $frame['line'] ?? 0
                ))
                ->values()
                ->all();

            $caller = collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 50))
                ->first(fn ($frame) => isset($frame['file']) && ! str_contains($frame['file'], '/vendor/'));

            app(RayDebugService::class)->storeWithColor(
                sprintf('Slow Query (%.1fms)', $query->time),
                [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time_ms' => round($query->time, 2),
                    'called_from' => $trace,
                ],
                $color,
                $caller['file'] ?? null,
                $caller['line'] ?? null,
                'query'
            );
        });
    }

    protected function registerRoutes(): void
    {
        Route::middleware('web')->prefix('debug/ray')->group(function () {
            Route::get('/', function () {
                $this->authorizeAccess();
                $entries = app(RayDebugService::class)->all();

                return view('ray::ray', ['entries' => $entries]);
            });

            Route::post('/clear', function () {
                $this->authorizeAccess();
                app(RayDebugService::class)->clear();

                return response()->json(['success' => true]);
            });

            Route::delete('/{id}', function ($id) {
                $this->authorizeAccess();
                $deleted = app(RayDebugService::class)->delete($id);

                return response()->json(['success' => $deleted]);
            });
        });
    }

    protected function authorizeAccess(): void
    {
        $allowedEmails = config('ray.allowed_emails', []);

        if (empty($allowedEmails)) {
            abort(500, 'Ray Debug: No allowed emails configured. Set ray.allowed_emails to ["*"] to allow all users, or specify allowed email addresses.');
        }

        // Allow all if wildcard is set
        if (in_array('*', $allowedEmails, true)) {
            return;
        }

        $user = auth()->user();

        if (! $user) {
            abort(403, 'Access denied to Ray Debug Viewer. Authentication required.');
        }

        $emailField = config('ray.user_email_field', 'email');
        $userEmail = $user->{$emailField} ?? null;

        if (! in_array($userEmail, $allowedEmails, true)) {
            abort(403, 'Access denied to Ray Debug Viewer.');
        }
    }
}
