<?php

namespace Nemo64\DoctrineFlysystemBundle;

use Doctrine\DBAL\Types\Type;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class Nemo64DoctrineFlysystemBundle extends Bundle
{
    function __construct()
    {
        if (!Type::hasType('flyfile')) {
            Type::addType('flyfile', 'Nemo64\DoctrineFlysystemBundle\Type\FileType');
        }
    }

    public function boot()
    {
    }
}
