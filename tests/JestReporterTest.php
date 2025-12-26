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
        $this->assertStringContainsString('75.00', $output); // Total statements
        $this->assertStringContainsString('66.67', $output); // Total branches
        $this->assertStringContainsString('72.73', $output); // Total lines
    }

    public function testCoverageSummaryDisplaysDirectoryStructure(): void
    {
        $reporter = new JestReporter($this->mockOutput);
        $reporter->printCoverageSummaryExternal($this->mockCloverReportPath);

        $output = $this->mockOutput->getOutput();

        // Should show the 'src' directory (the directory containing the files)
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

        $this->assertStringContainsString('60.00', $output); // Statements for Calculator.php
        $this->assertStringContainsString('50.00', $output); // Branches for Calculator.php
        $this->assertStringContainsString('57.14', $output); // Lines for Calculator.php
        $this->assertStringContainsString('100.00', $output); // Full coverage for StringUtils.php
    }

    public function testCoverageSummaryDisplaysUncoveredLineRanges(): void
    {
        $reporter = new JestReporter($this->mockOutput);
        $reporter->printCoverageSummaryExternal($this->mockCloverReportPath);

        $output = $this->mockOutput->getOutput();

        $this->assertStringContainsString('11,13-14', $output); // Uncovered lines for Calculator.php
    }

    public function testCoverageSummaryHandlesMissingReportFile(): void
    {
        $reporter = new JestReporter($this->mockOutput);
        $reporter->printCoverageSummaryExternal('/non/existent/clover.xml');

        $output = $this->mockOutput->getOutput();

        $this->assertStringContainsString('Code coverage report not found', $output);
    }
}
