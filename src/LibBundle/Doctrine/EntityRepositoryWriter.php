<?php

namespace LibBundle\Doctrine;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;

class EntityRepositoryWriter implements RepositoryWriterInterface
{
    private $repository;
    private $entityManager;
    private $data = null;
    private $associations = array();

    public function __construct(EntityRepository $repository, EntityManager $entityManager)
    {
        $this->repository = $repository;
        $this->entityManager = $entityManager;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setAssociations(array $associations)
    {
        $this->associations = $associations;
    }

    public function add()
    {
        if (!$this->validateData()) {
            return;
        }

        $this->save(true, false);
    }

    public function update()
    {
        if (!$this->validateData()) {
            return;
        }

        $this->save(false, false);
    }

    public function remove()
    {
        if (!$this->validateData()) {
            return;
        }

        $this->save(false, true);
    }

    private function save($add, $remove)
    {
        $entityName = lcfirst((new \ReflectionClass($this->repository->getClassName()))->getShortName());
        $associations = array($entityName => array('add' => $add, 'remove' => $remove, 'associations' => $this->associations));
        $roots = $this->isCollection($this->data) ? $this->data : array($this->data);
        foreach ($roots as $root) {
            $currentObjects = array();
            $oldObjects = array();
            $this->listCurrentObjects($currentObjects, $associations, $root);
            $this->listOldObjects($oldObjects, $associations, $root);
            foreach ($oldObjects as $key => $item) {
                if ($item['remove'] && (!isset($currentObjects[$key]) || $key === spl_object_hash($root))) {
                    $this->entityManager->remove($item['data']);
                }
            }
            foreach ($currentObjects as $key => $item) {
                if ($item['add'] && (!isset($oldObjects[$key]) || $key === spl_object_hash($root))) {
                    $this->entityManager->persist($item['data']);
                }
            }
        }
    }

    private function listCurrentObjects(&$list, $associations, $root, $parent = null)
    {
        foreach ($associations as $name => $item) {
            $add = isset($item['add']) ? $item['add'] : false;
            $remove = isset($item['remove']) ? $item['remove'] : false;
            $getter = 'get' . ucfirst($name);
            $data = ($parent === null) ? $root : $parent->$getter();
            if ($data instanceof Collection) {
                foreach ($data as $object) {
                    $list[spl_object_hash($object)] = array('add' => $add, 'remove' => $remove, 'data' => $object);
                    if (!empty($item['associations'])) {
                        $this->listCurrentObjects($list, $item['associations'], $root, $object);
                    }
                }
            } else if ($this->isEntityOrNull($data)) {
                if ($data !== null) {
                    $list[spl_object_hash($data)] = array('add' => $add, 'remove' => $remove, 'data' => $data);
                    if (!empty($item['associations'])) {
                        $this->listCurrentObjects($list, $item['associations'], $root, $data);
                    }
                }
            }
        }
    }

    private function listOldObjects(&$list, $associations, $root, $parent = null)
    {
        foreach ($associations as $name => $item) {
            $add = isset($item['add']) ? $item['add'] : false;
            $remove = isset($item['remove']) ? $item['remove'] : false;
            $getter = 'get' . ucfirst($name);
            if ($parent !== null) {
                $fieldMappings = $this->entityManager->getClassMetadata(get_class($parent))->getAssociationMappings();
                $entityClass = $fieldMappings[$name]['targetEntity'];
                if (isset($fieldMappings[$name]['mappedBy'])) {
                    $parentName = $fieldMappings[$name]['mappedBy'];
                    $oldData = $this->entityManager->getRepository($entityClass)->findBy(array($parentName => $parent));
                } else if (isset($fieldMappings[$name]['inversedBy'])) {
                    $id = $parent->$getter()->getId();
                    if ($id === null) {
                        $id = '';
                    }
                    $oldData = $this->entityManager->getRepository($entityClass)->find($id);
                }
            } else {
                $oldData = $root;
            }
            if ($this->isCollection($oldData)) {
                foreach ($oldData as $oldObject) {
                    $list[spl_object_hash($oldObject)] = array('add' => $add, 'remove' => $remove, 'data' => $oldObject);
                    if (!empty($item['associations'])) {
                        $this->listOldObjects($list, $item['associations'], $root, $oldObject);
                    }
                }
            } else if ($this->isEntityOrNull($oldData)) {
                if ($oldData !== null) {
                    $list[spl_object_hash($oldData)] = array('add' => $add, 'remove' => $remove, 'data' => $oldData);
                    if (!empty($item['associations'])) {
                        $this->listOldObjects($list, $item['associations'], $root, $oldData);
                    }
                }
            }
        }
    }

    private function validateData()
    {
        if (empty($this->data)) {
            return false;
        }
        if (is_array($this->data)) {
            $this->data = new ArrayCollection($this->data);
        }
        $entityClass = $this->repository->getClassName();
        $isObject = $this->data instanceof $entityClass;
        $forAllObject = function($key, $element) use ($entityClass) { return $element instanceof $entityClass; };
        $isCollection = $this->data instanceof Collection && $this->data->forAll($forAllObject);
        if (!$isObject && !$isCollection) {
            return false;
        }

        return true;
    }

    private function isEntity($object)
    {
        if (!is_object($object)) {
            return false;
        }

        $class = ClassUtils::getClass($object);

        return !$this->entityManager->getMetadataFactory()->isTransient($class);
    }

    private function isEntityOrNull($object)
    {
        return ($object === null) || $this->isEntity($object);
    }

    private function isCollection($object)
    {
        return is_array($object) || ($object instanceof Collection);
    }
}
