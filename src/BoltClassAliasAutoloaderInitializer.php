<?php

declare(strict_types=1);

namespace Tempest\Bolt;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Discovery\Composer;

use function Tempest\root_path;

final readonly class BoltClassAliasAutoloaderInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): BoltClassAliasAutoloader
    {
        return new BoltClassAliasAutoloader(fn (): array => $this->aliases($container->get(Composer::class)));
    }

    /**
     * @return array<string, class-string>
     */
    private function aliases(Composer $composer): array
    {
        $aliases = [];

        foreach ($composer->namespaces as $namespace) {
            $prefix = trim($namespace->namespace, '\\');
            $directory = rtrim(
                str_starts_with($namespace->path, '/') ? $namespace->path : root_path($namespace->path),
                '/',
            );

            if (! is_dir($directory)) {
                continue;
            }

            /** @var SplFileInfo $file */
            foreach ($this->phpFiles($directory) as $file) {
                $relative = substr($file->getPathname(), strlen($directory) + 1, -4);
                $fqcn = $prefix . '\\' . str_replace('/', '\\', $relative);

                $aliases[$file->getBasename('.php')] ??= $fqcn;
            }
        }

        return $aliases;
    }

    /**
     * @return iterable<SplFileInfo>
     */
    private function phpFiles(string $directory): iterable
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                yield $file;
            }
        }
    }
}
