<?php

namespace Stegeman\Messenger\CloudEvents\DependencyInjection;

use Stegeman\Messenger\CloudEvents\Serializer\MessageRegistry;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class CloudEventsExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $builder): void
    {
        $configs = $this->processConfiguration(new Configuration(), $configs);

        $loader = new YamlFileLoader(
            $builder,
            new FileLocator(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'config')
        );

        $loader->load('services.yaml');

        $definition = $builder->getDefinition(MessageRegistry::class);
        foreach ($configs['registry'] as $message) {
            $definition->addMethodCall('addMessage', [$message['name'], $message['className']]);
        }

        $builder->setParameter('cloud_events.normalizer_service_id', $configs['normalizer_service_id']);
    }
}