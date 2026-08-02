<?php

declare(strict_types=1);

namespace SymfonyBoilerplate\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<InClassNode>
 */
final class RequestDtoContractRule implements Rule
{
    private const REQUEST_INTERFACE = "App\\Shared\\Infrastructure\\Utils\\Request\\RequestInterface";

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

        if (!$classReflection->implementsInterface(self::REQUEST_INTERFACE)) {
            return [];
        }

        $classNode = $node->getOriginalNode();

        if (!$classNode instanceof Class_) {
            return [];
        }

        $errors = [];

        if (!$classNode->isFinal()) {
            $errors[] = RuleErrorBuilder::message(
                "Request DTO must be final — it is a closed data contract deserialized by the argument resolver, not an extension point.",
            )->identifier("symfonyBoilerplate.requestDtoFinal")->build();
        }

        if ($classNode->isReadonly()) {
            return $errors;
        }

        $constructor = $classNode->getMethod("__construct");

        if ($constructor === null) {
            return $errors;
        }

        foreach ($constructor->params as $parameter) {
            if (!$parameter->isPromoted() || $parameter->isReadonly()) {
                continue;
            }

            $errors[] = RuleErrorBuilder::message(sprintf(
                'Promoted property "$%s" of a request DTO must be readonly (or declare the whole class readonly) — a validated request must never change after the argument resolver built it.',
                $this->parameterName($parameter),
            ))->identifier("symfonyBoilerplate.requestDtoReadonly")->line($parameter->getLine())->build();
        }

        return $errors;
    }

    private function parameterName(Node\Param $parameter): string
    {
        if ($parameter->var instanceof Variable && is_string($parameter->var->name)) {
            return $parameter->var->name;
        }

        return "?";
    }
}
