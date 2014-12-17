<?php
/**
 * Created by PhpStorm.
 * User: marco
 * Date: 18.12.14
 * Time: 21:25
 */

namespace Nemo64\DatabaseFlysystemBundle\EventArgs;


use Doctrine\Common\EventArgs;
use League\Flysystem\File;

class FileEventArgs extends EventArgs
{
    /**
     * @var File
     */
    private $file;

    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * @return File
     */
    public function getFile()
    {
        return $this->file;
    }
}