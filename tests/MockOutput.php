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

    public function setVerbosity(int $level): void
    {
    }

    public function getVerbosity(): int
    {
        return self::VERBOSITY_NORMAL;
    }

    public function isQuiet(): bool
    {
        return false;
    }

    public function isVerbose(): bool
    {
        return false;
    }

    public function isVeryVerbose(): bool
    {
        return false;
    }

    public function isDebug(): bool
    {
        return false;
    }

    public function isDecorated(): bool
    {
        return false;
    }

    public function setDecorated(bool $decorated): void
    {
    }

    public function setFormatter(OutputFormatterInterface $formatter): void
    {
        $this->formatter = $formatter;
    }

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
