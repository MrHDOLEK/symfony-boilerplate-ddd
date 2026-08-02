<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Symfony\Request\Resolver;

use App\Shared\Infrastructure\Symfony\Request\Attribute\QueryParam;
use App\Shared\Infrastructure\Symfony\Request\Validator\RequestValidator;
use App\Shared\Infrastructure\Symfony\Request\Validator\ValidationError;
use App\Shared\Infrastructure\Utils\Request\QueryRequestInterface;
use Generator;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use TypeError;
use ValueError;

use function is_scalar;
use function is_subclass_of;

final class QueryRequestResolver implements ValueResolverInterface
{
    public function __construct(
        private RequestValidator $requestValidator,
    ) {}

    /**
     * @throws ValidationError
     *
     * @return Generator<QueryRequestInterface>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();

        if ($type === null || !is_subclass_of($type, QueryRequestInterface::class)) {
            return;
        }

        $query = $this->hydrate($type, $request);
        $this->requestValidator->validate($query);

        yield $query;
    }

    /**
     * @param class-string<QueryRequestInterface> $type
     */
    private function hydrate(string $type, Request $request): QueryRequestInterface
    {
        $reflection = new ReflectionClass($type);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return $reflection->newInstance();
        }

        $arguments = [];

        foreach ($constructor->getParameters() as $parameter) {
            $queryKey = $this->queryKey($parameter);

            if (!$request->query->has($queryKey)) {
                continue;
            }

            $raw = $request->query->get($queryKey);
            $arguments[$parameter->getName()] = $this->coerce(is_scalar($raw) ? (string)$raw : "", $parameter);
        }

        try {
            return $reflection->newInstanceArgs($arguments);
        } catch (TypeError|ValueError) {
            throw new ValidationError([
                ValidationError::GENERAL => "VALIDATION.INVALID_PAYLOAD",
            ]);
        }
    }

    private function queryKey(ReflectionParameter $parameter): string
    {
        $attributes = $parameter->getAttributes(QueryParam::class);

        if ($attributes === []) {
            return $parameter->getName();
        }

        return $attributes[0]->newInstance()->name;
    }

    private function coerce(string $value, ReflectionParameter $parameter): int|float|bool|string
    {
        $type = $parameter->getType();

        if (!$type instanceof ReflectionNamedType) {
            return $value;
        }

        return match ($type->getName()) {
            "int" => $this->coerceInt($value),
            "float" => $this->coerceFloat($value),
            "bool" => filter_var($value, FILTER_VALIDATE_BOOL),
            default => $value,
        };
    }

    private function coerceInt(string $value): int
    {
        if (!is_numeric($value)) {
            throw new ValidationError([
                ValidationError::GENERAL => "VALIDATION.INVALID_PAYLOAD",
            ]);
        }

        return (int)$value;
    }

    private function coerceFloat(string $value): float
    {
        if (!is_numeric($value)) {
            throw new ValidationError([
                ValidationError::GENERAL => "VALIDATION.INVALID_PAYLOAD",
            ]);
        }

        return (float)$value;
    }
}
