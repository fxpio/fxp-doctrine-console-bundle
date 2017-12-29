<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\DoctrineConsoleBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration of the securitybundle to get the fxp_security options.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Get the adapters.
     *
     * @return string[]
     */
    public static function getAdapters()
    {
        return [
            'adapter_id',
            'service_manager_adapter',
            'resource_adapter',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fxp_doctrine_console');

        $rootNode
            ->append($this->getCommands())
        ;

        return $treeBuilder;
    }

    /**
     * Get expression node.
     *
     * @return NodeDefinition
     */
    protected function getCommands()
    {
        $node = static::createNode('commands');
        $node
            ->fixXmlConfig('command')
            ->useAttributeAsKey('name', false)
            ->normalizeKeys(false)
            ->prototype('array')
                ->children()
                    ->append($this->getAdapterConfig())
                    ->append($this->getServiceManagerAdapterConfig())
                    ->append($this->getResourceAdapterConfig())
                    ->append($this->createCommand('view'))
                    ->append($this->createCommand('create'))
                    ->append($this->createCommand('update'))
                    ->append($this->createCommand('delete'))
                    ->append($this->createCommand('undelete'))
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Get the adapter node.
     *
     * @return NodeDefinition
     */
    protected function getAdapterConfig()
    {
        return static::createNode('adapter_id', 'scalar');
    }

    /**
     * Get the service manager adapter node.
     *
     * @return ArrayNodeDefinition
     */
    protected function getServiceManagerAdapterConfig()
    {
        $node = static::createNode('service_manager_adapter');

        $node
            ->children()
                ->scalarNode('manager_id')->isRequired()->defaultNull()->end()
                ->scalarNode('short_name')->isRequired()->defaultNull()->end()
                ->scalarNode('command_prefix')->isRequired()->defaultNull()->end()
                ->scalarNode('command_description')->cannotBeEmpty()->defaultValue('The "%s" command of <comment>"%s"</comment> class')->end()
                ->scalarNode('identifier_field')->cannotBeEmpty()->defaultValue('id')->end()
                ->scalarNode('identifier_argument')->cannotBeEmpty()->defaultValue('identifier')->end()
                ->scalarNode('identifier_argument_description')->cannotBeEmpty()->defaultValue('The unique identifier of %s')->end()
                ->scalarNode('display_name_method')->isRequired()->defaultNull()->end()
                ->scalarNode('new_instance_method')->defaultNull()->end()
                ->scalarNode('create_method')->defaultNull()->end()
                ->scalarNode('get_method')->defaultNull()->end()
                ->scalarNode('update_method')->defaultNull()->end()
                ->scalarNode('delete_method')->defaultNull()->end()
                ->scalarNode('undelete_method')->defaultNull()->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Get the resource adapter node.
     *
     * @return ArrayNodeDefinition
     */
    protected function getResourceAdapterConfig()
    {
        $node = static::createNode('resource_adapter');

        $node
            ->children()
                ->scalarNode('command_prefix')->isRequired()->defaultNull()->end()
                ->scalarNode('command_description')->cannotBeEmpty()->defaultValue('The "%s" command of <comment>"%s"</comment> class')->end()
                ->scalarNode('identifier_field')->cannotBeEmpty()->defaultValue('id')->end()
                ->scalarNode('identifier_argument')->cannotBeEmpty()->defaultValue('identifier')->end()
                ->scalarNode('identifier_argument_description')->cannotBeEmpty()->defaultValue('The unique identifier of %s')->end()
                ->scalarNode('display_name_method')->isRequired()->defaultNull()->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Create the full config of command.
     *
     * @param string $name The command name
     *
     * @return ArrayNodeDefinition
     */
    protected function createCommand($name)
    {
        $node = static::createNode($name);
        $node
            ->canBeEnabled()
            ->children()
                ->append($this->createFieldArguments())
                ->append($this->createFieldOptions())
            ->end()
        ;

        return $node;
    }

    /**
     * Create the field arguments for a command.
     *
     * @return ArrayNodeDefinition
     */
    protected function createFieldArguments()
    {
        $node = static::createNode('field_arguments');
        $node
            ->fixXmlConfig('field_argument')
            ->useAttributeAsKey('name', false)
            ->normalizeKeys(false)
            ->prototype('array')
                ->children()
                    ->integerNode('mode')->defaultNull()->end()
                    ->scalarNode('description')->defaultValue('')->end()
                    ->scalarNode('default')->defaultNull()->end()
                ->end()
            ->end();

        return $node;
    }

    /**
     * Create the field options for a command.
     *
     * @return ArrayNodeDefinition
     */
    protected function createFieldOptions()
    {
        $node = static::createNode('field_options');
        $node
            ->fixXmlConfig('field_option')
            ->useAttributeAsKey('name', false)
            ->normalizeKeys(false)
            ->prototype('array')
                ->children()
                    ->arrayNode('shortcut')
                        ->prototype('scalar')->end()
                        ->beforeNormalization()
                        ->ifString()
                            ->then(function ($v) {
                                return [$v];
                            })
                        ->end()
                    ->end()
                    ->integerNode('mode')->defaultNull()->end()
                    ->scalarNode('description')->defaultValue('')->end()
                    ->scalarNode('default')->defaultNull()->end()
                ->end()
            ->end();

        return $node;
    }

    /**
     * Create the root node.
     *
     * @param string $name The node name
     * @param string $type The type of node
     *
     * @return ArrayNodeDefinition|NodeDefinition
     */
    protected static function createNode($name, $type = 'array')
    {
        $treeBuilder = new TreeBuilder();
        /* @var ArrayNodeDefinition|NodeDefinition $node */
        $node = $treeBuilder->root($name, $type);

        return $node;
    }
}
