<?php

namespace RayzenAI\Ray\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use RayzenAI\Ray\RayDebugService;

class RayRequestProfiler
{
    protected float $startTime;

    protected float $startMemory;

    protected array $queries = [];

    protected array $duplicateQueries = [];

    protected int $slowQueryThreshold = 100;

    public function handle(Request $request, Closure $next): Response
    {
        if (! function_exists('isLocal') || ! isLocal()) {
            return $next($request);
        }

        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage(true);
        $this->queries = [];
        $this->duplicateQueries = [];

        DB::listen(function ($query) {
            $this->queries[] = [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time,
                'trace' => $this->getCallerTrace(),
            ];

            $queryHash = md5($query->sql);
            if (! isset($this->duplicateQueries[$queryHash])) {
                $this->duplicateQueries[$queryHash] = ['sql' => $query->sql, 'count' => 0, 'total_time' => 0];
            }
            $this->duplicateQueries[$queryHash]['count']++;
            $this->duplicateQueries[$queryHash]['total_time'] += $query->time;
        });

        $response = $next($request);

        $this->logRequestProfile($request, $response);

        return $response;
    }

    protected function shouldSkip(Request $request): bool
    {
        $path = $request->path();

        return str_starts_with($path, 'debug/')
            || str_starts_with($path, '_debugbar')
            || str_starts_with($path, 'livewire/update')
            || str_starts_with($path, 'horizon')
            || str_starts_with($path, 'telescope')
            || $request->is('*.js', '*.css', '*.ico', '*.png', '*.jpg', '*.svg', '*.woff', '*.woff2');
    }

    protected function logRequestProfile(Request $request, Response $response): void
    {
        $totalTime = (microtime(true) - $this->startTime) * 1000;
        $memoryUsed = memory_get_usage(true) - $this->startMemory;
        $peakMemory = memory_get_peak_usage(true);

        $slowQueries = array_filter($this->queries, fn ($q) => $q['time'] >= $this->slowQueryThreshold);
        $totalQueryTime = array_sum(array_column($this->queries, 'time'));

        $n1Queries = array_filter($this->duplicateQueries, fn ($q) => $q['count'] >= 3);

        $hasSlowQueries = count($slowQueries) > 0;
        $hasN1 = count($n1Queries) > 0;
        $isSlowRequest = $totalTime >= 100;
        $hasHighMemory = $peakMemory >= 25 * 1024 * 1024;

        if (! $isSlowRequest && ! $hasHighMemory) {
            return;
        }

        $color = match (true) {
            $hasN1 => 'red',
            $totalTime >= 500 => 'red',
            $hasHighMemory => 'red',
            $totalTime >= 200 => 'orange',
            default => 'yellow',
        };

        $title = sprintf(
            '%s %s (%dms)',
            $request->method(),
            '/' . $request->path(),
            round($totalTime)
        );

        if ($hasN1) {
            $title = 'N+1 ' . $title;
        }

        $data = [
            'request' => [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'route' => $request->route()?->getName() ?? $request->path(),
            ],
            'performance' => [
                'total_time_ms' => round($totalTime, 2),
                'query_time_ms' => round($totalQueryTime, 2),
                'memory_used' => $this->formatBytes($memoryUsed),
                'peak_memory' => $this->formatBytes($peakMemory),
            ],
            'queries' => [
                'total_count' => count($this->queries),
                'slow_count' => count($slowQueries),
            ],
        ];

        if ($hasN1) {
            $data['n1_queries'] = array_values(array_map(fn ($q) => [
                'sql' => strlen($q['sql']) > 100 ? substr($q['sql'], 0, 100) . '...' : $q['sql'],
                'count' => $q['count'],
                'total_time_ms' => round($q['total_time'], 2),
            ], $n1Queries));
        }

        if ($hasSlowQueries) {
            $data['slow_queries'] = array_values(array_map(fn ($q) => [
                'sql' => strlen($q['sql']) > 150 ? substr($q['sql'], 0, 150) . '...' : $q['sql'],
                'time_ms' => round($q['time'], 2),
                'called_from' => $q['trace'][0] ?? null,
            ], array_slice($slowQueries, 0, 5)));
        }

        app(RayDebugService::class)->storeWithColor(
            $title,
            $data,
            $color,
            null,
            null,
            'request'
        );
    }

    protected function getCallerTrace(): array
    {
        $skipPatterns = [
            '/vendor/',
            'RayRequestProfiler',
            'Illuminate/Database',
            'Illuminate/Events',
        ];

        return collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 50))
            ->filter(function ($frame) use ($skipPatterns) {
                if (! isset($frame['file'])) {
                    return false;
                }
                foreach ($skipPatterns as $pattern) {
                    if (str_contains($frame['file'], $pattern)) {
                        return false;
                    }
                }

                return true;
            })
            ->take(3)
            ->map(fn ($frame) => sprintf('%s:%d', str_replace(base_path() . '/', '', $frame['file']), $frame['line'] ?? 0))
            ->values()
            ->all();
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
