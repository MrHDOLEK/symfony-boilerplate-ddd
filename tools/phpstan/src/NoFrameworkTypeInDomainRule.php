<?php

declare(strict_types=1);

namespace SymfonyBoilerplate\PhpStan;

use PhpParser\Node;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\UnionType;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<InClassNode>
 */
final class NoFrameworkTypeInDomainRule implements Rule
{
    private const DOMAIN_NAMESPACE_PATTERN = '/^App\\\\[^\\\\]++\\\\Domain\\\\/';
    private const FORBIDDEN_PREFIXES = ["Symfony\\", "Doctrine\\", "Psr\\", "Nelmio\\"];
    private const ALLOWED_TYPES = [
        "Psr\\Clock\\ClockInterface",
        "Symfony\\Component\\Clock\\ClockInterface",
    ];
    private const ALLOWED_PREFIXES = ["Symfony\\Component\\Uid\\"];

    public function getNodeType(): string
    {
        return InClassNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $node->getClassReflection();

        if (preg_match(self::DOMAIN_NAMESPACE_PATTERN, $classReflection->getName()) !== 1) {
            return [];
        }

        $classNode = $node->getOriginalNode();
        $typeNames = $this->supertypeNames($classNode);

        foreach ($classNode->getProperties() as $property) {
            foreach ($this->collectTypeNames($property->type) as $typeName) {
                $typeNames[] = $typeName;
            }
        }

        foreach ($classNode->getMethods() as $method) {
            foreach ($method->params as $parameter) {
                foreach ($this->collectTypeNames($parameter->type) as $typeName) {
                    $typeNames[] = $typeName;
                }
            }

            foreach ($this->collectTypeNames($method->returnType) as $typeName) {
                $typeNames[] = $typeName;
            }
        }

        $errors = [];

        foreach ($typeNames as $typeName) {
            $resolved = $this->resolveName($typeName);

            if (!$this->isFrameworkType($resolved)) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(sprintf(
                'Domain class names the framework type "%s" — the domain layer stays framework-free; depend on an interface you own and adapt the framework type in the Infrastructure layer.',
                $resolved,
            ))->identifier("symfonyBoilerplate.noFrameworkTypeInDomain")->line($typeName->getLine())->build();
        }

        return $errors;
    }

    /**
     * @return list<Name>
     */
    private function supertypeNames(ClassLike $classNode): array
    {
        $names = [];

        if ($classNode instanceof Class_ && $classNode->extends instanceof Name) {
            $names[] = $classNode->extends;
        }

        if ($classNode instanceof Interface_) {
            foreach ($classNode->extends as $interfaceName) {
                $names[] = $interfaceName;
            }
        }

        if ($classNode instanceof Class_ || $classNode instanceof Enum_) {
            foreach ($classNode->implements as $interfaceName) {
                $names[] = $interfaceName;
            }
        }

        return $names;
    }

    /**
     * @return list<Name>
     */
    private function collectTypeNames(?Node $type): array
    {
        if ($type instanceof Name) {
            return [$type];
        }

        if ($type instanceof NullableType) {
            return $this->collectTypeNames($type->type);
        }

        if ($type instanceof UnionType || $type instanceof IntersectionType) {
            $names = [];

            foreach ($type->types as $innerType) {
                foreach ($this->collectTypeNames($innerType) as $name) {
                    $names[] = $name;
                }
            }

            return $names;
        }

        return [];
    }

    private function isFrameworkType(string $type): bool
    {
        if (in_array($type, self::ALLOWED_TYPES, true)) {
            return false;
        }

        foreach (self::ALLOWED_PREFIXES as $prefix) {
            if (str_starts_with($type, $prefix)) {
                return false;
            }
        }

        foreach (self::FORBIDDEN_PREFIXES as $prefix) {
            if (str_starts_with($type, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function resolveName(Name $name): string
    {
        $resolved = $name->getAttribute("resolvedName");

        return $resolved instanceof Name ? $resolved->toString() : $name->toString();
    }
}
