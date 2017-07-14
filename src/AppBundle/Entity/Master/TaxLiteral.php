<?php

namespace AppBundle\Entity\Master;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="master_tax_literal") @ORM\Entity
 */
class TaxLiteral
{
    /**
     * @ORM\Column(name="id", type="integer") @ORM\Id @ORM\GeneratedValue
     */
    private $id;
    /**
     * @ORM\Column(name="code", type="string", length=60)
     * @Assert\NotBlank()
     */
    private $code;
    
    public function getId() { return $this->id; }
    
    public function getCode() { return $this->code; }
    public function setCode($code) { $this->code = $code; }
}
