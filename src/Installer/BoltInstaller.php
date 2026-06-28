<?php

declare(strict_types=1);

namespace Tempest\Bolt\Installer;

use Tempest\Core\Installer;
use Tempest\Core\PublishesFiles;

use function Tempest\src_path;

final class BoltInstaller
{
    use PublishesFiles;

    #[Installer('Bolt', alias: 'bolt')]
    public function install(): void
    {
        $this->publish(
            source: __DIR__ . '/bolt.config.stub.php',
            destination: src_path('bolt.config.php'),
        );

        $this->publishImports();
    }
}
