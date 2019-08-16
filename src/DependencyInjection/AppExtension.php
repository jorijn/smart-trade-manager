<?php

namespace App\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class AppExtension extends Extension
{
    /**
     * {@inheritdoc}
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        // set the API configuration
        $container->setParameter('app.config.exchange.api_key', $config['exchange']['api_key']);
        $container->setParameter('app.config.exchange.api_secret', $config['exchange']['api_secret']);
        $container->setParameter('app.config.exchange.ladder_size', $config['exchange']['ladder_size']);
    }
}
