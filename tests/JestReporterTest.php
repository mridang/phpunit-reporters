<?php

declare(strict_types=1);

namespace Mridang\PHPUnitReporters\Tests;

use Mridang\PHPUnitReporters\JestReporter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mridang\PHPUnitReporters\JestReporter
 */
final class JestReporterTest extends TestCase
{
    private string $mockCloverReportPath;
    private MockOutput $mockOutput;

    #[\Override]
    protected function setUp(): void
    {
        $this->mockCloverReportPath = __DIR__ . '/fixtures/clover.xml';
        $this->mockOutput = new MockOutput();
    }

    public function testCoverageSummaryDisplaysHeaders(): void
    {
        $reporter = new JestReporter($this->mockOutput);
        $reporter->printCoverageSummaryExternal($this->mockCloverReportPath);

        $output = $this->mockOutput->getOutput();

        $this->assertStringContainsString('File', $output);
        $this->assertStringContainsString('% Stmts', $output);
        $this->assertStringContainsString('% Branch', $output);
        $this->assertStringContainsString('% Lines', $output);
        $this->assertStringContainsString('Uncovered Line #s', $output);
    }

    public function testCoverageSummaryDisplaysAllFilesSummaryRow(): void
    {
        $reporter = new JestReporter($this->mockOutput);
        $reporter->printCoverageSummaryExternal($this->mockCloverReportPath);

        $output = $this->mockOutput->getOutput();

        $this->assertStringContainsString('All files', $output);
        $this->assertStringContainsString('75.00', $output);
        $this->assertStringContainsString('66.67', $output);
        $this->assertStringContainsString('72.73', $output);
    }

    public function testCoverageSummaryDisplaysDirectoryStructure(): void
    {
        $reporter = new JestReporter($this->mockOutput);
        $reporter->printCoverageSummaryExternal($this->mockCloverReportPath);

        $output = $this->mockOutput->getOutput();

        $this->assertStringContainsString('src', $output);
    }

    public function testCoverageSummaryDisplaysFiles(): void
    {
        $reporter = new JestReporter($this->mockOutput);
        $reporter->printCoverageSummaryExternal($this->mockCloverReportPath);

        $output = $this->mockOutput->getOutput();

        $this->assertStringContainsString('Calculator.php', $output);
        $this->assertStringContainsString('StringUtils.php', $output);
    }

    public function testCoverageSummaryDisplaysFilePercentages(): void
    {
        $reporter = new JestReporter($this->mockOutput);
        $reporter->printCoverageSummaryExternal($this->mockCloverReportPath);

        $output = $this->mockOutput->getOutput();

        $this->assertStringContainsString('60.00', $output);
        $this->assertStringContainsString('50.00', $output);
        $this->assertStringContainsString('57.14', $output);
        $this->assertStringContainsString('100.00', $output);
    }

    public function testCoverageSummaryDisplaysUncoveredLineRanges(): void
    {
        $reporter = new JestReporter($this->mockOutput);
        $reporter->printCoverageSummaryExternal($this->mockCloverReportPath);

        $output = $this->mockOutput->getOutput();

        $this->assertStringContainsString('11,13-14', $output);
    }

    public function testCoverageSummaryHandlesMissingReportFile(): void
    {
        $reporter = new JestReporter($this->mockOutput);
        $reporter->printCoverageSummaryExternal('/non/existent/clover.xml');

        $output = $this->mockOutput->getOutput();

        $this->assertStringContainsString('Code coverage report not found', $output);
    }

    public function testUncoveredLinesTruncatedWithEllipsisWhenExceedingTerminalWidth(): void
    {
        $manyUncoveredPath = __DIR__ . '/fixtures/clover-many-uncovered.xml';
        $reporter = new JestReporter($this->mockOutput, 80);
        $reporter->printCoverageSummaryExternal($manyUncoveredPath);

        $output = $this->mockOutput->getOutput();

        $this->assertStringContainsString('...', $output);
        $this->assertStringNotContainsString('100', $output);
    }

    public function testUncoveredLinesNotTruncatedWhenTerminalWidthSufficient(): void
    {
        $reporter = new JestReporter($this->mockOutput, 200);
        $reporter->printCoverageSummaryExternal($this->mockCloverReportPath);

        $output = $this->mockOutput->getOutput();

        $this->assertStringContainsString('11,13-14', $output);
        $this->assertStringNotContainsString('...', $output);
    }
}
