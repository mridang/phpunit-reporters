<?php

declare(strict_types=1);

namespace Mridang\PHPUnitReporters;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;

class JestReporter
{
    private const int TAB_SIZE = 2;
    private const int TABLE_BORDER_PADDING = 16;
    private const int PERCENTAGE_COLUMNS_WIDTH = 24;

    private string $cloverReportPath;
    private int $maxUncoveredWidth = 0;

    public function __construct(
        private readonly OutputInterface $output = new ConsoleOutput(),
        private readonly ?int $terminalWidth = null
    ) {
    }

    public function printCoverageSummaryExternal(string $cloverReportPath): void
    {
        $this->cloverReportPath = $cloverReportPath;
        $this->printCoverageSummary();
    }

    public function printCoverageSummary(): void
    {
        $this->output->writeln('');

        if (!file_exists($this->cloverReportPath)) {
            $this->output->writeln(
                'Code coverage report not found at ' . $this->cloverReportPath
            );
            return;
        }

        $report = new CoverageReport($this->cloverReportPath);
        $files = $report->getFiles();

        if (empty($files)) {
            $this->output->writeln('No coverage data found.');
            return;
        }

        $tree = CoverageNode::buildTree($files);

        $this->calculateMaxUncoveredWidth($tree);

        $table = new Table($this->output);
        $table->setHeaders([
            'File',
            '% Stmts',
            '% Branch',
            '% Lines',
            'Uncovered Line #s',
        ]);

        $this->addNodeToTable($table, $tree, true);

        $table->render();
    }

    private function addNodeToTable(
        Table $table,
        CoverageNode $node,
        bool $isRoot = false
    ): void {
        $depth = $node->getDepth();

        if ($isRoot) {
            $table->addRow([
                'All files',
                $this->formatPercentage($node->statements),
                $this->formatPercentage($node->branches),
                $this->formatPercentage($node->lines),
                '',
            ]);
            $table->addRow(new TableSeparator());
        } else {
            $indent = str_repeat(' ', ($depth - 1) * self::TAB_SIZE);
            $name = $indent . $node->name;

            $table->addRow([
                $name,
                $this->formatPercentage($node->statements),
                $this->formatPercentage($node->branches),
                $this->formatPercentage($node->lines),
                $node->isFile
                    ? $this->formatUncoveredLines($node->uncoveredLines)
                    : '',
            ]);
        }

        $children = $node->children;
        uasort($children, function (CoverageNode $a, CoverageNode $b): int {
            if ($a->isFile !== $b->isFile) {
                return $a->isFile ? 1 : -1;
            }
            return strcasecmp($a->name, $b->name);
        });

        foreach ($children as $child) {
            $this->addNodeToTable($table, $child);
        }
    }

    private function formatPercentage(CoverageMetrics $metrics): string
    {
        if ($metrics->total === 0) {
            return '-';
        }

        $percentage = $metrics->getPercentage();
        $color = 'green';
        if ($percentage < 50) {
            $color = 'red';
        } elseif ($percentage < 80) {
            $color = 'yellow';
        }

        return sprintf(
            '<fg=%s>%s</>',
            $color,
            number_format($percentage, 2)
        );
    }

    /** @param list<int> $lines */
    private function formatUncoveredLines(array $lines): string
    {
        if (empty($lines)) {
            return '';
        }

        sort($lines);
        $ranges = [];
        $start = $lines[0];
        $end = $start;

        for ($i = 1; $i < count($lines); $i++) {
            if ($lines[$i] === $end + 1) {
                $end = $lines[$i];
            } else {
                $ranges[] = $start === $end
                    ? (string) $start
                    : $start . '-' . $end;
                $start = $lines[$i];
                $end = $start;
            }
        }
        $ranges[] = $start === $end ? (string) $start : $start . '-' . $end;

        $result = implode(',', $ranges);

        if ($this->maxUncoveredWidth > 0 && strlen($result) > $this->maxUncoveredWidth) {
            return substr($result, 0, $this->maxUncoveredWidth - 3) . '...';
        }

        return $result;
    }

    private function calculateMaxUncoveredWidth(CoverageNode $tree): void
    {
        $terminalWidth = $this->terminalWidth ?? (new Terminal())->getWidth();

        if ($terminalWidth <= 0) {
            $this->maxUncoveredWidth = 0;
            return;
        }

        $maxFileWidth = $this->getMaxFileWidth($tree, 0);
        $maxFileWidth = max($maxFileWidth, strlen('All files'));

        $fixedWidth = self::TABLE_BORDER_PADDING
            + $maxFileWidth
            + self::PERCENTAGE_COLUMNS_WIDTH;

        $availableWidth = $terminalWidth - $fixedWidth;

        $this->maxUncoveredWidth = max(0, $availableWidth);
    }

    private function getMaxFileWidth(CoverageNode $node, int $depth): int
    {
        $indent = ($depth > 0) ? ($depth - 1) * self::TAB_SIZE : 0;
        $maxWidth = $indent + strlen($node->name);

        foreach ($node->children as $child) {
            $childWidth = $this->getMaxFileWidth($child, $depth + 1);
            $maxWidth = max($maxWidth, $childWidth);
        }

        return $maxWidth;
    }
}
