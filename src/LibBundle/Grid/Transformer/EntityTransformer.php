<?php

namespace LibBundle\Grid\Transformer;

use Doctrine\Common\Persistence\ObjectManager;

class EntityTransformer implements DataTransformerInterface
{
    private $objectManager;
    private $className;

    public function __construct(ObjectManager $objectManager, $className)
    {
        $this->objectManager = $objectManager;
        $this->className = $className;
    }
    
    public function toView($entity)
    {
        return $entity === null ? '' : $entity->getId();
    }
    
    public function toModel($id)
    {
        return $id === '' ? null : $this->objectManager->find($this->className, $id);
    }
}
