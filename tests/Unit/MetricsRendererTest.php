<?php

namespace MrLinter\Metrics\Storage\PromPushGw\Tests;

use MrLinter\Contracts\Metrics\CounterRecord;
use MrLinter\Contracts\Metrics\GaugeRecord;
use MrLinter\Contracts\Metrics\Snapshot;
use MrLinter\Contracts\Metrics\Subject;
use MrLinter\Metrics\Storage\PromPushGw\MetricsRenderer;
use PHPUnit\Framework\TestCase;

final class MetricsRendererTest extends TestCase
{
    public static function providerForTestRender(): array
    {
        return [
            [
                'snapshot' => new Snapshot(
                    new Subject('', '', ''),
                    [],
                    [],
                    [],
                ),
                'globalLabels' => [],
                'expectedContent' => '',
            ],
            [
                'snapshot' => new Snapshot(
                    new Subject('cat', 'test', 'title 1'),
                    [
                        new CounterRecord(2.0, []),
                    ],
                    [],
                    [],
                ),
                'globalLabels' => [],
                'expectedContent' => "# HELP test title 1
# TYPE test Counter
\n
test{} 2\n\n",
            ],
            [
                'snapshot' => new Snapshot(
                    new Subject('cat', 'test', 'title 1'),
                    [
                        new CounterRecord(2.0, ['k' => 'v']),
                    ],
                    [],
                    [],
                ),
                'globalLabels' => [],
                'expectedContent' => "# HELP test title 1
# TYPE test Counter
\n
test{k=\"v\"} 2\n\n",
            ],
            [
                'snapshot' => new Snapshot(
                    new Subject('cat', 'g-key', 'g-title'),
                    [
                        new CounterRecord(2.0, ['k' => 'v']),
                    ],
                    [
                        new GaugeRecord(2.5, ['kk' => 'vv']),
                    ],
                    [],
                ),
                'globalLabels' => [],
                'expectedContent' => "# HELP g-key g-title
# TYPE g-key Counter
\n
g-key{k=\"v\"} 2
\n
# HELP g-key g-title
# TYPE g-key Gauge
\n
g-key{kk=\"vv\"} 2
\n",
            ],
        ];
    }

    /**
     * @covers       \MrLinter\Metrics\Storage\PromPushGw\MetricsRenderer::render
     * @covers       \MrLinter\Metrics\Storage\PromPushGw\MetricsRenderer::renderHistograms
     * @covers       \MrLinter\Metrics\Storage\PromPushGw\MetricsRenderer::renderCounters
     * @covers       \MrLinter\Metrics\Storage\PromPushGw\MetricsRenderer::renderGauges
     *
     * @dataProvider providerForTestRender
     */
    public function testRender(Snapshot $snapshot, array $globalLabels, string $expectedContent): void
    {
        $renderer = new MetricsRenderer();

        self::assertEquals($expectedContent, $renderer->render($snapshot));
    }
}
