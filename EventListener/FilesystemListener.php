<?php
/**
 * Created by PhpStorm.
 * User: marco
 * Date: 16.12.14
 * Time: 18:51
 */

namespace Nemo64\DatabaseFlysystemBundle\EventListener;


use Doctrine\DBAL\Types\ConversionException;
use League\Flysystem\FilesystemInterface;
use Nemo64\DatabaseFlysystemBundle\EventArgs\SerializeFileEventArgs;
use Nemo64\DatabaseFlysystemBundle\EventArgs\UnserializeFileEventArgs;
use Nemo64\DatabaseFlysystemBundle\FileManager\FilesystemManagerInterface;

class FilesystemListener
{
    /**
     * @var FilesystemManagerInterface
     */
    protected $filesystemManager;

    public function __construct(FilesystemManagerInterface $filesystemManager)
    {
        $this->filesystemManager = $filesystemManager;
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
     * @return FilesystemInterface
     * @throws ConversionException
     */
    protected function getFilesystem($filesystemName)
    {
        $filesystem = $this->filesystemManager->getFilesystemByName($filesystemName);

        if ($filesystem === null) {
            $msg = "Filesystem '$filesystemName' does not exist or isn't allowed to be used.\n";
            $msg .= "Check nemo64_database_flysystem.allowed_filesystems for more information.";
            throw new ConversionException($msg);
        }

        return $filesystem;
    }

    /**
     * @param FilesystemInterface $filesystem
     * @return string
     * @throws ConversionException
     */
    protected function getFilesystemName(FilesystemInterface $filesystem)
    {
        $filesystemName = $this->filesystemManager->getNameOfFilesystem($filesystem);

        if ($filesystemName === null) {
            $msg = "The filesystem is not in the list of allowed Filesystems.";
            throw new ConversionException($msg);
        }

        return $filesystemName;
    }
}