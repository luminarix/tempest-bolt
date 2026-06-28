<?php

declare(strict_types=1);

namespace Tempest\Bolt\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Bolt\BoltShell;
use Tempest\Bolt\BoltShellInitializer;
use Tempest\Bolt\PsyshBoltShell;
use Tempest\Container\GenericContainer;

final class BoltShellInitializerTest extends TestCase
{
    #[Test]
    public function it_initializes_the_default_shell_boundary(): void
    {
        $shell = (new BoltShellInitializer())->initialize(new GenericContainer());

        $this->assertInstanceOf(BoltShell::class, $shell);
        $this->assertInstanceOf(PsyshBoltShell::class, $shell);
    }
}
