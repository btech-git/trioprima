<?php

namespace AppBundle\Entity\Common;

use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;

class UserPassword
{
    /**
     * @SecurityAssert\UserPassword(message = "Wrong value for your current password")
     */
    private $oldPassword;
    /**
     * @Assert\NotBlank() @Assert\Length(min = 6, minMessage = "Password should be at least 6 characters long")
     */
    private $newPassword;

    public function getOldPassword() { return $this->oldPassword; }
    public function setOldPassword($oldPassword) { $this->oldPassword = $oldPassword; }
    
    public function getNewPassword() { return $this->newPassword; }
    public function setNewPassword($newPassword) { $this->newPassword = $newPassword; }
}
