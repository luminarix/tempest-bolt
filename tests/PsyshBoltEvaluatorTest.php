<?php

declare(strict_types=1);

namespace Tempest\Bolt\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Bolt\PsyshBoltEvaluator;

final class PsyshBoltEvaluatorTest extends TestCase
{
    #[Test]
    public function it_captures_printed_output(): void
    {
        $result = (new PsyshBoltEvaluator())->evaluate('echo "hello";', []);

        $this->assertSame('hello', $result->output);
        $this->assertNull($result->error);
    }

    #[Test]
    public function it_returns_the_value_of_the_last_expression(): void
    {
        $result = (new PsyshBoltEvaluator())->evaluate('1 + 2;', []);

        $this->assertSame('3', $result->result);
        $this->assertNull($result->error);
    }

    #[Test]
    public function it_exposes_scope_variables_to_the_snippet(): void
    {
        $result = (new PsyshBoltEvaluator())->evaluate('$number * 2;', ['number' => 21]);

        $this->assertSame('42', $result->result);
    }

    #[Test]
    public function it_returns_defined_variables_for_persistence(): void
    {
        $result = (new PsyshBoltEvaluator())->evaluate('$greeting = "hi";', []);

        $this->assertArrayHasKey('greeting', $result->scope);
        $this->assertSame('hi', $result->scope['greeting']);
    }

    #[Test]
    public function it_reports_thrown_exceptions_as_errors(): void
    {
        $result = (new PsyshBoltEvaluator())->evaluate('throw new \RuntimeException("boom");', []);

        $this->assertNull($result->result);
        $this->assertSame('RuntimeException: boom', $result->error);
    }

    #[Test]
    public function it_reports_parse_errors_without_crashing(): void
    {
        $result = (new PsyshBoltEvaluator())->evaluate('$x = ;', []);

        $this->assertNotNull($result->error);
        $this->assertNull($result->result);
    }

    #[Test]
    public function it_reports_incomplete_snippets(): void
    {
        $result = (new PsyshBoltEvaluator())->evaluate('if (true) {', []);

        $this->assertSame('The snippet is incomplete.', $result->error);
    }

    #[Test]
    public function it_pretty_prints_arrays(): void
    {
        $result = (new PsyshBoltEvaluator())->evaluate('["a" => 1];', []);

        $this->assertNotNull($result->result);
        $this->assertStringContainsString('"a" => 1', $result->result);
    }
}
