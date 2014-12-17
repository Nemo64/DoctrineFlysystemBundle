<?php
/**
 * Created by PhpStorm.
 * User: marco
 * Date: 18.12.14
 * Time: 20:32
 */

namespace Nemo64\DatabaseFlysystemBundle\EventArgs;


use League\Flysystem\File;

class UnserializeFileEventArgs extends FileEventArgs
{
    /**
     * @var string
     */
    private $filesystemName;

    public function __construct(File $file, $filesystemName)
    {
        parent::__construct($file);
        $this->filesystemName = $filesystemName;
    }

    /**
     * @return string
     */
    public function getFilesystemName()
    {
        return $this->filesystemName;
    }
}