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
class Edit extends BaseHelper
{
    /**
     * {@inheritdoc}
     */
    protected $action = 'edit';

    /**
     * {@inheritdoc}
     */
    protected function getInstance(InputInterface $input)
    {
        $id = $input->getArgument($this->adapter->getIdentifierArgument());

        return $this->adapter->get($id);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDisplayPattern()
    {
        return 'Updated the %s: <info>%s</info>';
    }
}
