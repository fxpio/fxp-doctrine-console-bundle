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
class Undelete extends Base
{
    /**
     * {@inheritdoc}
     */
    protected $action = 'undelete';

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $methodGet = $this->adapter->getDisplayNameMethod();
        $id = $input->getArgument($this->adapter->getIdentifierArgument());

        $instance = $this->adapter->undelete($id);

        $output->writeln(array(
            '',
            sprintf('Undeleted the %s: <info>%s</info>', $this->adapter->getShortName(), $instance->$methodGet()),
        ));
    }
}
