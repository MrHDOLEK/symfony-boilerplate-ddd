<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Symfony\Request\Resolver;

use BackedEnum;
use Generator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

use function enum_exists;
use function is_a;

final class QueryEnumResolver implements ValueResolverInterface
{
    /**
     * @return Generator<int, BackedEnum|null>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();

        if ($type === null || !enum_exists($type)) {
            return;
        }

        if (!is_a($type, BackedEnum::class, true)) {
            return;
        }

        $value = $request->query->get($argument->getName());

        if ($value === null || $value === "") {
            yield null;

            return;
        }

        yield $type::tryFrom((string)$value);
    }
}
