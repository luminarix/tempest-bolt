<?php

declare(strict_types=1);

namespace Tempest\Bolt;

use Tempest\Container\Container;
use Tempest\Core\Kernel;

final readonly class BoltScope
{
    public function __construct(
        private Container $container,
        private Kernel $kernel,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function variables(): array
    {
        return [
            'app' => $this->container,
            'container' => $this->container,
            'kernel' => $this->kernel,
            'rootPath' => $this->kernel->root,
            'internalStorage' => $this->kernel->internalStorage,
            'tempestVersion' => Kernel::VERSION,
        ];
    }
}
