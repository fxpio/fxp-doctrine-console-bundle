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

use Fxp\Component\DoctrineConsole\Adapter\ResourceAdapter;
use Fxp\Component\DoctrineConsole\Adapter\ServiceManagerAdapter;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\Expression;

/**
 * Command builder.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
abstract class CommandBuilder
{
    /**
     * @var array
     */
    private static $commands = [
        'view' => 'Fxp\Component\DoctrineConsole\Command\View',
        'create' => 'Fxp\Component\DoctrineConsole\Command\Create',
        'update' => 'Fxp\Component\DoctrineConsole\Command\Update',
        'delete' => 'Fxp\Component\DoctrineConsole\Command\Delete',
        'undelete' => 'Fxp\Component\DoctrineConsole\Command\Undelete',
    ];

    /**
     * Build the commands.
     *
     * @param ContainerBuilder $container The container builder
     * @param array            $configs   The command configs
     */
    public static function buildCommands(ContainerBuilder $container, array $configs)
    {
        foreach ($configs as $classname => $config) {
            $adapterId = self::buildAdapter($container, $config, $classname);

            foreach (array_keys(self::$commands) as $command) {
                $comConf = $config[$command];

                if ($comConf['enabled']) {
                    self::buildCommand($container, $command, $adapterId, $comConf['field_arguments'], $comConf['field_options']);
                }
            }
        }
    }

    /**
     * Build the command adapter.
     *
     * @param ContainerBuilder $container The container builder
     * @param array            $config    The config of command
     * @param string           $classname The class name
     *
     * @return string The service id of the command adapter
     */
    private static function buildAdapter(ContainerBuilder $container, array $config, $classname)
    {
        if (isset($config['adapter_id'])) {
            $id = $config['adapter_id'];
        } elseif (isset($config['service_manager_adapter'])) {
            $id = self::buildServiceManagerAdapter($container, $config['service_manager_adapter'], $classname);
        } elseif (isset($config['resource_adapter'])) {
            $id = self::buildResourceAdapter($container, $config['resource_adapter'], $classname);
        } else {
            throw new InvalidConfigurationException(sprintf('An adapter must be configured on "fxp_doctrine_console.commands.%s". Available adapters: "%s"', $classname, implode('", "', Configuration::getAdapters())));
        }

        return $id;
    }

    /**
     * Build the service manager adapter.
     *
     * @param ContainerBuilder $container The container builder
     * @param string|array     $config    The config to build an adapter of service manager
     * @param string           $classname The class name
     *
     * @return string The service id of the command adapter
     */
    private static function buildServiceManagerAdapter(ContainerBuilder $container, array $config, $classname)
    {
        $id = self::buildAdapterId($config['command_prefix']);
        $def = new Definition(ServiceManagerAdapter::class);
        $def
            ->addArgument(new Reference($config['manager_id']))
            ->addArgument(new Reference('validator', ContainerInterface::IGNORE_ON_INVALID_REFERENCE))
            ->addMethodCall('setClass', [$classname])
            ->addMethodCall('setShortName', [$config['short_name']])
            ->addMethodCall('setCommandPrefix', [$config['command_prefix']])
            ->addMethodCall('setCommandDescription', [str_replace('%s', '{s}', $config['command_description'])])
            ->addMethodCall('setIdentifierField', [$config['identifier_field']])
            ->addMethodCall('setIdentifierArgument', [$config['identifier_argument']])
            ->addMethodCall('setIdentifierArgumentDescription', [str_replace('%s', '{s}', $config['identifier_argument_description'])])
            ->addMethodCall('setDisplayNameMethod', [$config['display_name_method']])
            ->addMethodCall('setNewInstanceMethod', [$config['new_instance_method']])
            ->addMethodCall('setCreateMethod', [$config['create_method']])
            ->addMethodCall('setGetMethod', [$config['get_method']])
            ->addMethodCall('setUpdateMethod', [$config['update_method']])
            ->addMethodCall('setDeleteMethod', [$config['delete_method']])
            ->addMethodCall('setUndeleteMethod', [$config['undelete_method']])
        ;
        $container->setDefinition($id, $def);

        return $id;
    }

    /**
     * Build the resource adapter.
     *
     * @param ContainerBuilder $container The container builder
     * @param string|array     $config    The config to build an adapter of resource domain
     * @param string           $classname The class name
     *
     * @return string The service id of the command adapter
     */
    private static function buildResourceAdapter(ContainerBuilder $container, array $config, $classname)
    {
        $id = self::buildAdapterId($config['command_prefix']);
        $def = new Definition(ResourceAdapter::class);
        $def
            ->addArgument(new Expression('service("fxp_resource.domain_manager").get("'.str_replace('\\', '\\\\', $classname).'")'))
            ->addMethodCall('setCommandPrefix', [$config['command_prefix']])
            ->addMethodCall('setCommandDescription', [str_replace('%s', '{s}', $config['command_description'])])
            ->addMethodCall('setIdentifierField', [$config['identifier_field']])
            ->addMethodCall('setIdentifierArgument', [$config['identifier_argument']])
            ->addMethodCall('setIdentifierArgumentDescription', [str_replace('%s', '{s}', $config['identifier_argument_description'])])
            ->addMethodCall('setDisplayNameMethod', [$config['display_name_method']])
        ;
        $container->setDefinition($id, $def);

        return $id;
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
    private static function buildCommand(ContainerBuilder $container, $command, $adapterId, array $arguments = [], array $options = [])
    {
        $id = str_replace('command_adapter', 'commands', $adapterId).'.'.$command;
        $def = new Definition(self::$commands[$command]);
        $def
            ->addArgument(new Reference('fxp_doctrine_console.console.object_field_helper'))
            ->addArgument(new Reference($adapterId))
            ->addArgument($arguments)
            ->addArgument($options)
            ->addTag('console.command')
        ;

        $container->setDefinition($id, $def);
    }

    /**
     * Build the service id of command adapter.
     *
     * @param string $commandPrefix The command prefix
     *
     * @return string
     */
    private static function buildAdapterId($commandPrefix)
    {
        return 'fxp_doctrine_console.command_adapter.'
        .str_replace([':', '-'], '_', $commandPrefix);
    }
}
