<?php /** @noinspection PhpUnused */
/** @noinspection PhpUnused */
/** @noinspection PhpUnused */
/** @noinspection PhpUnused */
/** @noinspection PhpUnused */
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace Mridang\PHPUnitReporters\Tests;

use RuntimeException;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyleInterface;

class MockOutputFormatter implements OutputFormatterInterface
{
    public function setDecorated(bool $decorated): void
    {
    }

    public function isDecorated(): bool
    {
        return false;
    }

    public function setStyle(string $name, OutputFormatterStyleInterface $style): void
    {
    }

    public function hasStyle(string $name): bool
    {
        return false;
    }

    public function getStyle(string $name): OutputFormatterStyleInterface
    {
        throw new RuntimeException('No style defined');
    }

    public function format(?string $message): ?string
    {
        return $message;
    }
}
