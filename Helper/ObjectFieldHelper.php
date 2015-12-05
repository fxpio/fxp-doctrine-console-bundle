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
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Sonatra\Bundle\DoctrineConsoleBundle\Util\ObjectFieldUtil;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
            $this->configs[$className] = array(array(), array());
            $meta = $this->om->getClassMetadata($className);
            $this->addConfigFields($meta, $className);
            $this->addConfigAssociations($meta, $className);
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

        ObjectFieldUtil::addOptions($definition, $fields, 'The <comment>"%s"</comment> field');
        ObjectFieldUtil::addOptions($definition, $associations, 'The <comment>"%s"</comment> association identifier of <comment>"%s"</comment>');
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
            $value = ObjectFieldUtil::getFieldValue($input, $fieldName);

            if (empty($value)) {
                continue;
            }

            $value = ObjectFieldUtil::convertEmptyValue($value);

            if ((array_key_exists($fieldName, $fields))) {
                ObjectFieldUtil::setFieldValue($instance, $fieldName, $value);
            } elseif ((array_key_exists($fieldName, $associations))) {
                $this->setAssociationValue($instance, $fieldName, $value, $associations[$fieldName], $targetId);
            }
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
            /* @var ConstraintViolationInterface[] $errorList */
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
     * Set the association field value.
     *
     * @param object     $instance  The object instance
     * @param string     $fieldName The field name
     * @param mixed|null $value     The field value
     * @param string     $target    The target class name
     * @param string     $id        The doctrine identifier name
     */
    private function setAssociationValue($instance, $fieldName, $value, $target, $id)
    {
        $targetRepo = $this->om->getRepository($target);
        $target = $targetRepo->findBy(array($id => $value));

        if (null === $target) {
            throw new \InvalidArgumentException(sprintf('The specified mapped field "%s" couldn\'t be found with the Id "%s".', $fieldName, $value));
        }

        ObjectFieldUtil::setFieldValue($instance, $fieldName, $target[0]);
    }

    /**
     * Add the config of fields.
     *
     * @param ClassMetadata $metadata  The doctrine metadata
     * @param string        $className The class name
     */
    private function addConfigFields(ClassMetadata $metadata, $className)
    {
        foreach ($metadata->getFieldNames() as $field) {
            if (!$metadata->isIdentifier($field)) {
                $this->configs[$className][0][$field] = $metadata->getTypeOfField($field);
            }
        }
    }

    /**
     * Add the config of associations.
     *
     * @param ClassMetadata $metadata  The doctrine metadata
     * @param string        $className The class name
     */
    private function addConfigAssociations(ClassMetadata $metadata, $className)
    {
        foreach ($metadata->getAssociationNames() as $association) {
            if (!$metadata->isAssociationInverseSide($association)
                && $metadata->isSingleValuedAssociation($association)) {
                $this->configs[$className][1][$association] = $metadata->getAssociationTargetClass($association);
            }
        }
    }
}
