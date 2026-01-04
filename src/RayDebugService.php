<?php

namespace RayzenAI\Ray;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class RayDebugService
{
    protected string $storagePath;

    protected int $maxEntries;

    public function __construct()
    {
        $this->storagePath = config('ray.storage_path', storage_path('logs/ray-debug.json'));
        $this->maxEntries = config('ray.max_entries', 100);
    }

    public function store(string $title, mixed $data, ?string $file = null, ?int $line = null, string $color = 'default'): array
    {
        return $this->storeWithColor($title, $data, $color, $file, $line);
    }

    public function storeWithColor(string $title, mixed $data, string $color = 'default', ?string $file = null, ?int $line = null, string $category = 'debug'): array
    {
        $entries = $this->all();

        $entry = [
            'id' => Str::uuid()->toString(),
            'title' => $title,
            'data' => $data,
            'type' => $this->getType($data),
            'color' => $color,
            'category' => $category,
            'file' => $file ? str_replace(base_path() . '/', '', $file) : null,
            'line' => $line,
            'timestamp' => now()->toIso8601String(),
            'timestamp_human' => now()->format('H:i:s.v'),
        ];

        array_unshift($entries, $entry);
        $entries = array_slice($entries, 0, $this->maxEntries);
        $this->save($entries);

        return $entry;
    }

    public function all(): array
    {
        if (! File::exists($this->storagePath)) {
            return [];
        }

        return json_decode(File::get($this->storagePath), true) ?? [];
    }

    public function delete(string $id): bool
    {
        $entries = $this->all();
        $originalCount = count($entries);
        $entries = array_values(array_filter($entries, fn ($entry) => $entry['id'] !== $id));

        if (count($entries) === $originalCount) {
            return false;
        }

        $this->save($entries);

        return true;
    }

    public function clear(): void
    {
        if (File::exists($this->storagePath)) {
            File::delete($this->storagePath);
        }
    }

    protected function save(array $entries): void
    {
        $directory = dirname($this->storagePath);
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        File::put($this->storagePath, json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    protected function getType(mixed $data): string
    {
        return match (true) {
            is_array($data) => 'array',
            is_object($data) => get_class($data),
            is_string($data) => 'string',
            is_int($data) => 'integer',
            is_float($data) => 'float',
            is_bool($data) => 'boolean',
            is_null($data) => 'null',
            default => 'unknown',
        };
    }
}
