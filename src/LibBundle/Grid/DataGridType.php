<?php

namespace LibBundle\Grid;

use Doctrine\Common\Persistence\ObjectRepository;

abstract class DataGridType
{
    public abstract function buildWidgets(WidgetsBuilder $builder, array $options);
    
    public abstract function buildData(DataBuilder $builder, ObjectRepository $repository, array $options);
}
