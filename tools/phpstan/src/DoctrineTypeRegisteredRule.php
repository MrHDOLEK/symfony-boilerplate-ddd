<?php

declare(strict_types=1);

namespace SymfonyBoilerplate\PhpStan;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PHPStan\Analyser\Scope;
use PHPStan\Node\InClassNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<InClassNode>
 */
final class DoctrineTypeRegisteredRule implements Rule
{
    private const DOCTRINE_TYPE_CLASS = "Doctrine\\DBAL\\Types\\Type";
    private const TYPE_NAMESPACE_MARKER = "\\Infrastructure\\Doctrine\\Type\\";
    private const NAME_CONSTANT = "NAME";

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

        if (!str_contains($classReflection->getName(), self::TYPE_NAMESPACE_MARKER)) {
            return [];
        }

        if (!$classReflection->isSubclassOf(self::DOCTRINE_TYPE_CLASS)) {
            return [];
        }

        $classNode = $node->getOriginalNode();

        if (!$classNode instanceof Class_) {
            return [];
        }

        $constant = $this->findNameConstant($classNode);

        if ($constant === null) {
            return [
                RuleErrorBuilder::message(
                    'Doctrine type must declare "public const string NAME" — the constant is the single source of truth for the name registered in config/packages/doctrine.php under dbal.types.',
                )->identifier("symfonyBoilerplate.doctrineTypeRegistered")->build(),
            ];
        }

        if (!$constant->isPublic() || !$this->isStringType($constant->type)) {
            return [
                RuleErrorBuilder::message(
                    'Doctrine type constant NAME must be declared "public const string NAME" — the constant is the single source of truth for the name registered in config/packages/doctrine.php under dbal.types.',
                )->identifier("symfonyBoilerplate.doctrineTypeRegistered")->line($constant->getLine())->build(),
            ];
        }

        return [];
    }

    private function findNameConstant(Class_ $classNode): ?ClassConst
    {
        foreach ($classNode->getConstants() as $classConst) {
            foreach ($classConst->consts as $const) {
                if ($const->name->toString() === self::NAME_CONSTANT) {
                    return $classConst;
                }
            }
        }

        return null;
    }

    private function isStringType(?Node $type): bool
    {
        return $type instanceof Identifier && $type->toLowerString() === "string";
    }
}
