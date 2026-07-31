<?php

declare(strict_types=1);

namespace SymfonyBoilerplate\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<InClassNode>
 */
final class ApiEndpointDocumentedRule implements Rule
{
    private const CONTROLLER_NAMESPACE_MARKER = "\\Infrastructure\\Symfony\\Controller\\";
    private const HEALTH_NAMESPACE_MARKER = "\\Controller\\Health\\";
    private const OPENAPI_NAMESPACE = "OpenApi\\Attributes\\";
    private const ROUTE_ATTRIBUTES = [
        "Symfony\\Component\\Routing\\Attribute\\Route",
        "Symfony\\Component\\Routing\\Annotation\\Route",
    ];

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

        if (!str_contains($name, self::CONTROLLER_NAMESPACE_MARKER)) {
            return [];
        }

        if (str_contains($name, self::HEALTH_NAMESPACE_MARKER)) {
            return [];
        }

        $classNode = $node->getOriginalNode();

        if (!$classNode instanceof Class_) {
            return [];
        }

        if (!$this->isRouted($classNode)) {
            return [];
        }

        if ($this->hasOpenApiAttribute($classNode->attrGroups)) {
            return [];
        }

        foreach ($classNode->getMethods() as $method) {
            if ($this->hasOpenApiAttribute($method->attrGroups)) {
                return [];
            }
        }

        return [
            RuleErrorBuilder::message(
                "Routed controller must document its endpoint with an #[OA\\...] attribute — the published OpenAPI contract is generated from these. (Controllers under ...\\Controller\\Health\\ are exempt.)",
            )->identifier("symfonyBoilerplate.apiEndpointDocumented")->build(),
        ];
    }

    private function isRouted(Class_ $classNode): bool
    {
        if ($this->hasRouteAttribute($classNode->attrGroups)) {
            return true;
        }

        foreach ($classNode->getMethods() as $method) {
            if ($this->hasRouteAttribute($method->attrGroups)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<Node\AttributeGroup> $attributeGroups
     */
    private function hasRouteAttribute(array $attributeGroups): bool
    {
        foreach ($this->attributeNames($attributeGroups) as $attributeName) {
            if (in_array($attributeName, self::ROUTE_ATTRIBUTES, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<Node\AttributeGroup> $attributeGroups
     */
    private function hasOpenApiAttribute(array $attributeGroups): bool
    {
        foreach ($this->attributeNames($attributeGroups) as $attributeName) {
            if (str_starts_with($attributeName, self::OPENAPI_NAMESPACE)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<Node\AttributeGroup> $attributeGroups
     *
     * @return list<string>
     */
    private function attributeNames(array $attributeGroups): array
    {
        $names = [];

        foreach ($attributeGroups as $attributeGroup) {
            foreach ($attributeGroup->attrs as $attribute) {
                $names[] = $this->resolveName($attribute->name);
            }
        }

        return $names;
    }

    private function resolveName(Name $name): string
    {
        $resolved = $name->getAttribute("resolvedName");

        return $resolved instanceof Name ? $resolved->toString() : $name->toString();
    }
}
