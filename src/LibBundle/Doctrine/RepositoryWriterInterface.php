<?php

namespace LibBundle\Doctrine;

interface RepositoryWriterInterface
{
    public function add();

    public function update();

    public function remove();
}
