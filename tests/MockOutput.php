<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace Mridang\PHPUnitReporters\Tests;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MockOutput implements OutputInterface
{
    private string $output = '';
    private ?OutputFormatterInterface $formatter = null;

    /**
     * @param string|iterable<string> $messages
     * @param bool $newline
     * @param int $options
     * @return void
     * @noinspection PhpDocSignatureInspection
     */
    #[\Override]
    public function write(string|iterable $messages, bool $newline = false, int $options = self::OUTPUT_NORMAL): void
    {
        if (is_iterable($messages)) {
            foreach ($messages as $message) {
                $this->output .= $message;
            }
        } else {
            $this->output .= $messages;
        }
        if ($newline) {
            $this->output .= PHP_EOL;
        }
    }

    /**
     * @param string|iterable<string> $messages
     * @param int $options
     * @noinspection PhpDocSignatureInspection
     */
    #[\Override]
    public function writeln(string|iterable $messages, int $options = self::OUTPUT_NORMAL): void
    {
        if (is_iterable($messages)) {
            foreach ($messages as $message) {
                $this->output .= $message . PHP_EOL;
            }
        } else {
            $this->output .= $messages . PHP_EOL;
        }
    }

    #[\Override]
    public function setVerbosity(int $level): void
    {
    }

    #[\Override]
    public function getVerbosity(): int
    {
        return self::VERBOSITY_NORMAL;
    }

    #[\Override]
    public function isQuiet(): bool
    {
        return false;
    }

    #[\Override]
    public function isVerbose(): bool
    {
        return false;
    }

    #[\Override]
    public function isVeryVerbose(): bool
    {
        return false;
    }

    #[\Override]
    public function isDebug(): bool
    {
        return false;
    }

    #[\Override]
    public function isDecorated(): bool
    {
        return false;
    }

    #[\Override]
    public function setDecorated(bool $decorated): void
    {
    }

    #[\Override]
    public function setFormatter(OutputFormatterInterface $formatter): void
    {
        $this->formatter = $formatter;
    }

    #[\Override]
    public function getFormatter(): OutputFormatterInterface
    {
        if ($this->formatter === null) {
            $this->formatter = new MockOutputFormatter();
        }
        return $this->formatter;
    }

    public function getOutput(): string
    {
        return $this->output;
    }
}
