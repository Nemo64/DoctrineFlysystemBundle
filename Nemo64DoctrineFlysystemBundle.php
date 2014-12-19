<?php

namespace Nemo64\DoctrineFlysystemBundle;

use Doctrine\DBAL\Types\Type;
use Nemo64\DoctrineFlysystemBundle\Type\FileType;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class Nemo64DoctrineFlysystemBundle extends Bundle
{
    function __construct()
    {
        if (!Type::hasType(FileType::TYPE)) {
            Type::addType(FileType::TYPE, 'Nemo64\DoctrineFlysystemBundle\Type\FileType');
        }
    }

    public function boot()
    {
    }
}
