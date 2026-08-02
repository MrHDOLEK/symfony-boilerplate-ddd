<?php

declare(strict_types=1);

namespace SymfonyBoilerplate\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Expr>
 */
final class NoDebugFunctionRule implements Rule
{
    private const APPLICATION_NAMESPACE = "App";
    private const DEBUG_FUNCTIONS = ["dd", "dump", "var_dump", "print_r", "var_export", "error_log", "phpinfo"];

    public function getNodeType(): string
    {
        return Expr::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$this->isApplicationCode($scope)) {
            return [];
        }

        if ($node instanceof Exit_) {
            return [
                RuleErrorBuilder::message(
                    "No exit/die in application code — throw an exception or return a Response, and report diagnostics through the injected Psr\\Log\\LoggerInterface. Debug helpers must never reach a commit.",
                )->identifier("symfonyBoilerplate.noDebugFunction")->build(),
            ];
        }

        if (!$node instanceof FuncCall || !$node->name instanceof Name) {
            return [];
        }

        $functionName = strtolower($node->name->toString());

        if (!in_array($functionName, self::DEBUG_FUNCTIONS, true)) {
            return [];
        }

        return [
            RuleErrorBuilder::message(sprintf(
                'Debug helper "%s()" left in application code — report diagnostics through the injected Psr\\Log\\LoggerInterface instead. Debug helpers must never reach a commit.',
                $functionName,
            ))->identifier("symfonyBoilerplate.noDebugFunction")->build(),
        ];
    }

    private function isApplicationCode(Scope $scope): bool
    {
        $namespace = (string)$scope->getNamespace();

        return $namespace === self::APPLICATION_NAMESPACE || str_starts_with($namespace, self::APPLICATION_NAMESPACE . "\\");
    }
}
