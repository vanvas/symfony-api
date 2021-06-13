<?php
declare(strict_types=1);

namespace Vim\Api\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Vim\Api\EventSubscriber\CorsSubscriber;

class ApiExtension extends Extension
{
    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container
            ->getDefinition(CorsSubscriber::class)
            ->setArgument('$allowOrigin', $config['cors']['allow_origin'])
        ;
    }
}
