<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\DoctrineConsoleBundle\Command;

use Sonatra\Bundle\DoctrineConsoleBundle\Adapter\AdapterInterface;
use Sonatra\Bundle\DoctrineConsoleBundle\Helper\ObjectFieldHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class Base extends ContainerAwareCommand
{
    /**
     * @var ObjectFieldHelper
     */
    protected $helper;

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var string
     */
    protected $action = 'INVALID_ACTION';

    /**
     * @var array
     */
    protected $configArguments;

    /**
     * @var array
     */
    protected $configOptions;

    /**
     * Constructor.
     *
     * @param ObjectFieldHelper $helper          The doctrine console object field helper
     * @param AdapterInterface  $adapter         The command adapter
     * @param array             $configArguments The config of custom command arguments
     * @param array             $configOptions   The config of custom command options
     */
    public function __construct(ObjectFieldHelper $helper, AdapterInterface $adapter, array $configArguments = array(), array $configOptions = array())
    {
        $this->helper = $helper;
        $this->adapter = $adapter;
        $this->configArguments = $configArguments;
        $this->configOptions = $configOptions;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $adp = $this->adapter;
        $this
            ->setName($adp->getCommandPrefix().':'.$this->action)
            ->setDescription(sprintf($adp->getCommandDescription(), $this->action, $adp->getClass()))
        ;

        foreach ($this->configArguments as $name => $config) {
            $this->addArgument($name, $config['mode'], $config['description'], $config['default']);
        }

        foreach ($this->configOptions as $name => $config) {
            $this->addOption($name, $config['shortcut'], $config['mode'], $config['description'], $config['default']);
        }

        if ('create' !== $this->action && !$this->getDefinition()->hasArgument($adp->getIdentifierArgument())) {
            $this->addArgument($adp->getIdentifierArgument(), InputArgument::REQUIRED, sprintf($adp->getIdentifierArgumentDescription(), $adp->getShortName()));
        }
    }
}
