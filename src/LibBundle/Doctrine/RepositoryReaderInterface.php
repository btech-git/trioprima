<?php

namespace LibBundle\Doctrine;

interface RepositoryReaderInterface
{
    public function match();

    public function count();
}
