<?php

declare(strict_types=1);

namespace SymfonyBoilerplate\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * @implements Rule<Expr>
 */
final class NoRawRequestAccessInControllerRule implements Rule
{
    private const CONTROLLER_NAMESPACE_MARKER = "\\Infrastructure\\Symfony\\Controller\\";
    private const REQUEST_CLASS = "Symfony\\Component\\HttpFoundation\\Request";
    private const RAW_METHODS = ["getContent", "get", "toArray"];
    private const RAW_BAGS = ["request", "query", "files", "cookies"];
    private const REMEDY = "bind a validated request DTO (a class implementing App\\Shared\\Infrastructure\\Utils\\Request\\RequestInterface or App\\Shared\\Infrastructure\\Utils\\Request\\QueryRequestInterface) and let the argument resolver plus RequestValidator do the work.";

    public function getNodeType(): string
    {
        return Expr::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $classReflection = $scope->getClassReflection();

        if ($classReflection === null) {
            return [];
        }

        if (!str_contains($classReflection->getName(), self::CONTROLLER_NAMESPACE_MARKER)) {
            return [];
        }

        if ($node instanceof MethodCall) {
            return $this->processMethodCall($node, $scope);
        }

        if ($node instanceof PropertyFetch) {
            return $this->processPropertyFetch($node, $scope);
        }

        return [];
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function processMethodCall(MethodCall $node, Scope $scope): array
    {
        if (!$node->name instanceof Identifier) {
            return [];
        }

        $methodName = $node->name->toString();

        if (!in_array($methodName, self::RAW_METHODS, true) || !$this->isRequest($node->var, $scope)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Raw request access in a controller — do not call $request->%s(), %s',
                $methodName,
                self::REMEDY,
            ))->identifier("symfonyBoilerplate.rawRequestAccess")->build(),
        ];
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function processPropertyFetch(PropertyFetch $node, Scope $scope): array
    {
        if (!$node->name instanceof Identifier) {
            return [];
        }

        $bagName = $node->name->toString();

        if (!in_array($bagName, self::RAW_BAGS, true) || !$this->isRequest($node->var, $scope)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Raw request bag in a controller — do not reach into $request->%s, %s',
                $bagName,
                self::REMEDY,
            ))->identifier("symfonyBoilerplate.rawRequestBag")->build(),
        ];
    }

    private function isRequest(Expr $expr, Scope $scope): bool
    {
        return (new ObjectType(self::REQUEST_CLASS))->isSuperTypeOf($scope->getType($expr))->yes();
    }
}
