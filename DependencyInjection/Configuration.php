<?php

namespace Nemo64\DatabaseFlysystemBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('nemo64_database_flysystem');

        $nodes = $rootNode->children();

        $doctrineEntityManagerNode = $nodes->arrayNode('doctrine_entity_managers');
        $doctrineEntityManagerNode->prototype('scalar');

        $allowedFilesystemNode = $nodes->arrayNode('filesystems')->useAttributeAsKey('name');
        /** @var NodeBuilder $allowedFilesystemNodes */
        /** @noinspection PhpUndefinedMethodInspection */
        $allowedFilesystemNodes = $allowedFilesystemNode->prototype('array')->children();
        $allowedFilesystemNodes->booleanNode('orphan_removal')->isRequired();

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
