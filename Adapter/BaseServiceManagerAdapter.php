<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\DoctrineConsoleBundle\Adapter;

/**
 * Base of Command Adapter for service manager.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class BaseServiceManagerAdapter implements AdapterInterface
{
    /**
     * @var object
     */
    protected $manager;

    /**
     * @var string
     */
    protected $classname;

    /**
     * @var string
     */
    protected $shortName;

    /**
     * @var string
     */
    protected $commandPrefix;

    /**
     * @var string
     */
    protected $commandDescription;

    /**
     * @var string
     */
    protected $identifierField;

    /**
     * @var string
     */
    protected $identifierArgument;

    /**
     * @var string
     */
    protected $identifierArgumentDescription;

    /**
     * @var string
     */
    protected $displayNameMethod;

    /**
     * @var string|null
     */
    protected $createMethod;

    /**
     * @var string|null
     */
    protected $getMethod;

    /**
     * @var string|null
     */
    protected $updateMethod;

    /**
     * @var string|null
     */
    protected $deleteMethod;

    /**
     * @var string|null
     */
    protected $undeleteMethod;

    /**
     * Constructor.
     *
     * @param object $manager
     */
    public function __construct($manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        $this->validate('create');

        return $this->manager->{$this->createMethod}();
    }

    /**
     * {@inheritdoc}
     */
    public function get($identifier)
    {
        $this->validate('get');

        $instance = $this->manager->{$this->getMethod}(array($this->getIdentifierField() => $identifier));

        if (null === $instance) {
            throw new \InvalidArgumentException(sprintf('The %s with the identifier "%s" does not exist', $this->getShortName(), $identifier));
        }

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function update($instance)
    {
        $this->validate('update');
        $this->manager->{$this->updateMethod}($instance);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($instance)
    {
        $this->validate('delete');
        $this->manager->{$this->deleteMethod}($instance);
    }

    /**
     * {@inheritdoc}
     */
    public function undelete($identifier)
    {
        $this->validate('undelete');

        return $this->manager->{$this->undeleteMethod}($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        return $this->classname;
    }

    /**
     * {@inheritdoc}
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommandPrefix()
    {
        return $this->commandPrefix;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommandDescription()
    {
        return $this->commandDescription;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierField()
    {
        return $this->identifierField;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierArgument()
    {
        return $this->identifierArgument;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierArgumentDescription()
    {
        return $this->identifierArgumentDescription;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayNameMethod()
    {
        return $this->displayNameMethod;
    }

    /**
     * Validate the adapter method.
     *
     * @param string $method The method name
     *
     * @throws \RuntimeException When the method does not supported
     */
    private function validate($method)
    {
        $actionMethod = $method.'Method';
        $ref = new \ReflectionClass($this->manager);

        if (null === $this->$actionMethod || !$ref->hasMethod($this->$actionMethod)) {
            throw new \RuntimeException(sprintf('The "%s" method for "%s" adapter is does not supported', $method, $this->getClass()));
        }
    }
}
