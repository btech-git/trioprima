<?php

namespace LibBundle\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

class EntityToIdTransformer implements DataTransformerInterface
{
    private $objectManager;
    private $className;

    public function __construct(ObjectManager $objectManager, $className)
    {
        $this->objectManager = $objectManager;
        $this->className = $className;
    }

    public function transform($entity = null)
    {
        if (null === $entity) {
            return '';
        }
        
        if (!($entity instanceof $this->className)) {
            throw new TransformationFailedException(sprintf(
                'An entity is not an instance of "%s"!', $this->className
            ));
        }

        return $entity->getId();
    }

    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }

        $entity = $this->objectManager->find($this->className, $id);

        if (null === $entity) {
            throw new TransformationFailedException(sprintf(
                'An entity "%s" with id "%d" does not exist!', $this->className, $id
            ));
        }

        return $entity;
    }
}
