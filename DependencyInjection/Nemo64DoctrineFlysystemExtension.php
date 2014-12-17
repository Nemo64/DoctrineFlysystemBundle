<?php

namespace Nemo64\DatabaseFlysystemBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class Nemo64DatabaseFlysystemExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $this->configureFilesystemListener($container, $config);
        $this->configureDoctrineFileManager($container, $config);
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     */
    protected function configureFilesystemListener(ContainerBuilder $container, array $config)
    {
        $definition = $container->getDefinition('nemo64_database_flysystem.filesystem_listener');

        foreach ($config['filesystems'] as $filesystemName => $filesystemConfig) {
            $filesystemServiceId = 'oneup_flysystem.' . $filesystemName . '_filesystem';

            $arguments = array($filesystemName, new Reference($filesystemServiceId), $filesystemConfig);
            $definition->addMethodCall('addFilesystem', $arguments);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     */
    protected function configureDoctrineFileManager(ContainerBuilder $container, array $config)
    {
        $definition = $container->getDefinition('nemo64_database_flysystem.doctrine_file_manager');

        foreach ($config['doctrine_entity_managers'] as $entityManagerName) {
            $entityManagerId = 'doctrine.orm.' . $entityManagerName . '_entity_manager';

            $arguments = array(new Reference($entityManagerId));
            $definition->addMethodCall('addEntityManager', $arguments);
        }
    }
}
