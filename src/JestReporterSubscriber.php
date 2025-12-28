<?php

declare(strict_types=1);

namespace Mridang\PHPUnitReporters;

use PHPUnit\Event\Application\Finished;
use PHPUnit\Event\Application\FinishedSubscriber;

final readonly class JestReporterSubscriber implements FinishedSubscriber
{
    public function __construct(private string $cloverPath)
    {
    }

    #[\Override]
    public function notify(Finished $event): void
    {
        $reporter = new JestReporter();
        $reporter->printCoverageSummaryExternal($this->cloverPath);
    }
}
