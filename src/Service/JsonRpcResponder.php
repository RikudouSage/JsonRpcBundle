<?php

namespace Rikudou\JsonRpcBundle\Service;

use Error;
use Rikudou\JsonRpcBundle\Enum\JsonRpcErrorCode;
use Rikudou\JsonRpcBundle\Exception\JsonRpcInvalidParamsException;
use Rikudou\JsonRpcBundle\Exception\JsonRpcMethodNotFoundException;
use Rikudou\JsonRpcBundle\JsonRpc\JsonRpcMethod;
use Rikudou\JsonRpcBundle\Request\JsonRpcBatchRequest;
use Rikudou\JsonRpcBundle\Request\JsonRpcRequest;
use Rikudou\JsonRpcBundle\Request\JsonRpcRequestParams;
use Rikudou\JsonRpcBundle\Request\JsonRpcSingleRequest;
use Rikudou\JsonRpcBundle\Response\JsonRpcBatchResponse;
use Rikudou\JsonRpcBundle\Response\JsonRpcError;
use Rikudou\JsonRpcBundle\Response\JsonRpcResponse;
use Rikudou\JsonRpcBundle\Response\JsonRpcSingleResponse;

final class JsonRpcResponder
{
    /**
     * @param array<JsonRpcMethod> $methods
     */
    public function __construct(private array $methods)
    {
    }

    /**
     * @throws JsonRpcMethodNotFoundException
     * @throws JsonRpcInvalidParamsException
     */
    public function respond(JsonRpcRequest $request): JsonRpcResponse
    {
        if ($request instanceof JsonRpcSingleRequest) {
            return $this->respondToSingleRequest($request);
        }
        assert($request instanceof JsonRpcBatchRequest);

        $responses = [];
        foreach ($request->getRequests() as $singleRequest) {
            try {
                $responses[] = $this->respondToSingleRequest($singleRequest);
            } catch (JsonRpcMethodNotFoundException $e) {
                $responses[] = new JsonRpcSingleResponse(
                    $singleRequest,
                    new JsonRpcError(JsonRpcErrorCode::METHOD_NOT_FOUND, $e->getMessage())
                );
            } catch (JsonRpcInvalidParamsException $e) {
                $responses[] = new JsonRpcSingleResponse(
                    $singleRequest,
                    new JsonRpcError(JsonRpcErrorCode::INVALID_PARAMS, $e->getMessage())
                );
            }
        }

        return new JsonRpcBatchResponse($responses);
    }

    /**
     * @throws JsonRpcMethodNotFoundException
     * @throws JsonRpcInvalidParamsException
     */
    private function respondToSingleRequest(JsonRpcSingleRequest $request): JsonRpcSingleResponse
    {
        $method = $this->findMethod($request->getMethod());
        if ($method === null) {
            throw new JsonRpcMethodNotFoundException();
        }

        try {
            return new JsonRpcSingleResponse($request, $method->execute(new JsonRpcRequestParams($request->getParams())));
        } catch (Error $e) {
            if (str_starts_with($e->getMessage(), 'Unknown named parameter')) {
                throw new JsonRpcInvalidParamsException('You are providing extra parameters not understood by this method');
            }
            throw $e;
        }
    }

    private function findMethod(string $methodName): ?JsonRpcMethod
    {
        foreach ($this->methods as $method) {
            if ($method->getMethodName() === $methodName) {
                return $method;
            }
        }

        return null;
    }
}
