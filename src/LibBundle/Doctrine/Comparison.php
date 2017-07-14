<?php

namespace LibBundle\Doctrine;

class Comparison
{
    const EQ        = '=';
    const NEQ       = '<>';
    const LT        = '<';
    const LTE       = '<=';
    const GT        = '>';
    const GTE       = '>=';
    const IS        = '='; // no difference with EQ
    const IN        = 'IN';
    const NIN       = 'NIN';
    const CONTAINS  = 'CONTAINS';
    const NCONTAINS = 'NCONTAINS';
    const STARTS_WITH = 'STARTS_WITH';
    const N_STARTS_WITH = 'N_STARTS_WITH';
    const ENDS_WITH = 'ENDS_WITH';
    const N_ENDS_WITH = 'N_ENDS_WITH';
}
