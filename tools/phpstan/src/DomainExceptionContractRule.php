<?php

declare(strict_types=1);

namespace SymfonyBoilerplate\PhpStan;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<InClassNode>
 */
final class DomainExceptionContractRule implements Rule
{
    private const DOMAIN_EXCEPTION_CLASS = "App\\Shared\\Domain\\DomainException";
    private const DOMAIN_NAMESPACE_PATTERN = '/^App\\\\[^\\\\]++\\\\Domain\\\\/';

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $node->getClassReflection();

        if ($classReflection->isInterface() || $classReflection->isAbstract()) {
            return [];
        }

        $name = $classReflection->getName();

        if ($name === self::DOMAIN_EXCEPTION_CLASS) {
            return [];
        }

        if (preg_match(self::DOMAIN_NAMESPACE_PATTERN, $name) !== 1 || !str_ends_with($name, "Exception")) {
            return [];
        }

        if ($classReflection->isSubclassOf(self::DOMAIN_EXCEPTION_CLASS)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(
                "Domain exception must extend App\\Shared\\Domain\\DomainException — domain failures share one base so the ExceptionListener can map them to an HTTP status.",
            )->identifier("symfonyBoilerplate.domainExceptionContract")->build(),
        ];
    }
}
