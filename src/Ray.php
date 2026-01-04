<?php

namespace RayzenAI\Ray;

use Illuminate\Support\Facades\DB;

class Ray
{
    protected mixed $data = null;

    protected string $title = 'debug';

    protected string $color = 'default';

    protected ?string $file = null;

    protected ?int $line = null;

    protected static array $timers = [];

    protected static array $counters = [];

    protected static bool $queryLogging = false;

    protected static int $slowQueryThreshold = 0;

    protected static array $queryLog = [];

    public function __construct(mixed $data = null)
    {
        $this->data = $data;

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        foreach ($backtrace as $frame) {
            if (! isset($frame['class']) || $frame['class'] !== self::class) {
                $this->file = $frame['file'] ?? null;
                $this->line = $frame['line'] ?? null;
                break;
            }
        }
    }

    public function label(string $label): self
    {
        $this->title = $label;

        return $this;
    }

    public function color(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function red(): self
    {
        return $this->color('red');
    }

    public function green(): self
    {
        return $this->color('green');
    }

    public function blue(): self
    {
        return $this->color('blue');
    }

    public function yellow(): self
    {
        return $this->color('yellow');
    }

    public function purple(): self
    {
        return $this->color('purple');
    }

    public function orange(): self
    {
        return $this->color('orange');
    }

    public static function error(mixed $data): self
    {
        return (new self($data))->label('error')->red();
    }

    public static function warning(mixed $data): self
    {
        return (new self($data))->label('warning')->yellow();
    }

    public static function success(mixed $data): self
    {
        return (new self($data))->label('success')->green();
    }

    public static function info(mixed $data): self
    {
        return (new self($data))->label('info')->blue();
    }

    public static function measure(string $label = 'timer'): self
    {
        if (! isset(self::$timers[$label])) {
            self::$timers[$label] = microtime(true);

            return (new self("Timer '{$label}' started"))->label('measure')->purple();
        }

        $elapsed = (microtime(true) - self::$timers[$label]) * 1000;
        unset(self::$timers[$label]);

        return (new self(sprintf('%.2f ms', $elapsed)))->label("measure: {$label}")->purple();
    }

    public static function memory(): self
    {
        $usage = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);

        return (new self([
            'current' => self::formatBytes($usage),
            'peak' => self::formatBytes($peak),
            'current_bytes' => $usage,
            'peak_bytes' => $peak,
        ]))->label('memory')->purple();
    }

    public static function count(string $label = 'count'): self
    {
        if (! isset(self::$counters[$label])) {
            self::$counters[$label] = 0;
        }
        self::$counters[$label]++;

        return (new self(self::$counters[$label]))->label("count: {$label}")->orange();
    }

    public static function resetCount(string $label = 'count'): void
    {
        unset(self::$counters[$label]);
    }

    public static function showQueries(): self
    {
        self::$queryLogging = true;
        self::$slowQueryThreshold = 0;
        self::$queryLog = [];

        DB::listen(function ($query) {
            if (! self::$queryLogging) {
                return;
            }

            if (self::$slowQueryThreshold > 0 && $query->time < self::$slowQueryThreshold) {
                return;
            }

            self::$queryLog[] = [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time_ms' => $query->time,
            ];

            (new self([
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => sprintf('%.2f ms', $query->time),
            ]))->label('query')->color($query->time > 100 ? 'red' : ($query->time > 50 ? 'yellow' : 'blue'))->send();
        });

        return (new self('Query logging enabled'))->label('queries')->blue();
    }

    public static function showSlowQueries(int $thresholdMs = 100): self
    {
        self::$slowQueryThreshold = $thresholdMs;

        return self::showQueries()->label("slow queries (>{$thresholdMs}ms)");
    }

    public static function stopQueries(): self
    {
        self::$queryLogging = false;
        $count = count(self::$queryLog);

        return (new self("Stopped. Logged {$count} queries"))->label('queries')->blue();
    }

    public static function querySummary(): self
    {
        $queries = self::$queryLog;
        $totalTime = array_sum(array_column($queries, 'time_ms'));

        return (new self([
            'total_queries' => count($queries),
            'total_time_ms' => round($totalTime, 2),
            'avg_time_ms' => count($queries) ? round($totalTime / count($queries), 2) : 0,
            'slowest_query' => ! empty($queries) ? max(array_column($queries, 'time_ms')) . ' ms' : null,
        ]))->label('query summary')->purple();
    }

    public function pause(): self
    {
        $this->title = 'PAUSED: ' . $this->title;
        $this->color = 'orange';

        return $this;
    }

    public function die(): never
    {
        $this->label('DIE: ' . $this->title)->red()->send();
        exit(1);
    }

    public static function trace(): self
    {
        $trace = collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS))
            ->slice(1, 10)
            ->map(fn ($frame, $i) => sprintf(
                '#%d %s%s%s() at %s:%d',
                $i,
                $frame['class'] ?? '',
                $frame['type'] ?? '',
                $frame['function'] ?? 'unknown',
                basename($frame['file'] ?? 'unknown'),
                $frame['line'] ?? 0
            ))
            ->values()
            ->all();

        return (new self($trace))->label('trace')->purple();
    }

    public static function caller(): self
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $caller = $backtrace[1] ?? [];

        return (new self([
            'file' => $caller['file'] ?? 'unknown',
            'line' => $caller['line'] ?? 0,
            'function' => ($caller['class'] ?? '') . ($caller['type'] ?? '') . ($caller['function'] ?? ''),
        ]))->label('caller')->blue();
    }

    public function send(): self
    {
        if (! function_exists('isLocal') || ! isLocal()) {
            return $this;
        }

        $serializedData = $this->serializeData($this->data);

        app(RayDebugService::class)->storeWithColor(
            $this->title,
            $serializedData,
            $this->color,
            $this->file,
            $this->line
        );

        return $this;
    }

    public function __destruct()
    {
        if ($this->data !== null || $this->title !== 'debug') {
            $this->send();
        }
    }

    public function value(): mixed
    {
        return $this->data;
    }

    protected function serializeData(mixed $data): mixed
    {
        if (is_object($data)) {
            if (method_exists($data, 'toArray')) {
                return $data->toArray();
            } elseif ($data instanceof \JsonSerializable) {
                return $data->jsonSerialize();
            } elseif ($data instanceof \Throwable) {
                return [
                    'message' => $data->getMessage(),
                    'code' => $data->getCode(),
                    'file' => $data->getFile(),
                    'line' => $data->getLine(),
                    'trace' => array_slice($data->getTrace(), 0, 5),
                ];
            }

            return (array) $data;
        }

        return $data;
    }

    protected static function formatBytes(int $bytes): string
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
