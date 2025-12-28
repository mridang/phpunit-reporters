<?php

declare(strict_types=1);

namespace Mridang\PHPUnitReporters;

use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

final class JestReporterExtension implements Extension
{
    #[\Override]
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $cloverPath = $parameters->has('cloverPath')
            ? $parameters->get('cloverPath')
            : 'build/coverage/clover.xml';

        $facade->registerSubscribers(
            new JestReporterSubscriber($cloverPath)
        );
    }
}
