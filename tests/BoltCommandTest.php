<?php

declare(strict_types=1);

namespace Tempest\Bolt\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Tempest\Bolt\BoltClassAliasAutoloader;
use Tempest\Bolt\BoltCommand;
use Tempest\Bolt\BoltScope;
use Tempest\Bolt\BoltShell;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ExitCode;
use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use Tempest\Core\Kernel;

final class BoltCommandTest extends TestCase
{
    #[Test]
    public function it_registers_the_bolt_command(): void
    {
        $attribute = (new ReflectionMethod(BoltCommand::class, '__invoke'))
            ->getAttributes(ConsoleCommand::class)[0]
            ->newInstance();

        $this->assertSame('bolt', $attribute->getName());
        $this->assertSame('Start an interactive shell for the current Tempest application', $attribute->description);
    }

    #[Test]
    public function it_starts_the_shell_with_tempest_scope_variables(): void
    {
        $container = new GenericContainer();
        $kernel = new FakeKernel('/project', '/project/.tempest', $container);
        $shell = new FakeBoltShell(exitCode: 7);

        $exitCode = (new BoltCommand(
            shell: $shell,
            scope: new BoltScope($container, $kernel),
            aliasAutoloader: new BoltClassAliasAutoloader(fn (): array => []),
        ))->__invoke();

        $this->assertSame(ExitCode::ERROR, $exitCode);
        $this->assertSame($container, $shell->scopeVariables['app']);
        $this->assertSame($container, $shell->scopeVariables['container']);
        $this->assertSame($kernel, $shell->scopeVariables['kernel']);
        $this->assertSame('/project', $shell->scopeVariables['rootPath']);
        $this->assertSame(Kernel::VERSION, $shell->scopeVariables['tempestVersion']);
    }
}

final class FakeBoltShell implements BoltShell
{
    public array $scopeVariables = [];

    public function __construct(
        private readonly int $exitCode,
    ) {}

    public function run(array $scopeVariables): int
    {
        $this->scopeVariables = $scopeVariables;

        return $this->exitCode;
    }
}

final class FakeKernel implements Kernel
{
    public function __construct(
        public string $root,
        public string $internalStorage,
        public Container $container,
    ) {}

    public static function boot(
        string $root,
        array $discoveryLocations = [],
        ?Container $container = null,
        ?string $internalStorage = null,
    ): self {
        return new self(
            root: $root,
            internalStorage: $internalStorage ?? $root . '/.tempest',
            container: $container ?? new GenericContainer(),
        );
    }

    public function shutdown(int|string $status = ''): never
    {
        exit($status);
    }
}
