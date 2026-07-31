<?php

declare(strict_types=1);

namespace SymfonyBoilerplate\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Attribute>
 */
final class NoDoctrineAttributeInDomainRule implements Rule
{
    private const DOMAIN_NAMESPACE_PATTERN = '/^App\\\\[^\\\\]++\\\\Domain\\\\/';
    private const FORBIDDEN_PREFIXES = ["Doctrine\\ORM\\", "Doctrine\\DBAL\\"];

    public function getNodeType(): string
    {
        return Attribute::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->isDomainNamespace((string)$scope->getNamespace())) {
            return [];
        }

        $fullyQualifiedName = $this->resolveName($node->name);

        foreach (self::FORBIDDEN_PREFIXES as $prefix) {
            if (!str_starts_with($fullyQualifiedName, $prefix)) {
                continue;
            }

            return [
                RuleErrorBuilder::message(
                    "No Doctrine attributes on domain classes — map the entity from the Infrastructure layer (XML or PHP mapping under Infrastructure\\Doctrine) instead.",
                )->identifier("symfonyBoilerplate.noDoctrineAttributeInDomain")->build(),
            ];
        }

        return [];
    }

    private function isDomainNamespace(string $namespace): bool
    {
        return preg_match(self::DOMAIN_NAMESPACE_PATTERN, $namespace . "\\") === 1;
    }

    private function resolveName(Name $name): string
    {
        $resolved = $name->getAttribute("resolvedName");

        return $resolved instanceof Name ? $resolved->toString() : $name->toString();
    }
}
