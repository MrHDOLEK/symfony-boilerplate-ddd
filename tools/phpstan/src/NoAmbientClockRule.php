<?php

declare(strict_types=1);

namespace SymfonyBoilerplate\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<New_>
 */
final class NoAmbientClockRule implements Rule
{
    private const DOMAIN_NAMESPACE_MARKER = "\\Domain\\";

    public function getNodeType(): string
    {
        return New_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->isDomainNamespace((string)$scope->getNamespace())) {
            return [];
        }

        if (!$node->class instanceof Name || $node->class->toLowerString() !== "datetimeimmutable") {
            return [];
        }

        $argument = $node->getArgs()[0]->value ?? null;

        if ($argument === null || ($argument instanceof String_ && strtolower($argument->value) === "now")) {
            return [
                RuleErrorBuilder::message(
                    "Do not read the ambient clock — inject Psr\\Clock\\ClockInterface (symfony/clock provides it) and take the current time from its now(). (Domain classes are exempt, they may stamp themselves.)",
                )->identifier("symfonyBoilerplate.noAmbientClock")->build(),
            ];
        }

        return [];
    }

    private function isDomainNamespace(string $namespace): bool
    {
        return str_contains($namespace . "\\", self::DOMAIN_NAMESPACE_MARKER);
    }
}
