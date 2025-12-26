<?php

declare(strict_types=1);

namespace Mridang\PHPUnitReporters\Tests;

use InvalidArgumentException;
use Mridang\PHPUnitReporters\CoverageReport;
use Mridang\PHPUnitReporters\FileCoverage;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @covers \Mridang\PHPUnitReporters\CoverageReport
 */
final class CoverageReportTest extends TestCase
{
    private string $mockCloverReportPath;

    protected function setUp(): void
    {
        $this->mockCloverReportPath = __DIR__ . '/fixtures/clover.xml';
    }

    public function testConstructorThrowsExceptionForNonExistentFile(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Clover report file does not exist');
        new CoverageReport('/non/existent/path/clover.xml');
    }

    public function testConstructorThrowsExceptionForInvalidXml(): void
    {
        $invalidXmlPath = sys_get_temp_dir() . '/invalid_clover.xml';
        file_put_contents($invalidXmlPath, 'not valid xml <<<<');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to parse clover.xml');

        try {
            new CoverageReport($invalidXmlPath);
        } finally {
            @unlink($invalidXmlPath);
        }
    }

    public function testGetFilesReturnsCorrectData(): void
    {
        $report = new CoverageReport($this->mockCloverReportPath);
        $files = $report->getFiles();

        $this->assertArrayHasKey('/path/to/project/src/Calculator.php', $files);
        $this->assertArrayHasKey('/path/to/project/src/StringUtils.php', $files);

        $calculator = $files['/path/to/project/src/Calculator.php'];
        $this->assertInstanceOf(FileCoverage::class, $calculator);
        $this->assertEquals(5, $calculator->statements->total);
        $this->assertEquals(3, $calculator->statements->covered);
        $this->assertEquals(2, $calculator->branches->total);
        $this->assertEquals(1, $calculator->branches->covered);
        $this->assertEquals(7, $calculator->lines->total);
        $this->assertEquals(4, $calculator->lines->covered);
        $this->assertEquals([11, 13, 14], $calculator->uncoveredLines);

        $stringUtils = $files['/path/to/project/src/StringUtils.php'];
        $this->assertInstanceOf(FileCoverage::class, $stringUtils);
        $this->assertEquals(3, $stringUtils->statements->total);
        $this->assertEquals(3, $stringUtils->statements->covered);
        $this->assertEquals(1, $stringUtils->branches->total);
        $this->assertEquals(1, $stringUtils->branches->covered);
        $this->assertEquals(4, $stringUtils->lines->total);
        $this->assertEquals(4, $stringUtils->lines->covered);
        $this->assertEquals([], $stringUtils->uncoveredLines);
    }

    public function testGetTotalCoverageReturnsCorrectData(): void
    {
        $report = new CoverageReport($this->mockCloverReportPath);
        $totals = $report->getTotalCoverage();

        $this->assertEquals(8, $totals->statements->total);
        $this->assertEquals(6, $totals->statements->covered);
        $this->assertEquals(3, $totals->branches->total);
        $this->assertEquals(2, $totals->branches->covered);
        $this->assertEquals(11, $totals->lines->total);
        $this->assertEquals(8, $totals->lines->covered);
    }
}
