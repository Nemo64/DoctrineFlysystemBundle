<?php

namespace Nemo64\DatabaseFlysystemBundle;

use Doctrine\DBAL\Types\Type;
use Nemo64\DatabaseFlysystemBundle\Type\FileType;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class Nemo64DatabaseFlysystemBundle extends Bundle
{
    function __construct()
    {
        if (!Type::hasType(FileType::TYPE)) {
            Type::addType(FileType::TYPE, 'Nemo64\DatabaseFlysystemBundle\Type\FileType');
        }
    }

    public function boot()
    {
    }
}
