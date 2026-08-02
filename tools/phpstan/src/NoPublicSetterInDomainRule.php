<?php

declare(strict_types=1);

namespace SymfonyBoilerplate\PhpStan;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassMethodNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<InClassMethodNode>
 */
final class NoPublicSetterInDomainRule implements Rule
{
    private const DOMAIN_NAMESPACE_PATTERN = '/^App\\\\[^\\\\]++\\\\Domain\\\\/';
    private const SETTER_PATTERN = '/^set[A-Z]/';

    public function getNodeType(): string
    {
        return InClassMethodNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $scope->getClassReflection();

        if ($classReflection === null || preg_match(self::DOMAIN_NAMESPACE_PATTERN, $classReflection->getName()) !== 1) {
            return [];
        }

        $method = $node->getMethodReflection();

        if (!$method->isPublic() || preg_match(self::SETTER_PATTERN, $method->getName()) !== 1) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Public setter "%s()" on a domain class — express the change as an intention-revealing domain method that keeps the invariant.',
                $method->getName(),
            ))->identifier("symfonyBoilerplate.noPublicSetterInDomain")->build(),
        ];
    }
}
