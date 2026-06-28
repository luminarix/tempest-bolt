<?php

declare(strict_types=1);

namespace Tempest\Bolt;

use Psy\Configuration;
use Psy\Shell;
use Tempest\Core\Kernel;

final readonly class PsyshBoltShell implements BoltShell
{
    public function run(array $scopeVariables): int
    {
        $configuration = new Configuration();
        $configuration->setPrompt('bolt> ');
        $configuration->setStartupMessage(sprintf('Bolt shell for Tempest %s', Kernel::VERSION));

        $shell = new Shell($configuration);
        $shell->setScopeVariables($scopeVariables);

        return $shell->run();
    }
}
