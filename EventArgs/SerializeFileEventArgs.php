<?php
/**
 * Created by PhpStorm.
 * User: marco
 * Date: 18.12.14
 * Time: 21:26
 */

namespace Nemo64\DoctrineFlysystemBundle\EventArgs;


use League\Flysystem\File;
use League\Flysystem\Filesystem;

class SerializeFileEventArgs extends FileEventArgs
{
    /**
     * @var string
     */
    private $filesystemName;

    public function __construct(File $file)
    {
        parent::__construct($file);
    }

    /**
     * @return string
     */
    public function getFilesystemName()
    {
        return $this->filesystemName;
    }

    /**
     * @param string $filesystemName
     */
    public function setFilesystemName($filesystemName)
    {
        $this->filesystemName = $filesystemName;
    }
}