# JSON-RPC bundle for Symfony.

This allows you to respond and handle JSON-RPC requests using modern php.

Both batch requests and single requests are supported.

## Installation

Requires php 8 and Symfony 5.x.

`composer require rikudou/json-rpc-bundle`

## Usage

Create a controller that will be handling the traffic, the controller is very simple and basically looks like this:

```php
<?php

use Rikudou\JsonRpcBundle\Response\JsonRpcResponse;
use Rikudou\JsonRpcBundle\Request\JsonRpcRequest;
use Rikudou\JsonRpcBundle\Service\JsonRpcResponder;
use Symfony\Component\Routing\Annotation\Route;

final class MyController
{
    #[Route('/jsonrpc')]
    public function myHandler(JsonRpcRequest $request, JsonRpcResponder $responder): JsonRpcResponse
    {
        return $responder->respond($request);
    }
}
```

The `JsonRpcRequest` object is automatically injected based on current request and the `JsonRpcResponder` takes care
of locating the correct method and providing response.

## Creating methods

Creating methods is very simple and you have two options:

1. use the `Rikudou\JsonRpcBundle\Attribute\JsonRpcMethod` attribute in a callable object (implementing `__invoke()`)
2. implement the `\Rikudou\JsonRpcBundle\JsonRpc\JsonRpcMethod` interface

Note that while the attribute and interface have the same name, the namespace is different.

### Example with attribute

```php
<?php

use Rikudou\JsonRpcBundle\Attribute\JsonRpcMethod;

#[JsonRpcMethod('myMethodName')]
final class MyMethod
{
    public function __invoke(): string
    {
        return 'some-response';
    }
}
```

Or if you want to accept parameters:

```php
<?php

use Rikudou\JsonRpcBundle\Attribute\JsonRpcMethod;

#[JsonRpcMethod('myMethodName')]
final class MyMethod
{
    public function __invoke(string $parameter1, string $parameter2): string
    {
        return $parameter1 . $parameter2;
    }
}
```

If any other parameters are provided, an exception might be thrown, to avoid that you can add a variadic parameter
that catches all other parameters (the name of the parameter doesn't matter):

```php
<?php

use Rikudou\JsonRpcBundle\Attribute\JsonRpcMethod;

#[JsonRpcMethod('myMethodName')]
final class MyMethod
{
    public function __invoke(string $parameter1, string $parameter2, ...$allOtherParameters): string
    {
        return $parameter1 . $parameter2 . json_encode($allOtherParameters);
    }
}
```

### Example with interface

```php
<?php

use Rikudou\JsonRpcBundle\JsonRpc\JsonRpcMethod;
use Rikudou\JsonRpcBundle\Request\JsonRpcRequestParams;

final class MyMethod implements JsonRpcMethod
{
    public function getMethodName() : string
    {
        return 'myMethod';
    }
    
    public function execute(JsonRpcRequestParams $params) : int|string|null|float|JsonSerializable|array|stdClass
    {
        return 'some-response';
    }
}
```

You can also check for parameters:

```php
<?php

use Rikudou\JsonRpcBundle\JsonRpc\JsonRpcMethod;
use Rikudou\JsonRpcBundle\Request\JsonRpcRequestParams;

final class MyMethod implements JsonRpcMethod
{
    public function getMethodName() : string
    {
        return 'myMethod';
    }
    
    public function execute(JsonRpcRequestParams $params) : int|string|null|float|JsonSerializable|array|stdClass
    {
        if (!$params->hasParams()) {
            throw new RuntimeException('There are no parameters!');
        }
        
        if (!isset($params['myParam'])) {
            throw new RuntimeException('The parameter "myParam" is missing!');
        }
        
        $allParameters = $params->getParams();
        
        return $params['myParam'] . json_encode($allParameters);
    }
}
```

## Working with request object

If you want to do some checks on the request object you can do so before passing it to the `JsonRpcResponder` service.

The `JsonRpcRequest` is a marker interface that doesn't contain any methods meaning you need to check yourself if it's
an instance of `JsonRpcSingleRequest` or `JsonRpcBatchRequest`.

The `JsonRpcSingleRequest` object contains these methods:

- `getMethod(): string`
- `getParams(): ?array`
- `getId(): int|string|null`

The `JsonRpcBatchRequest` contains these methods:

- `getRequests(): iterable<JsonRpcSingleRequest>` - returns list of individual requests7

In your controller you can also typehint the concrete class (`JsonRpcSingleRequest` or `JsonRpcBatchRequest`) but
in that case you will get a `TypeError` when the other type of request arrives.

If you want to get the current request outside of a controller class, you will need to use the service 
`JsonRpcRequestParser`:

```php
<?php

use Rikudou\JsonRpcBundle\Service\JsonRpcRequestParser;
use Rikudou\JsonRpcBundle\Request\JsonRpcRequest;
use Symfony\Component\HttpFoundation\RequestStack;

final class MyService
{
    public function __construct(private JsonRpcRequestParser $parser, private RequestStack $requestStack) 
    {
    }
    
    public function getRequest(): JsonRpcRequest
    {
        // if you don't provide a Symfony\Component\HttpFoundation\Request parameter, the current request is used
        $request = $this->parser->parse();
        // or you can specify the request yourself
        $request = $this->parser->parse($this->requestStack->getCurrentRequest());
        
        return $this->parser->parse();
    }
}
```

## Working with response object

If you want to alter the response in your controller, you can call `JsonRpcResponse`'s `getJson()` method which returns
an array with the raw data that would be encoded to JSON.

You can check whether the response is a single one or batch one by checking for instance of `JsonRpcSingleResponse`
and `JsonRpcBatchResponse`.

```php
<?php

use Symfony\Component\HttpFoundation\JsonResponse;
use Rikudou\JsonRpcBundle\Request\JsonRpcRequest;
use Rikudou\JsonRpcBundle\Service\JsonRpcResponder;
use Symfony\Component\Routing\Annotation\Route;
use Rikudou\JsonRpcBundle\Response\JsonRpcSingleResponse;
use Rikudou\JsonRpcBundle\Response\JsonRpcBatchResponse;

final class JsonRpcController
{
    #[Route('/jsonrpc')]
    public function jsonRpc(JsonRpcRequest $request, JsonRpcResponder $responder): JsonResponse
    {
        $response = $responder->respond($request);
        
        $rawData = $response->getJson();
        
        if ($response instanceof JsonRpcSingleResponse) {
            // do something with $rawData
        } elseif ($response instanceof JsonRpcBatchResponse) {
            // do something with $rawData
        }
        
        return new JsonResponse($rawData, $response->getStatusCode());
    }
}
```
