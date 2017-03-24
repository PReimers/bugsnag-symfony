<?php

namespace Bugsnag\BugsnagBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CallbackRegisteringPass implements CompilerPassInterface
{
    const FACTORY_SERVICE_NAME = 'bugsnag.factory';
    const TAG_NAME = 'bugsnag.callback';

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (! $container->has(self::FACTORY_SERVICE_NAME)) {
            return;
        }

        // Get the Bugsnag factory service
        $bugsnagFactory = $container->findDefinition(self::FACTORY_SERVICE_NAME);

        // Get all services tagged as a callback
        $callbackServices = $container->findTaggedServiceIds(self::TAG_NAME);

        // Add each callback to the factory service via an addCallback call
        foreach ($callbackServices as $id => $tags) {
            foreach ($tags as $attributes) {
                // Get the method name to call on the service from the tag definition,
                // defaulting to registerCallback
                $method = isset($attributes['method']) ? $attributes['method'] : 'registerCallback';
                $bugsnagFactory->addMethodCall('addCallback', [[new Reference($id), $method]]);
            }
        }
    }
}
