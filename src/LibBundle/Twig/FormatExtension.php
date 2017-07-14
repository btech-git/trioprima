<?php

namespace LibBundle\Twig;

use LibBundle\Util\NumberWord;

class FormatExtension extends \Twig_Extension
{
    public function getName()
    {
        return 'format_extension';
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('say', array($this, 'filterSay')),
        );
    }

    public function filterSay($number)
    {
        return NumberWord::name($number);
    }
}
