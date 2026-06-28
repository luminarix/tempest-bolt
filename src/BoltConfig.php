<?php

declare(strict_types=1);

namespace Tempest\Bolt;

use Closure;
use Tempest\Core\Environment;
use Tempest\Http\Request;

final class BoltConfig
{
    /**
     * @param bool $enabled Master switch for the web panel.
     * @param array<Environment> $environments Environments in which the panel is reachable. Defaults to local only.
     * @param null|Closure(Request): bool $authorize Optional app-supplied gate. Return false to deny access (403).
     * @param bool $persistScope Whether variables defined in the panel persist across executions within a session.
     */
    public function __construct(
        public bool $enabled = true,
        public array $environments = [Environment::LOCAL],
        public ?Closure $authorize = null,
        public bool $persistScope = false,
    ) {}
}
