<?php

declare(strict_types=1);

namespace SymfonyBoilerplate\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<New_>
 */
final class NoMutableDateTimeRule implements Rule
{
    public function getNodeType(): string
    {
        return New_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->class instanceof Name || $node->class->toLowerString() !== "datetime") {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                "Use DateTimeImmutable, not mutable DateTime.",
            )->identifier("symfonyBoilerplate.noMutableDateTime")->build(),
        ];
    }
}
