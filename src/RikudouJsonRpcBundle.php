<?php

namespace Rikudou\JsonRpcBundle;

use Rikudou\JsonRpcBundle\Attribute\JsonRpcMethod as JsonRpcMethodAttribute;
use Rikudou\JsonRpcBundle\DependencyInjection\Compiler\JsonRpcResponderCompilerPass;
use Rikudou\JsonRpcBundle\JsonRpc\JsonRpcMethod as JsonRpcMethodInterface;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class RikudouJsonRpcBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->registerAttributeForAutoconfiguration(
            JsonRpcMethodAttribute::class,
            static function (ChildDefinition $definition, JsonRpcMethodAttribute $attribute): void {
                $definition->addTag('rikudou.json_rpc.json_rpc_method', [
                    'name' => $attribute->methodName,
                ]);
            }
        );
        $container->registerForAutoconfiguration(JsonRpcMethodInterface::class)
            ->addTag('rikudou.json_rpc.json_rpc_method');
        $container->addCompilerPass(new JsonRpcResponderCompilerPass());
    }
}
