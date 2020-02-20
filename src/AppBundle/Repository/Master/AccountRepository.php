<?php

namespace AppBundle\Repository\Master;

use LibBundle\Doctrine\EntityRepository;

class AccountRepository extends EntityRepository
{
    public function findReceivableRecord()
    {
        return $this->find(158);
    }
    
    public function findPayableRecord()
    {
        return $this->find(164);
    }
}