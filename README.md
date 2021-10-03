# JSON-RPC bundle for Symfony.

This allows you to respond and handle JSON-RPC requests using modern php.

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