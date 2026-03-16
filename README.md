# Laravel Slow Query Detector

A lightweight Laravel package that detects slow database queries and N+1 problems, logging them with execution time and origin trace.

## Installation

```bash
composer require furkankufrevi/laravel-slow-query-detector
```

The service provider is auto-discovered. To publish the config:

```bash
php artisan vendor:publish --tag=slow-query-detector-config
```

## Setup

Add the middleware to your routes or groups:

```php
// bootstrap/app.php (Laravel 11+)
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\SlowQueryDetector\Middleware\SlowQueryMiddleware::class);
})

// app/Http/Kernel.php (Laravel 10)
protected $middleware = [
    \SlowQueryDetector\Middleware\SlowQueryMiddleware::class,
];
```

## Configuration

Set via `.env` or publish the config file:

```env
SLOW_QUERY_DETECTOR_ENABLED=true
SLOW_QUERY_THRESHOLD=100
SLOW_QUERY_LOG_CHANNEL=
```

| Option | Default | Description |
|---|---|---|
| `enabled` | `true` | Enable/disable the detector |
| `threshold` | `100` | Slow query threshold in milliseconds |
| `detect_n_plus_one` | `true` | Enable N+1 duplicate query detection |
| `duplicate_threshold` | `3` | Min repeated queries to trigger N+1 warning |
| `log_channel` | `null` | Log channel (null = default) |
| `include_trace` | `true` | Include origin stack trace in logs |
| `trace_depth` | `5` | Max stack frames to capture |

## Log Output

**Slow query:**
```
[Slow Query] GET /users | 250.00ms | select * from users where active = 1 | Origin: UserController.php:42
```

**N+1 detection:**
```
[N+1 Detected] GET /posts | Query repeated 15x: select * from comments where post_id = '3'
```

**Summary:**
```
[Query Summary] GET /posts | 18 queries | 320.50ms total
```

## License

MIT
