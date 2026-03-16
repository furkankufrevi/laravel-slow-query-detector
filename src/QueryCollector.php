<?php

namespace SlowQueryDetector;

class QueryCollector
{
    /** @var array<int, array{sql: string, time: float, normalized: string, trace: string|null}> */
    protected array $queries = [];

    protected float $threshold;
    protected bool $includeTrace;
    protected int $traceDepth;

    public function __construct(float $threshold, bool $includeTrace = true, int $traceDepth = 5)
    {
        $this->threshold = $threshold;
        $this->includeTrace = $includeTrace;
        $this->traceDepth = $traceDepth;
    }

    public function record(string $sql, float $time): void
    {
        $this->queries[] = [
            'sql' => $sql,
            'time' => $time,
            'normalized' => $this->normalize($sql),
            'trace' => $this->includeTrace ? $this->captureTrace() : null,
        ];
    }

    /**
     * @return array<int, array{sql: string, time: float}>
     */
    public function getSlowQueries(): array
    {
        return array_values(array_filter($this->queries, fn ($q) => $q['time'] >= $this->threshold));
    }

    /**
     * @return array<string, array{count: int, sql: string}>
     */
    public function getDuplicates(int $minCount = 3): array
    {
        $counts = [];
        foreach ($this->queries as $query) {
            $key = $query['normalized'];
            if (!isset($counts[$key])) {
                $counts[$key] = ['count' => 0, 'sql' => $query['sql']];
            }
            $counts[$key]['count']++;
        }

        return array_filter($counts, fn ($entry) => $entry['count'] >= $minCount);
    }

    public function getTotalTime(): float
    {
        return array_sum(array_column($this->queries, 'time'));
    }

    public function getQueryCount(): int
    {
        return count($this->queries);
    }

    public function reset(): void
    {
        $this->queries = [];
    }

    protected function normalize(string $sql): string
    {
        $sql = preg_replace('/\b\d+\b/', '?', $sql);
        $sql = preg_replace("/('[^']*')/", '?', $sql);
        $sql = preg_replace('/\s+/', ' ', $sql);

        return trim($sql);
    }

    protected function captureTrace(): ?string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 30);

        $appFrames = [];
        foreach ($trace as $frame) {
            $file = $frame['file'] ?? '';
            if ($file && !str_contains($file, '/vendor/') && !str_contains($file, 'SlowQueryDetector')) {
                $appFrames[] = basename($file) . ':' . ($frame['line'] ?? '?');
                if (count($appFrames) >= $this->traceDepth) {
                    break;
                }
            }
        }

        return $appFrames ? implode(' → ', $appFrames) : null;
    }
}
