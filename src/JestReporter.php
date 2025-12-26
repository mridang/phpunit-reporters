<?php

declare(strict_types=1);

namespace Mridang\PHPUnitReporters;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Renders code coverage in Jest/Istanbul's console table format.
 *
 * This reporter displays coverage metrics in a hierarchical table showing
 * directories and files with their statement, branch, and line coverage
 * percentages, plus uncovered line numbers for each file.
 */
class JestReporter
{
    /** @var int Number of spaces per indentation level. */
    private const TAB_SIZE = 2;

    /** @var string Path to the clover.xml report file. */
    private string $cloverReportPath;

    /**
     * Creates a new Jest-style coverage reporter.
     *
     * @param OutputInterface $output The output interface for rendering.
     */
    public function __construct(
        private readonly OutputInterface $output = new ConsoleOutput()
    ) {
    }

    /**
     * Prints coverage summary from an external clover report path.
     *
     * @param string $cloverReportPath Path to the clover.xml file.
     */
    public function printCoverageSummaryExternal(string $cloverReportPath): void
    {
        $this->cloverReportPath = $cloverReportPath;
        $this->printCoverageSummary();
    }

    /**
     * Prints the coverage summary table to the output.
     *
     * Parses the clover.xml report, builds a hierarchical tree of coverage
     * nodes, and renders them as a formatted table with color-coded
     * percentages.
     */
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

    /**
     * Recursively adds a coverage node and its children to the table.
     *
     * @param Table        $table  The table to add rows to.
     * @param CoverageNode $node   The coverage node to render.
     * @param bool         $isRoot Whether this is the root node.
     */
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

    /**
     * Formats coverage metrics as a color-coded percentage string.
     *
     * Colors: green >= 80%, yellow >= 50%, red < 50%.
     *
     * @param CoverageMetrics $metrics The metrics to format.
     *
     * @return string Formatted percentage with ANSI color codes.
     */
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

    /**
     * Formats uncovered line numbers as compressed ranges.
     *
     * Consecutive lines are grouped into ranges (e.g., "1-5,10,15-20").
     *
     * @param list<int> $lines The uncovered line numbers.
     *
     * @return string Comma-separated ranges or empty string if none.
     */
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

        return implode(',', $ranges);
    }
}
