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

use Sonatra\Bundle\DoctrineConsoleBundle\Helper\DetailObjectHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class View extends Base
{
    /**
     * {@inheritdoc}
     */
    protected $action = 'view';

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument($this->adapter->getIdentifierArgument());
        $instance = $this->adapter->get($id);

        $output->writeln(array(
            '',
            '<info>Details of '.$this->adapter->getShortName().':</info>',
            '',
        ));

        DetailObjectHelper::display($output, $instance);
        $output->writeln('');
    }
}
