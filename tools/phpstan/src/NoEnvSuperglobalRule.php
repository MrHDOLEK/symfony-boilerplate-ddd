<?php

declare(strict_types=1);

namespace SymfonyBoilerplate\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Expr>
 */
final class NoEnvSuperglobalRule implements Rule
{
    private const APPLICATION_NAMESPACE = "App";
    private const FORBIDDEN_FUNCTIONS = ["getenv"];
    private const ENVIRONMENT_VARIABLES = ["_ENV", "_SERVER"];
    private const REQUEST_VARIABLES = ["_GET", "_POST", "_REQUEST", "_COOKIE", "_FILES"];

    public function getNodeType(): string
    {
        return Expr::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->isApplicationCode($scope)) {
            return [];
        }

        if ($node instanceof Variable) {
            return $this->processVariable($node);
        }

        if (!$node instanceof FuncCall || !$node->name instanceof Name) {
            return [];
        }

        $functionName = strtolower($node->name->toString());

        if (!in_array($functionName, self::FORBIDDEN_FUNCTIONS, true)) {
            return [];
        }

        return [$this->environmentError($functionName . "()")];
    }

    /**
     * @return list<IdentifierRuleError>
     */
    private function processVariable(Variable $node): array
    {
        if (!is_string($node->name)) {
            return [];
        }

        if (in_array($node->name, self::ENVIRONMENT_VARIABLES, true)) {
            return [$this->environmentError("\$" . $node->name)];
        }

        if (in_array($node->name, self::REQUEST_VARIABLES, true)) {
            return [$this->requestError("\$" . $node->name)];
        }

        return [];
    }

    private function environmentError(string $description): IdentifierRuleError
    {
        return RuleErrorBuilder::message(sprintf(
            'Configuration read from "%s" — bind a container parameter or an %%env(...)%% reference in config/ and inject the value instead of touching the superglobals.',
            $description,
        ))->identifier("symfonyBoilerplate.noEnvSuperglobal")->build();
    }

    private function requestError(string $description): IdentifierRuleError
    {
        return RuleErrorBuilder::message(sprintf(
            'Request input read from "%s" — bind a validated request DTO (App\\Shared\\Infrastructure\\Utils\\Request\\RequestInterface or App\\Shared\\Infrastructure\\Utils\\Request\\QueryRequestInterface) and let the argument resolver hand it to the controller.',
            $description,
        ))->identifier("symfonyBoilerplate.noRequestSuperglobal")->build();
    }

    private function isApplicationCode(Scope $scope): bool
    {
        $namespace = (string)$scope->getNamespace();

        return $namespace === self::APPLICATION_NAMESPACE || str_starts_with($namespace, self::APPLICATION_NAMESPACE . "\\");
    }
}
