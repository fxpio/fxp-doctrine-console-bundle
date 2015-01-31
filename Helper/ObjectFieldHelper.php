<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\DoctrineConsoleBundle\Helper;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Helper for manipulate and validate the doctrine objects in console.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ObjectFieldHelper
{
    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var ValidatorInterface|null
     */
    protected $validator;

    /**
     * @var array
     */
    protected $configs;

    /**
     * Constructor.
     *
     * @param ObjectManager      $objectManager The object manager
     * @param ValidatorInterface $validator     The validator
     */
    public function __construct(ObjectManager $objectManager, ValidatorInterface $validator = null)
    {
        $this->om = $objectManager;
        $this->validator = $validator;
        $this->configs = array();
    }

    /**
     * Get the object configs.
     *
     * @param string|object $className The class name or the instance
     *
     * @return array The config fields and config associations
     */
    public function getConfigs($className)
    {
        if (is_object($className)) {
            $className = get_class($className);
        }

        if (!array_key_exists($className, $this->configs)) {
            $meta = $this->om->getClassMetadata($className);
            $fieldList = $meta->getFieldNames();
            $associationList = $meta->getAssociationNames();
            $configFields = array();
            $configAssociations = array();

            foreach ($fieldList as $field) {
                if (!$meta->isIdentifier($field)) {
                    $configFields[$field] = $meta->getTypeOfField($field);
                }
            }

            foreach ($associationList as $association) {
                if (!$meta->isAssociationInverseSide($association)
                    && $meta->isSingleValuedAssociation($association)) {
                    $configAssociations[$association] = $meta->getAssociationTargetClass($association);
                }
            }

            $this->configs[$className] = array($configFields, $configAssociations);
        }

        return $this->configs[$className];
    }

    /**
     * Inject the fields options in command definition.
     *
     * @param InputDefinition $definition The console input definition
     * @param string          $className  The class name or the instance
     */
    public function injectFieldOptions(InputDefinition $definition, $className)
    {
        list($fields, $associations) = $this->getConfigs($className);

        foreach ($fields as $field => $type) {
            $mode = 'array' === $type
                ? InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY
                : InputOption::VALUE_REQUIRED;

            if (!$definition->hasOption($field) && !$definition->hasArgument($field)) {
                $definition->addOption(new InputOption($field, null, $mode, sprintf('The <comment>"%s"</comment> field', $field)));
            }
        }

        foreach ($associations as $association => $classname) {
            if (!$definition->hasOption($association) && !$definition->hasArgument($association)) {
                $definition->addOption(new InputOption($association, null, InputOption::VALUE_REQUIRED, sprintf('The <comment>"%s"</comment> association identifier of <comment>"%s"</comment>', $association, $classname)));
            }
        }
    }

    /**
     * Inject the field values in the object instance.
     *
     * @param InputInterface $input    The console input
     * @param object         $instance The object instance
     * @param string         $targetId The doctrine identifier name of association target
     */
    public function injectNewValues(InputInterface $input, $instance, $targetId = 'id')
    {
        list($fields, $associations) = $this->getConfigs($instance);
        $fieldNames = array_keys(array_merge($fields, $associations));

        foreach ($fieldNames as $fieldName) {
            $value = $this->getFieldValue($input, $fieldName);

            if (empty($value)) {
                continue;
            }

            $value = $this->convertEmptyValue($value);

            if ((array_key_exists($fieldName, $fields))) {
                $this->setFieldValue($instance, $fieldName, $value);
                continue;
            }

            if ((array_key_exists($fieldName, $associations))) {
                $this->setAssociationValue($instance, $fieldName, $value, $associations[$fieldName], $targetId);
                continue;
            }

            throw new \InvalidArgumentException(sprintf('The field "%s" seems not to exist in your "%s" class.', $fieldName, get_class($instance)));
        }
    }

    /**
     * Validate the object instance.
     *
     * @param object $instance The object instance
     *
     * @throws ValidatorException When an error exist
     */
    public function validateObject($instance)
    {
        if (null !== $this->validator) {
            $errorList = $this->validator->validate($instance);

            if (count($errorList) > 0) {
                $msg = sprintf('Field validation errors for "%s":', get_class($instance));

                foreach ($errorList as $error) {
                    $msg .= sprintf('%s  - %s: %s', PHP_EOL, $error->getPropertyPath(), $error->getMessage());
                }

                throw new ValidatorException($msg);
            }
        }
    }

    /**
     * Get field value in console input.
     *
     * @param InputInterface $input     The console input
     * @param string         $fieldName The field name
     *
     * @return mixed|null
     */
    protected function getFieldValue(InputInterface $input, $fieldName)
    {
        $value = null;

        if ($input->hasArgument($fieldName)) {
            $value = $input->getArgument($fieldName);
        } elseif ($input->hasOption($fieldName)) {
            $value = $input->getOption($fieldName);
        }

        return $value;
    }

    /**
     * Convert the magic "{{null}}" to null value and "{{empty}}" to array value.
     *
     * @param mixed $value
     *
     * @return mixed|null
     */
    protected function convertEmptyValue($value)
    {
        if ('{{null}}' === $value) {
            $value = null;
        } elseif ('{{empty}}' === $value) {
            $value = array();
        }

        return $value;
    }

    /**
     * Set the field value.
     *
     * @param mixed      $instance  The object instance
     * @param string     $fieldName The field name
     * @param mixed|null $value     The field value
     */
    protected function setFieldValue($instance, $fieldName, $value)
    {
        $setterMethodName = "set".ucfirst($fieldName);

        try {
            $ref = new \ReflectionClass($instance);
            $ref->getMethod($setterMethodName);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(sprintf('The setter method "%s" that should be used for property "%s" seems not to exist. Please check your spelling in the command option or in your implementation class.', $setterMethodName, $fieldName));
        }

        $instance->$setterMethodName($value);
    }

    /**
     * Set the association field value.
     *
     * @param object     $instance  The object instance
     * @param string     $fieldName The field name
     * @param mixed|null $value     The field value
     * @param string     $target    The target class name
     * @param string     $id        The doctrine identifier name
     */
    protected function setAssociationValue($instance, $fieldName, $value, $target, $id)
    {
        $targetRepo = $this->om->getRepository($target);
        $target = $targetRepo->findBy(array($id => $value));

        if (null === $target) {
            throw new \InvalidArgumentException(sprintf('The specified mapped field "%s" couldn\'t be found with the Id "%s".', $fieldName, $value));
        }

        $this->setFieldValue($instance, $fieldName, $target[0]);
    }
}
