<?php

namespace SlowQueryDetector;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class SlowQueryDetectorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/slow-query-detector.php', 'slow-query-detector');

        $this->app->singleton(QueryCollector::class, function ($app) {
            return new QueryCollector(
                threshold: (float) config('slow-query-detector.threshold', 100),
                includeTrace: (bool) config('slow-query-detector.include_trace', true),
                traceDepth: (int) config('slow-query-detector.trace_depth', 5),
            );
        });
    }

    public function boot(): void
    {
        if (!config('slow-query-detector.enabled')) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/slow-query-detector.php' => config_path('slow-query-detector.php'),
        ], 'slow-query-detector-config');

        DB::listen(function (QueryExecuted $event) {
            $collector = $this->app->make(QueryCollector::class);
            $sql = $this->buildSqlWithBindings($event);
            $collector->record($sql, $event->time);
        });
    }

    protected function buildSqlWithBindings(QueryExecuted $event): string
    {
        $sql = $event->sql;

        foreach ($event->bindings as $binding) {
            $value = is_numeric($binding) ? $binding : "'" . addslashes((string) $binding) . "'";
            $sql = preg_replace('/\?/', (string) $value, $sql, 1);
        }

        return $sql;
    }
}
