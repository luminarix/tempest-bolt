<?php

declare(strict_types=1);

namespace Tempest\Bolt;

use Closure;
use Psy\CodeCleaner;
use Psy\CodeCleaner\NoReturnValue;
use Psy\Exception\ErrorException;
use Psy\Exception\ParseErrorException;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Throwable;

final class PsyshBoltEvaluator implements BoltEvaluator
{
    private readonly CodeCleaner $cleaner;

    public function __construct(?CodeCleaner $cleaner = null)
    {
        $this->cleaner = $cleaner ?? new CodeCleaner();
    }

    public function evaluate(string $code, array $scope): BoltResult
    {
        try {
            $cleaned = $this->cleaner->clean(explode("\n", $code));
        } catch (ParseErrorException $parseError) {
            return new BoltResult(output: '', result: null, error: $parseError->getMessage(), scope: $scope);
        }

        if ($cleaned === false) {
            return new BoltResult(output: '', result: null, error: 'The snippet is incomplete.', scope: $scope);
        }

        [$rawResult, $output, $error, $newScope] = ($this->runner())($cleaned, $scope);

        if ($error instanceof Throwable) {
            return new BoltResult(
                output: $output,
                result: null,
                error: sprintf('%s: %s', $error::class, $error->getMessage()),
                scope: $newScope,
            );
        }

        $result = $rawResult instanceof NoReturnValue ? null : $rawResult;

        return new BoltResult(
            output: $output,
            result: $result === null ? null : $this->present($result),
            error: null,
            scope: $newScope,
        );
    }

    /**
     * A static closure with no bound `$this`, so user code cannot reach the
     * evaluator and `get_defined_vars()` only ever sees prefixed internals
     * plus the user's own variables.
     *
     * @return Closure(string, array<string, mixed>): array{0: mixed, 1: string, 2: ?Throwable, 3: array<string, mixed>}
     */
    private function runner(): Closure
    {
        return static function (string $__bolt_cleaned, array $__bolt_scope): array {
            extract($__bolt_scope, EXTR_SKIP);
            unset($__bolt_scope);

            $__bolt_output = '';
            $__bolt_obLevel = ob_get_level();

            ob_start(static function (string $chunk) use (&$__bolt_output): string {
                $__bolt_output .= $chunk;

                return '';
            }, 1);

            set_error_handler(static function (int $errno, string $errstr, string $errfile, int $errline) use (&$__bolt_output): bool {
                if ((error_reporting() & $errno) === 0) {
                    return true;
                }

                if ($errno & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR)) {
                    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
                }

                $label = match (true) {
                    (bool) ($errno & (E_DEPRECATED | E_USER_DEPRECATED)) => 'Deprecated',
                    (bool) ($errno & (E_WARNING | E_USER_WARNING | E_CORE_WARNING | E_COMPILE_WARNING)) => 'Warning',
                    default => 'Notice',
                };

                $__bolt_output .= sprintf("%s: %s in %s on line %d\n", $label, $errstr, $errfile, $errline);

                return true;
            });

            $__bolt_result = null;
            $__bolt_error = null;

            try {
                $__bolt_result = eval($__bolt_cleaned);
            } catch (Throwable $throwable) {
                $__bolt_error = $throwable;
            } finally {
                restore_error_handler();

                while (ob_get_level() > $__bolt_obLevel) {
                    ob_end_clean();
                }
            }

            $__bolt_newScope = get_defined_vars();
            unset(
                $__bolt_newScope['__bolt_cleaned'],
                $__bolt_newScope['__bolt_output'],
                $__bolt_newScope['__bolt_obLevel'],
                $__bolt_newScope['__bolt_result'],
                $__bolt_newScope['__bolt_error'],
            );

            return [$__bolt_result, $__bolt_output, $__bolt_error, $__bolt_newScope];
        };
    }

    private function present(mixed $value): string
    {
        $cloner = new VarCloner();
        $dumper = new CliDumper();
        $dumper->setColors(false);

        $output = '';
        $dumper->dump(
            $cloner->cloneVar($value),
            function (string $line, int $depth) use (&$output): void {
                if ($depth >= 0) {
                    $output .= str_repeat('  ', $depth) . $line . "\n";
                }
            },
        );

        return rtrim($output, "\n");
    }
}
