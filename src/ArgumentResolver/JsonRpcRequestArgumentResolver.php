<?php

namespace Rikudou\JsonRpcBundle\ArgumentResolver;

use Generator;
use JetBrains\PhpStorm\Pure;
use Rikudou\JsonRpcBundle\Exception\JsonRpcInternalErrorException;
use Rikudou\JsonRpcBundle\Exception\JsonRpcInvalidRequestException;
use Rikudou\JsonRpcBundle\Exception\JsonRpcParseException;
use Rikudou\JsonRpcBundle\Request\JsonRpcRequest;
use Rikudou\JsonRpcBundle\Service\JsonRpcRequestParser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class JsonRpcRequestArgumentResolver implements ArgumentValueResolverInterface
{
    public function __construct(private JsonRpcRequestParser $requestParser)
    {
    }

    #[Pure]
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        if ($argument->getType() === null) {
            return false;
        }

        return is_a($argument->getType(), JsonRpcRequest::class, true);
    }

    /**
     * @throws JsonRpcInvalidRequestException
     * @throws JsonRpcParseException
     * @throws JsonRpcInternalErrorException
     * @phpstan-return Generator<JsonRpcRequest>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): Generator
    {
        yield $this->requestParser->parse($request);
    }
}
