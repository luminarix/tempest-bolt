<?php

declare(strict_types=1);

namespace Tempest\Bolt;

interface BoltShell
{
    /**
     * @param array<string, mixed> $scopeVariables
     */
    public function run(array $scopeVariables): int;
}
