# Ray Debug for Laravel

A lightweight Ray-like debugging tool for Laravel applications with a web-based debug viewer.

## Features

- `ray()` helper function for quick debugging
- Automatic slow query logging (configurable threshold)
- Request profiling middleware (N+1 detection, memory usage)
- Web-based debug viewer at `/debug/ray`
- Color-coded entries (red, orange, yellow, green, blue)
- Configurable access control via email whitelist

## Requirements

- PHP 8.2+
- Laravel 11 or 12

## Installation

```bash
composer require rayzenai/ray
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=ray-config
```

This creates `config/ray.php`:

```php
return [
    // Define allowed users by email
    // Use ['*'] to allow all authenticated users
    'allowed_emails' => [
        // '*',
        // 'admin@example.com',
    ],

    // User model configuration (for apps with custom user tables)
    'user_model' => \App\Models\User::class,
    'user_email_field' => 'email',

    // Queries slower than this (ms) are auto-logged
    'slow_query_threshold' => 100,

    // Max debug entries to keep
    'max_entries' => 100,

    // Storage location
    'storage_path' => storage_path('logs/ray-debug.json'),
];
```

## Usage

### Basic Debugging

```php
// Simple debug
ray($variable);

// With label
ray('user data', $user);

// With colors
ray($data)->red();
ray($data)->green();
ray($data)->blue();
ray($data)->yellow();
ray($data)->orange();

// Chained
ray($data)->label('My Label')->green();
```

### Static Methods

```php
// Semantic logging
Ray::error($data);    // Red
Ray::warning($data);  // Yellow
Ray::success($data);  // Green
Ray::info($data);     // Blue

// Performance
Ray::measure('label');        // Start timer
// ... code ...
Ray::measure('label');        // Stop & log elapsed time

Ray::memory();                // Log memory usage

// Debugging
Ray::trace();                 // Log stack trace
Ray::caller();                // Log caller info

// Query logging
Ray::showQueries();           // Start logging all queries
Ray::showSlowQueries(50);     // Log queries > 50ms
Ray::stopQueries();           // Stop logging
Ray::querySummary();          // Get query stats
```

### Counter

```php
// Count iterations
foreach ($items as $item) {
    Ray::count('loop');       // Increments each call
}

Ray::resetCount('loop');      // Reset counter
```

### Die & Dump

```php
ray($data)->die();            // Log and exit

// Or use helper
rayDie($data, 'label');
```

## Request Profiler Middleware

Add the middleware to log slow requests and detect N+1 queries:

```php
// app/Http/Kernel.php
protected $middlewareGroups = [
    'web' => [
        // ... other middleware
        \RayzenAI\Ray\Middleware\RayRequestProfiler::class,
    ],
];
```

The profiler automatically logs:
- Requests taking > 100ms
- Requests using > 25MB memory
- N+1 query detection (same query 3+ times)

## Debug Viewer

Access the debug viewer at `/debug/ray` (local environment only).

Features:
- Tabs: Debug, Requests, Queries
- Color filters
- Search
- Auto-refresh toggle
- Expand/collapse entries
- Copy to clipboard
- Delete individual entries or clear all

## Security

The package only works in `local` environment. Access is controlled by:

1. **Empty config** - Shows error message prompting configuration
2. **Wildcard `['*']`** - Allows all authenticated users
3. **Email list** - Only specified emails can access

## License

MIT
