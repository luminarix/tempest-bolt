<?php

declare(strict_types=1);

namespace Tempest\Bolt;

use Closure;

final class BoltClassAliasAutoloader
{
    private bool $registered = false;

    /** @var array<string, class-string>|null */
    private ?array $aliases = null;

    /**
     * @param Closure(): array<string, class-string> $aliasFactory Resolves short class name => fully-qualified name.
     */
    public function __construct(
        private readonly Closure $aliasFactory,
    ) {}

    public function register(): void
    {
        if ($this->registered) {
            return;
        }

        $this->registered = true;

        spl_autoload_register($this->resolve(...));
    }

    public function resolve(string $class): void
    {
        if (str_contains($class, '\\')) {
            return;
        }

        $this->aliases ??= ($this->aliasFactory)();

        $target = $this->aliases[$class] ?? null;

        if ($target === null) {
            return;
        }

        if (class_exists($target) || interface_exists($target) || enum_exists($target)) {
            class_alias($target, $class);
        }
    }
}
