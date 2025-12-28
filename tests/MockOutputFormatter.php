<?php

/** @noinspection PhpUnused */
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
    #[\Override]
    public function setDecorated(bool $decorated): void
    {
    }

    #[\Override]
    public function isDecorated(): bool
    {
        return false;
    }

    #[\Override]
    public function setStyle(string $name, OutputFormatterStyleInterface $style): void
    {
    }

    #[\Override]
    public function hasStyle(string $name): bool
    {
        return false;
    }

    #[\Override]
    public function getStyle(string $name): OutputFormatterStyleInterface
    {
        throw new RuntimeException('No style defined');
    }

    #[\Override]
    public function format(?string $message): ?string
    {
        return $message;
    }
}
