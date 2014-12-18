<?php
/**
 * Created by PhpStorm.
 * User: marco
 * Date: 16.12.14
 * Time: 18:51
 */

namespace Nemo64\DoctrineFlysystemBundle\EventListener;


use League\Flysystem\Filesystem;
use Nemo64\DoctrineFlysystemBundle\EventArgs\SerializeFileEventArgs;
use Nemo64\DoctrineFlysystemBundle\EventArgs\UnserializeFileEventArgs;
use Nemo64\DoctrineFlysystemBundle\Exception\FilesystemConversionException;

class FilesystemListener
{
    /**
     * @var Filesystem[]
     */
    private $allowedFilesystems = array();

    /**
     * @param string $name
     * @param Filesystem $filesystem
     */
    public function addFilesystem($name, Filesystem $filesystem)
    {
        $this->allowedFilesystems[$name] = $filesystem;
    }

    /**
     * @param UnserializeFileEventArgs $args
     */
    public function unserializeFile(UnserializeFileEventArgs $args)
    {
        $filesystemName = $args->getFilesystemName();
        $filesystem = $this->getFilesystem($filesystemName);
        $args->getFile()->setFilesystem($filesystem);
    }

    /**
     * @param SerializeFileEventArgs $args
     */
    public function serializeFile(SerializeFileEventArgs $args)
    {
        // TODO make it possible to access filesystem via getter
        $property = new \ReflectionProperty(get_class($args->getFile()), 'filesystem');
        $property->setAccessible(true);

        $filesystem = $property->getValue($args->getFile());
        $filesystemName = $this->getFilesystemName($filesystem);
        $args->setFilesystemName($filesystemName);
    }

    /**
     * @param string $filesystemName
     * @return Filesystem
     */
    protected function getFilesystem($filesystemName)
    {
        if (!array_key_exists($filesystemName, $this->allowedFilesystems)) {
            $msg = "Filesystem '$filesystemName' does not exist or isn't allowed to be used.\n";
            $msg .= "Check nemo64_doctrine_flysystem.allowed_filesystems for more information.";
            throw new FilesystemConversionException($msg);
        }

        return $this->allowedFilesystems[$filesystemName];
    }

    /**
     * @param Filesystem $filesystem
     * @return string
     */
    protected function getFilesystemName(Filesystem $filesystem)
    {
        $name = array_search($filesystem, $this->allowedFilesystems);

        if ($name === false) {
            $msg = "The filesystem is not in the list of allowed Filesystems.";
            throw new FilesystemConversionException($msg);
        }

        return $name;
    }
}