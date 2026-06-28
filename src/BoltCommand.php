<?php

declare(strict_types=1);

namespace Tempest\Bolt;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\ExitCode;

final readonly class BoltCommand
{
    public function __construct(
        private BoltShell $shell,
        private BoltScope $scope,
        private BoltClassAliasAutoloader $aliasAutoloader,
    ) {}

    #[ConsoleCommand(
        name: 'bolt',
        description: 'Start an interactive shell for the current Tempest application',
    )]
    public function __invoke(): ExitCode
    {
        $this->aliasAutoloader->register();

        $exitCode = $this->shell->run($this->scope->variables());

        return ExitCode::tryFrom($exitCode) ?? ExitCode::ERROR;
    }
}
