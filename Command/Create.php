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

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class Create extends BaseHelper
{
    /**
     * {@inheritdoc}
     */
    protected $action = 'create';

    /**
     * {@inheritdoc}
     */
    protected function getInstance(InputInterface $input)
    {
        return $this->adapter->create();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDisplayPattern()
    {
        return 'Created the %s: <info>%s</info>';
    }
}
