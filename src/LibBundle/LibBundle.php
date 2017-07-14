<?php

namespace LibBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class LibBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new DependencyInjection\LibExtension();
    }
}
