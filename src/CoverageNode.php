<?php

declare(strict_types=1);

namespace Mridang\PHPUnitReporters;

use const DIRECTORY_SEPARATOR;

/**
 * A tree node representing a directory or file in the coverage hierarchy.
 *
 * This class builds a hierarchical tree structure from flat file coverage
 * data, enabling display of coverage metrics at both file and directory
 * levels, similar to Istanbul's text reporter.
 */
final class CoverageNode
{
    /** @var bool Whether this node represents a file (true) or directory. */
    public bool $isFile = false;

    /** @var array<string, CoverageNode> Child nodes keyed by name. */
    public array $children = [];

    /** @var CoverageMetrics Statement coverage for this node. */
    public CoverageMetrics $statements;

    /** @var CoverageMetrics Branch/method coverage for this node. */
    public CoverageMetrics $branches;

    /** @var CoverageMetrics Line coverage for this node. */
    public CoverageMetrics $lines;

    /** @var list<int> Uncovered line numbers (only for file nodes). */
    public array $uncoveredLines = [];

    /**
     * Creates a new coverage node.
     *
     * @param string            $name   The node name (file or directory).
     * @param CoverageNode|null $parent The parent node, or null for root.
     */
    public function __construct(
        public string $name,
        public ?CoverageNode $parent = null,
    ) {
        $this->statements = CoverageMetrics::zero();
        $this->branches = CoverageMetrics::zero();
        $this->lines = CoverageMetrics::zero();
    }

    /**
     * Calculates the depth of this node in the tree.
     *
     * @return int The depth (0 for root, 1 for first level, etc.).
     */
    public function getDepth(): int
    {
        $depth = 0;
        $node = $this->parent;
        while ($node !== null) {
            $depth++;
            $node = $node->parent;
        }
        return $depth;
    }

    /**
     * Aggregates coverage metrics from all child nodes.
     *
     * Recursively traverses the tree, summing metrics from children into
     * parent nodes. File nodes are skipped as they already have metrics.
     */
    public function aggregateFromChildren(): void
    {
        if ($this->isFile) {
            return;
        }

        $this->statements = CoverageMetrics::zero();
        $this->branches = CoverageMetrics::zero();
        $this->lines = CoverageMetrics::zero();

        foreach ($this->children as $child) {
            $child->aggregateFromChildren();

            $this->statements = $this->statements->add($child->statements);
            $this->branches = $this->branches->add($child->branches);
            $this->lines = $this->lines->add($child->lines);
        }
    }

    /**
     * Builds a coverage tree from a flat map of file coverage data.
     *
     * Creates a hierarchical tree structure where directories contain files
     * and subdirectories. The common base path is removed, but one level of
     * directory structure is preserved for display.
     *
     * @param array<string, FileCoverage> $files Map of absolute paths to
     *                                           coverage data.
     *
     * @return self The root node of the coverage tree.
     */
    public static function buildTree(array $files): self
    {
        $root = new self('');

        $paths = array_keys($files);
        $basePath = self::findCommonPath($paths);

        foreach ($files as $absolutePath => $coverage) {
            $relativePath = $basePath !== ''
                ? ltrim(substr($absolutePath, strlen($basePath)), DIRECTORY_SEPARATOR)
                : $absolutePath;

            $parts = explode(DIRECTORY_SEPARATOR, $relativePath);
            $current = $root;

            foreach ($parts as $part) {
                if (!isset($current->children[$part])) {
                    $current->children[$part] = new self($part, $current);
                }
                $current = $current->children[$part];
            }

            $current->isFile = true;
            $current->statements = $coverage->statements;
            $current->branches = $coverage->branches;
            $current->lines = $coverage->lines;
            $current->uncoveredLines = $coverage->uncoveredLines;
        }

        $root->aggregateFromChildren();

        return $root;
    }

    /**
     * Finds the common base path for all file paths.
     *
     * Returns the parent of the deepest common directory to preserve at
     * least one level of directory structure in the output.
     *
     * @param list<string> $paths List of absolute file paths.
     *
     * @return string The common base path, or empty string if none.
     */
    private static function findCommonPath(array $paths): string
    {
        if (empty($paths)) {
            return '';
        }

        if (count($paths) === 1) {
            $parent = dirname($paths[0]);
            return dirname($parent);
        }

        $parts = array_map(
            fn (string $path) => explode(DIRECTORY_SEPARATOR, dirname($path)),
            $paths
        );

        $common = [];
        $minLength = min(array_map('count', $parts));

        for ($i = 0; $i < $minLength; $i++) {
            $segment = $parts[0][$i];
            foreach ($parts as $pathParts) {
                if ($pathParts[$i] !== $segment) {
                    break 2;
                }
            }
            $common[] = $segment;
        }

        if (count($common) > 0) {
            array_pop($common);
        }

        return implode(DIRECTORY_SEPARATOR, $common);
    }
}
