<?php

namespace LibBundle\Doctrine;

use Doctrine\Common\Collections\Criteria;

class EntityRepository extends \Doctrine\ORM\EntityRepository
{
    public function match(Criteria $criteria = null, array $associations = array())
    {
        $reader = new EntityRepositoryReader($this);
        $reader->setCriteria($criteria);
        $reader->setAssociations($associations);

        return $reader->match();
    }

    public function count(Criteria $criteria = null, array $associations = array())
    {
        $reader = new EntityRepositoryReader($this);
        $reader->setCriteria($criteria);
        $reader->setAssociations($associations);

        return $reader->count();
    }

    public function add($data = null, array $associations = array())
    {
        $writer = new EntityRepositoryWriter($this, $this->_em);
        $writer->setData($data);
        $writer->setAssociations($associations);

        $writer->add();
        if (ObjectPersister::isAutoFlush()) {
            $this->_em->flush();
        } else {
            ObjectPersister::addObjectManager($this->_em);
        }
    }

    public function update($data = null, array $associations = array())
    {
        $writer = new EntityRepositoryWriter($this, $this->_em);
        $writer->setData($data);
        $writer->setAssociations($associations);

        $writer->update();
        if (ObjectPersister::isAutoFlush()) {
            $this->_em->flush();
        } else {
            ObjectPersister::addObjectManager($this->_em);
        }
    }

    public function remove($data = null, array $associations = array())
    {
        $writer = new EntityRepositoryWriter($this, $this->_em);
        $writer->setData($data);
        $writer->setAssociations($associations);

        $writer->remove();
        if (ObjectPersister::isAutoFlush()) {
            $this->_em->flush();
        } else {
            ObjectPersister::addObjectManager($this->_em);
        }
    }
}
