<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\DoctrineConsoleBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Command builder.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class CommandBuilder
{
    /**
     * @var array
     */
    protected static $commands = array(
        'view'     => 'Sonatra\Bundle\DoctrineConsoleBundle\Command\View',
        'create'   => 'Sonatra\Bundle\DoctrineConsoleBundle\Command\Create',
        'edit'     => 'Sonatra\Bundle\DoctrineConsoleBundle\Command\Edit',
        'delete'   => 'Sonatra\Bundle\DoctrineConsoleBundle\Command\Delete',
        'undelete' => 'Sonatra\Bundle\DoctrineConsoleBundle\Command\Undelete',
    );

    /**
     * Build the commands.
     *
     * @param ContainerBuilder $container The container builder
     * @param array            $configs   The command configs
     */
    public static function buildCommands(ContainerBuilder $container, array $configs)
    {
        foreach ($configs as $classname => $config) {
            $adapterId = static::buildAdapter($container, $config['adapter'], $classname);

            foreach (array_keys(static::$commands) as $command) {
                $comConf = $config[$command];

                if ($comConf['enabled']) {
                    static::buildCommand($container, $command, $adapterId, $comConf['field_arguments'], $comConf['field_options']);
                }
            }
        }
    }

    /**
     * Build the command adapter.
     *
     * @param ContainerBuilder $container The container builder
     * @param string|array     $config    The service id of command adapter or the config for build an adapter of service manager
     * @param string           $classname The class name
     *
     * @return string The service id of the command adapter
     */
    public static function buildAdapter(ContainerBuilder $container, $config, $classname)
    {
        if (is_array($config)) {
            $id = 'sonatra_doctrine_console.command_adapter.'
                .str_replace(array(':', '-'), '_', $config['command_prefix']);
            $def = new Definition('Sonatra\Bundle\DoctrineConsoleBundle\Adapter\ServiceManagerAdapter');
            $def
                ->addArgument(new Reference($config['service_manager']))
                ->addMethodCall('setClass', array($classname))
                ->addMethodCall('setShortName', array($config['short_name']))
                ->addMethodCall('setCommandPrefix', array($config['command_prefix']))
                ->addMethodCall('setCommandDescription', array(str_replace('%s', '{s}', $config['command_description'])))
                ->addMethodCall('setIdentifierField', array($config['identifier_field']))
                ->addMethodCall('setIdentifierArgument', array($config['identifier_argument']))
                ->addMethodCall('setIdentifierArgumentDescription', array(str_replace('%s', '{s}', $config['identifier_argument_description'])))
                ->addMethodCall('setDisplayNameMethod', array($config['display_name_method']))
                ->addMethodCall('setCreateMethod', array($config['create_method']))
                ->addMethodCall('setGetMethod', array($config['get_method']))
                ->addMethodCall('setUpdateMethod', array($config['update_method']))
                ->addMethodCall('setDeleteMethod', array($config['delete_method']))
                ->addMethodCall('setUndeleteMethod', array($config['undelete_method']))
            ;
            $container->setDefinition($id, $def);

            $config = $id;
        }

        return $config;
    }

    /**
     * Build the command.
     *
     * @param ContainerBuilder $container The container builder
     * @param string           $command   The command action
     * @param string           $adapterId The id of the command adapter service
     * @param array            $arguments The configs of command arguments
     * @param array            $options   The configs of command options
     */
    public static function buildCommand(ContainerBuilder $container, $command, $adapterId, array $arguments = array(), array $options = array())
    {
        $id = str_replace('command_adapter', 'commands', $adapterId).'.'.$command;
        $def = new Definition(static::$commands[$command]);
        $def
            ->addArgument(new Reference('sonatra_doctrine_console.console.object_field_helper'))
            ->addArgument(new Reference($adapterId))
            ->addArgument($arguments)
            ->addArgument($options)
            ->addTag('console.command')
        ;

        $container->setDefinition($id, $def);
    }
}
