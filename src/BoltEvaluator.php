<?php

declare(strict_types=1);

namespace Tempest\Bolt;

interface BoltEvaluator
{
    /**
     * Evaluate a snippet of PHP code within the given scope.
     *
     * @param array<string, mixed> $scope Variables made available to the code.
     */
    public function evaluate(string $code, array $scope): BoltResult;
}
