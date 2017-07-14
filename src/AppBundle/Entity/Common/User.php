<?php

namespace AppBundle\Entity\Common;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="common_user")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"staff" = "AppBundle\Entity\Admin\Staff"})
 * @UniqueEntity("username")
 */
abstract class User implements UserInterface, EquatableInterface, \Serializable
{
    /**
     * @ORM\Column(name="id", type="integer") @ORM\Id @ORM\GeneratedValue
     */
    protected $id;
    /**
     * @ORM\Column(name="username", type="string", length=25, unique=true)
     * @Assert\NotBlank() @Assert\Length(max=25)
     */
    protected $username;
    /**
     * @ORM\Column(name="password", type="string", length=64)
     * @Assert\NotBlank()
     */
    protected $password;
    /**
     * @ORM\ManyToMany(targetEntity="UserRole", inversedBy="users") @ORM\JoinTable(name="common_user_user_role")
     */
    protected $userRoles;
    
    public function __construct()
    {
        $this->userRoles = new ArrayCollection();
    }

    public function getId() { return $this->id; }
    
    public function getUsername() { return $this->username; }
    public function setUsername($username) { $this->username = $username; }
    
    public function getPassword() { return $this->password; }
    public function setPassword($password) { $this->password = $password; }
    
    public function getUserRoles() { return $this->userRoles; }
    public function setUserRoles(Collection $userRoles) { $this->userRoles = $userRoles; }
    
    public function getSalt()
    {
        return null;
    }
    
    public function getRoles()
    {
        return array('ROLE_USER');
    }
    
    public function eraseCredentials()
    {
    }
    
    public function isEqualTo(UserInterface $user)
    {
        return get_class($this) === get_class($user) && $this->getId() === $user->getId();
    }
    
    public function serialize()
    {
        return serialize(array(
            $this->id,
        ));
    }

    public function unserialize($serialized)
    {
        list(
            $this->id,
        ) = unserialize($serialized);
    }
}
