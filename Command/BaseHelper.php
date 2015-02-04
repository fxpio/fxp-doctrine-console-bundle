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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class BaseHelper extends Base
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->helper->injectFieldOptions($this->getDefinition(), $this->adapter->getClass());
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $methodGet = $this->adapter->getDisplayNameMethod();
        $instance = $this->getInstance($input);

        $this->helper->injectNewValues($input, $instance);
        $this->helper->validateObject($instance);
        $this->adapter->update($instance);

        $output->writeln(array(
            '',
            sprintf($this->getDisplayPattern(), $this->adapter->getShortName(), $instance->$methodGet()),
        ));
    }

    /**
     * Get the instance of object.
     *
     * @param InputInterface $input The input console
     *
     * @return object
     */
    abstract protected function getInstance(InputInterface $input);

    /**
     * Get the display pattern for the output console.
     *
     * @return string
     */
    abstract protected function getDisplayPattern();
}
