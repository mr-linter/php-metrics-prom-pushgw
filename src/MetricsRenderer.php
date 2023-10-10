<?php

namespace MrLinter\Metrics\Storage\PromPushGw;

use MrLinter\Contracts\Metrics\Snapshot;

class MetricsRenderer
{
    public function render(Snapshot $snapshot, array $globalLabels = []): string
    {
        $content = [];

        $this->renderCounters($snapshot, $globalLabels, $content);
        $this->renderGauges($snapshot, $globalLabels, $content);
        $this->renderHistograms($snapshot, $globalLabels, $content);

        return implode("\n", $content);
    }

    /**
     * @param array<string, string> $globalLabels
     * @param array<string> $content
     */
    private function renderCounters(Snapshot $snapshot, array $globalLabels, array &$content): void
    {
        if (count($snapshot->counters) === 0) {
            return;
        }

        $content[] = "# HELP {$snapshot->subject->key} {$snapshot->subject->title}";
        $content[] = "# TYPE {$snapshot->subject->key} Counter";
        $content[] = "\n";

        foreach ($snapshot->counters as $counter) {
            $labelsString = $this->collectLabels($counter->labels, $globalLabels);

            $content[] = sprintf('%s{%s} %d', $snapshot->subject->key, $labelsString, $counter->value);

            $content[] = "\n";
        }
    }

    /**
     * @param array<string, string> $globalLabels
     * @param array<string> $content
     */
    private function renderGauges(Snapshot $snapshot, array $globalLabels, array &$content): void
    {
        if (count($snapshot->gauges) === 0) {
            return;
        }

        $content[] = "# HELP {$snapshot->subject->key} {$snapshot->subject->title}";
        $content[] = "# TYPE {$snapshot->subject->key} Gauge";
        $content[] = "\n";

        foreach ($snapshot->gauges as $counter) {
            $labelsString = $this->collectLabels($counter->labels, $globalLabels);

            $content[] = sprintf('%s{%s} %d', $snapshot->subject->key, $labelsString, $counter->value);

            $content[] = "\n";
        }
    }

    private function renderHistograms(Snapshot $snapshot, array $globalLabels, array &$content): void
    {
        if (count($snapshot->histograms) === 0) {
            return;
        }

        $content[] = "# HELP {$snapshot->subject->key} {$snapshot->subject->title}";
        $content[] = "# TYPE {$snapshot->subject->key} Histogram";
        $content[] = "\n";

        foreach ($snapshot->histograms as $key => $gram) {
            $labelsString = $this->collectLabels($gram->labels(), $globalLabels);

            $bucketKey = $key . '_bucket';

            foreach ($gram->frequencies() as $bucket => $bucketCount) {
                $content[] = sprintf('%s{le="%d",%s} %d', $bucketKey, $bucket, $labelsString, $bucketCount);
            }

            $count = 0;
            $sum = 0;

            foreach ($gram->all() as $value) {
                ++$count;
                $sum += $value;
            }

            $content[] = sprintf('%s{le="+Inf",%s} %d', $bucketKey, $labelsString, $count);
            $content[] = sprintf('%s_sum{%s} %d', $key, $labelsString, $sum);
            $content[] = sprintf('%s_count{%s} %d', $key, $labelsString, $count);

            $content[] = "\n";
        }
    }

    /**
     * @param array<string, string> $globalLabels
     */
    private function collectLabels(array $labels, array $globalLabels): string
    {
        $wrapper = function (array $labels) use (&$labelsString) {
            foreach ($labels as $labelKey => $labelValue) {
                $labelsString .= sprintf(
                    '%s="%s"',
                    $labelKey,
                    $labelValue,
                );

                if (next($labels) !== false) {
                    $labelsString .= ',';
                }
            }
        };

        $labelsString = '';

        $wrapper($labels);
        $wrapper($globalLabels);

        return $labelsString;
    }
}
