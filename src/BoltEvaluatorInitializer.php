<?php

declare(strict_types=1);

namespace Tempest\Bolt;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final readonly class BoltEvaluatorInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): BoltEvaluator
    {
        return new PsyshBoltEvaluator();
    }
}
