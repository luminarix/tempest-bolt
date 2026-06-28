<?php

declare(strict_types=1);

namespace Tempest\Bolt;

use Tempest\Core\Environment;
use Tempest\Http\ContentType;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Responses\Forbidden;
use Tempest\Http\Responses\Json;
use Tempest\Http\Responses\NotFound;
use Tempest\Http\Responses\Ok;
use Tempest\Core\Kernel;
use Tempest\Router\Get;
use Tempest\Router\Post;
use Throwable;

final readonly class BoltController
{
    private const string SESSION_KEY = 'bolt.scope';

    public function __construct(
        private BoltConfig $config,
        private Environment $environment,
        private BoltScope $scope,
        private BoltEvaluator $evaluator,
        private BoltClassAliasAutoloader $aliasAutoloader,
    ) {}

    #[Get('/bolt')]
    public function panel(Request $request): Response
    {
        return $this->guard($request) ?? new Ok($this->html())->setContentType(ContentType::HTML);
    }

    #[Post('/bolt/execute')]
    public function execute(Request $request): Response
    {
        if ($denied = $this->guard($request)) {
            return $denied;
        }

        $this->aliasAutoloader->register();

        $code = $this->readCode($request);

        $base = $this->scope->variables();
        $stored = $this->config->persistScope
            ? (array) $request->session->get(self::SESSION_KEY, [])
            : [];

        $startedAt = microtime(true);
        $result = $this->evaluator->evaluate($code, [...$base, ...$stored]);
        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

        if ($this->config->persistScope) {
            $userScope = array_diff_key($result->scope, $base);
            $request->session->set(self::SESSION_KEY, $this->onlySerializable($userScope));
        }

        return new Json([
            'output' => $result->output,
            'result' => $result->result,
            'error' => $result->error,
            'durationMs' => $durationMs,
        ]);
    }

    #[Get('/bolt/assets/bolt.js')]
    public function script(Request $request): Response
    {
        return $this->guard($request) ?? $this->asset('bolt.js', ContentType::JS);
    }

    #[Get('/bolt/assets/bolt.css')]
    public function styles(Request $request): Response
    {
        return $this->guard($request) ?? $this->asset('bolt.css', ContentType::CSS);
    }

    private function guard(Request $request): ?Response
    {
        if (! $this->config->enabled) {
            return new NotFound();
        }

        if (! in_array($this->environment, $this->config->environments, strict: true)) {
            return new NotFound();
        }

        if ($this->config->authorize !== null && ($this->config->authorize)($request) !== true) {
            return new Forbidden();
        }

        return null;
    }

    private function readCode(Request $request): string
    {
        try {
            $payload = json_decode($request->raw ?? '', associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            $payload = null;
        }

        if (is_array($payload) && isset($payload['code'])) {
            return (string) $payload['code'];
        }

        return (string) ($request->get('code') ?? '');
    }

    /**
     * @param array<string, mixed> $scope
     * @return array<string, mixed>
     */
    private function onlySerializable(array $scope): array
    {
        $serializable = [];

        foreach ($scope as $key => $value) {
            try {
                serialize($value);
                $serializable[$key] = $value;
            } catch (Throwable) {
                // Closures, resources, and similar values cannot persist across requests.
            }
        }

        return $serializable;
    }

    private function asset(string $file, ContentType $contentType): Response
    {
        $path = dirname(__DIR__) . '/dist/' . $file;

        if (! is_file($path)) {
            return new NotFound();
        }

        return new Ok(file_get_contents($path))
            ->setContentType($contentType)
            ->addHeader('Cache-Control', 'no-cache');
    }

    /**
     * @return list<array{name: string, description: string}>
     */
    private function variables(): array
    {
        $descriptions = [
            'app' => 'The Tempest container',
            'container' => 'The Tempest container',
            'kernel' => 'The current Tempest kernel',
            'rootPath' => 'The project root path',
            'internalStorage' => "Tempest's internal storage path",
            'tempestVersion' => 'The Tempest version',
        ];

        $variables = [];

        foreach (array_keys($this->scope->variables()) as $name) {
            $variables[] = [
                'name' => '$' . $name,
                'description' => $descriptions[$name] ?? '',
            ];
        }

        return $variables;
    }

    private function html(): string
    {
        $bootstrap = json_encode([
            'executeUrl' => '/bolt/execute',
            'tempestVersion' => Kernel::VERSION,
            'variables' => $this->variables(),
        ], JSON_THROW_ON_ERROR);

        return <<<HTML
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <meta name="robots" content="noindex, nofollow">
                <title>Bolt</title>
                <script>
                    (function () {
                        try {
                            var mode = localStorage.getItem('bolt:theme') || 'system';
                            var dark = mode === 'dark' || (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                            document.documentElement.classList.toggle('dark', dark);
                        } catch (error) {}
                    })();
                </script>
                <link rel="stylesheet" href="/bolt/assets/bolt.css">
            </head>
            <body>
                <div id="bolt-root"></div>
                <script>window.__BOLT__ = {$bootstrap};</script>
                <script type="module" src="/bolt/assets/bolt.js"></script>
            </body>
            </html>
            HTML;
    }
}
