<?php

declare(strict_types=1);

namespace Tempest\Bolt\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tempest\Bolt\BoltClassAliasAutoloader;
use Tempest\Bolt\BoltResult;

final class BoltClassAliasAutoloaderTest extends TestCase
{
    #[Test]
    public function it_aliases_a_short_name_to_the_fully_qualified_class(): void
    {
        $autoloader = new BoltClassAliasAutoloader(fn (): array => ['BoltShortAliasProbe' => BoltResult::class]);
        $autoloader->register();

        $this->assertTrue(class_exists('BoltShortAliasProbe'));
        $this->assertSame(BoltResult::class, (new ReflectionClass('BoltShortAliasProbe'))->getName());
    }

    #[Test]
    public function it_ignores_namespaced_class_names(): void
    {
        $autoloader = new BoltClassAliasAutoloader(fn (): array => ['Whatever' => BoltResult::class]);

        $autoloader->resolve('Some\\Namespaced\\Whatever');

        $this->assertFalse(class_exists('Some\\Namespaced\\Whatever', autoload: false));
    }

    #[Test]
    public function it_ignores_unknown_short_names(): void
    {
        $autoloader = new BoltClassAliasAutoloader(fn (): array => []);

        $autoloader->resolve('BoltUnknownAliasProbe');

        $this->assertFalse(class_exists('BoltUnknownAliasProbe', autoload: false));
    }
}
