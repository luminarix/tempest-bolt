<?php

declare(strict_types=1);

namespace Tempest\Bolt;

final readonly class BoltResult
{
    /**
     * @param string $output Everything the snippet echoed or printed.
     * @param string|null $result A printable representation of the last expression's value.
     * @param string|null $error The error message, if execution failed.
     * @param array<string, mixed> $scope The scope after execution, including any variables the snippet defined.
     */
    public function __construct(
        public string $output,
        public ?string $result,
        public ?string $error,
        public array $scope,
    ) {}
}
