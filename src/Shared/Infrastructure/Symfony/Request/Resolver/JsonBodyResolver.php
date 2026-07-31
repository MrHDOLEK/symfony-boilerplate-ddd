<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Symfony\Request\Resolver;

use App\Shared\Infrastructure\Symfony\Request\Validator\RequestValidator;
use App\Shared\Infrastructure\Symfony\Request\Validator\ValidationError;
use App\Shared\Infrastructure\Utils\Request\QueryRequestInterface;
use App\Shared\Infrastructure\Utils\Request\RequestInterface;
use Generator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\PropertyAccess\Exception\RuntimeException;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use TypeError;

use function in_array;
use function is_subclass_of;

final class JsonBodyResolver implements ValueResolverInterface
{
    private const string FORMAT = "json";

    public function __construct(
        private SerializerInterface $serializer,
        private RequestValidator $requestValidator,
    ) {}

    /**
     * @throws ValidationError
     *
     * @return Generator
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (!$this->supports($argument)) {
            return;
        }

        try {
            $deserialized = $this->serializer->deserialize(
                empty($request->getContent()) ? "{}" : $request->getContent(),
                $argument->getType() ?? "",
                self::FORMAT,
                [
                    AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
                ],
            );
        } catch (NotNormalizableValueException $exception) {
            throw new ValidationError([
                $this->fieldPath($exception) => "VALIDATION.INVALID_VALUE",
            ]);
        } catch (UnexpectedValueException|InvalidArgumentException|RuntimeException|TypeError) {
            throw new ValidationError([
                ValidationError::GENERAL => "VALIDATION.INVALID_PAYLOAD",
            ]);
        }

        if ($deserialized instanceof RequestInterface) {
            $this->requestValidator->validate($deserialized);
        }

        yield $deserialized;
    }

    private function fieldPath(NotNormalizableValueException $exception): string
    {
        $path = $exception->getPath();

        return $path !== null && $path !== "" ? $path : ValidationError::GENERAL;
    }

    private function supports(ArgumentMetadata $argument): bool
    {
        $type = (string)$argument->getType();

        if (!class_exists($type)) {
            return false;
        }

        if (is_subclass_of($type, QueryRequestInterface::class)) {
            return false;
        }

        return in_array(RequestInterface::class, class_implements($type), true);
    }
}
