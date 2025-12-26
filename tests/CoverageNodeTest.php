<?php

declare(strict_types=1);

namespace Mridang\PHPUnitReporters\Tests;

use Mridang\PHPUnitReporters\CoverageMetrics;
use Mridang\PHPUnitReporters\CoverageNode;
use Mridang\PHPUnitReporters\FileCoverage;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mridang\PHPUnitReporters\CoverageNode
 */
final class CoverageNodeTest extends TestCase
{
    public function testGetDepthReturnsZeroForRoot(): void
    {
        $root = new CoverageNode('root');

        $this->assertEquals(0, $root->getDepth());
    }

    public function testGetDepthReturnsCorrectDepthForNestedNodes(): void
    {
        $root = new CoverageNode('root');
        $child = new CoverageNode('child', $root);
        $grandchild = new CoverageNode('grandchild', $child);

        $this->assertEquals(0, $root->getDepth());
        $this->assertEquals(1, $child->getDepth());
        $this->assertEquals(2, $grandchild->getDepth());
    }

    public function testAggregateFromChildrenSumsMetricsCorrectly(): void
    {
        $root = new CoverageNode('root');

        $child1 = new CoverageNode('child1', $root);
        $child1->isFile = true;
        $child1->statements = new CoverageMetrics(10, 8);
        $child1->branches = new CoverageMetrics(5, 3);
        $child1->lines = new CoverageMetrics(20, 15);
        $root->children['child1'] = $child1;

        $child2 = new CoverageNode('child2', $root);
        $child2->isFile = true;
        $child2->statements = new CoverageMetrics(15, 12);
        $child2->branches = new CoverageMetrics(8, 6);
        $child2->lines = new CoverageMetrics(30, 25);
        $root->children['child2'] = $child2;

        $root->aggregateFromChildren();

        $this->assertEquals(25, $root->statements->total);
        $this->assertEquals(20, $root->statements->covered);
        $this->assertEquals(13, $root->branches->total);
        $this->assertEquals(9, $root->branches->covered);
        $this->assertEquals(50, $root->lines->total);
        $this->assertEquals(40, $root->lines->covered);
    }

    public function testAggregateFromChildrenRecursivelyAggregatesNestedNodes(): void
    {
        $root = new CoverageNode('root');
        $directory = new CoverageNode('src', $root);
        $root->children['src'] = $directory;

        $file = new CoverageNode('File.php', $directory);
        $file->isFile = true;
        $file->statements = new CoverageMetrics(10, 5);
        $file->branches = new CoverageMetrics(4, 2);
        $file->lines = new CoverageMetrics(15, 10);
        $directory->children['File.php'] = $file;

        $root->aggregateFromChildren();

        $this->assertEquals(10, $directory->statements->total);
        $this->assertEquals(5, $directory->statements->covered);
        $this->assertEquals(10, $root->statements->total);
        $this->assertEquals(5, $root->statements->covered);
    }

    public function testBuildTreeCreatesCorrectHierarchy(): void
    {
        $files = [
            '/project/src/Calculator.php' => new FileCoverage(
                new CoverageMetrics(5, 3),
                new CoverageMetrics(2, 1),
                new CoverageMetrics(7, 4),
                [11, 13, 14]
            ),
            '/project/src/StringUtils.php' => new FileCoverage(
                new CoverageMetrics(3, 3),
                new CoverageMetrics(1, 1),
                new CoverageMetrics(4, 4),
                []
            ),
        ];

        $tree = CoverageNode::buildTree($files);

        $this->assertArrayHasKey('src', $tree->children);

        $srcNode = $tree->children['src'];
        $this->assertFalse($srcNode->isFile);
        $this->assertArrayHasKey('Calculator.php', $srcNode->children);
        $this->assertArrayHasKey('StringUtils.php', $srcNode->children);

        $calculator = $srcNode->children['Calculator.php'];
        $this->assertTrue($calculator->isFile);
        $this->assertEquals(5, $calculator->statements->total);
        $this->assertEquals(3, $calculator->statements->covered);
        $this->assertEquals([11, 13, 14], $calculator->uncoveredLines);

        $stringUtils = $srcNode->children['StringUtils.php'];
        $this->assertTrue($stringUtils->isFile);
        $this->assertEquals(3, $stringUtils->statements->total);
        $this->assertEquals([], $stringUtils->uncoveredLines);
    }

    public function testBuildTreeAggregatesMetricsToRoot(): void
    {
        $files = [
            '/project/src/Calculator.php' => new FileCoverage(
                new CoverageMetrics(5, 3),
                new CoverageMetrics(2, 1),
                new CoverageMetrics(7, 4),
                []
            ),
            '/project/src/StringUtils.php' => new FileCoverage(
                new CoverageMetrics(3, 3),
                new CoverageMetrics(1, 1),
                new CoverageMetrics(4, 4),
                []
            ),
        ];

        $tree = CoverageNode::buildTree($files);

        $this->assertEquals(8, $tree->statements->total);
        $this->assertEquals(6, $tree->statements->covered);
        $this->assertEquals(3, $tree->branches->total);
        $this->assertEquals(2, $tree->branches->covered);
        $this->assertEquals(11, $tree->lines->total);
        $this->assertEquals(8, $tree->lines->covered);
    }

    public function testBuildTreeHandlesSingleFile(): void
    {
        $files = [
            '/project/src/Calculator.php' => new FileCoverage(
                new CoverageMetrics(5, 3),
                new CoverageMetrics(2, 1),
                new CoverageMetrics(7, 4),
                [11]
            ),
        ];

        $tree = CoverageNode::buildTree($files);

        $this->assertArrayHasKey('src', $tree->children);
        $srcNode = $tree->children['src'];
        $this->assertArrayHasKey('Calculator.php', $srcNode->children);
    }

    public function testBuildTreeHandlesMultipleDirectoryLevels(): void
    {
        $files = [
            '/project/src/Utils/StringHelper.php' => new FileCoverage(
                new CoverageMetrics(5, 4),
                new CoverageMetrics(2, 2),
                new CoverageMetrics(7, 6),
                []
            ),
            '/project/src/Models/User.php' => new FileCoverage(
                new CoverageMetrics(10, 8),
                new CoverageMetrics(4, 3),
                new CoverageMetrics(15, 12),
                [5, 10]
            ),
        ];

        $tree = CoverageNode::buildTree($files);

        $this->assertArrayHasKey('src', $tree->children);
        $srcNode = $tree->children['src'];

        $this->assertArrayHasKey('Utils', $srcNode->children);
        $this->assertArrayHasKey('Models', $srcNode->children);

        $utilsNode = $srcNode->children['Utils'];
        $this->assertArrayHasKey('StringHelper.php', $utilsNode->children);

        $modelsNode = $srcNode->children['Models'];
        $this->assertArrayHasKey('User.php', $modelsNode->children);
    }

    public function testBuildTreeHandlesEmptyArray(): void
    {
        $tree = CoverageNode::buildTree([]);

        $this->assertEmpty($tree->children);
        $this->assertEquals(0, $tree->statements->total);
    }
}
