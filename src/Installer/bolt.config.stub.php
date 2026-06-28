<?php

declare(strict_types=1);

use Tempest\Bolt\BoltConfig;
use Tempest\Core\Environment;

return new BoltConfig(
    enabled: true,
    environments: [Environment::LOCAL],
    authorize: null,
    persistScope: false,
);
