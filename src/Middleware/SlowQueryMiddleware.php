<?php

namespace SlowQueryDetector\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SlowQueryDetector\QueryCollector;
use Symfony\Component\HttpFoundation\Response;

class SlowQueryMiddleware
{
    protected QueryCollector $collector;

    public function __construct(QueryCollector $collector)
    {
        $this->collector = $collector;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $this->collector->reset();

        $response = $next($request);

        $this->report($request);

        return $response;
    }

    protected function report(Request $request): void
    {
        $route = $request->method() . ' ' . $request->path();
        $logger = $this->getLogger();

        $slowQueries = $this->collector->getSlowQueries();
        foreach ($slowQueries as $query) {
            $message = sprintf(
                '[Slow Query] %s | %.2fms | %s',
                $route,
                $query['time'],
                $query['sql']
            );

            if ($query['trace']) {
                $message .= ' | Origin: ' . $query['trace'];
            }

            $logger->warning($message);
        }

        if (config('slow-query-detector.detect_n_plus_one')) {
            $threshold = (int) config('slow-query-detector.duplicate_threshold', 3);
            $duplicates = $this->collector->getDuplicates($threshold);

            foreach ($duplicates as $entry) {
                $logger->warning(sprintf(
                    '[N+1 Detected] %s | Query repeated %dx: %s',
                    $route,
                    $entry['count'],
                    $entry['sql']
                ));
            }
        }

        $count = $this->collector->getQueryCount();
        $total = $this->collector->getTotalTime();

        if ($count > 0 && ($slowQueries || !empty($duplicates))) {
            $logger->info(sprintf(
                '[Query Summary] %s | %d queries | %.2fms total',
                $route,
                $count,
                $total
            ));
        }
    }

    protected function getLogger(): \Psr\Log\LoggerInterface
    {
        $channel = config('slow-query-detector.log_channel');

        return $channel ? Log::channel($channel) : Log::getFacadeRoot();
    }
}
