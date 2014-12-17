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

class FilesystemListener
{
    /**
     * @var array
     */
    private $filesystems = array();

    /**
     * @param string $name
     * @param FilesystemInterface $filesystem
     * @param array $config
     */
    public function addFilesystem($name, FilesystemInterface $filesystem, array $config)
    {
        $this->filesystems[$name] = array(
            'filesystem' => $filesystem,
            'config' => $config
        );
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
        if (!array_key_exists($filesystemName, $this->filesystems)) {
            $msg = "Filesystem '$filesystemName' does not exist or isn't allowed to be used.\n";
            $msg .= "Check nemo64_database_flysystem.allowed_filesystems for more information.";
            throw new ConversionException($msg);
        }

        return $this->filesystems[$filesystemName]['filesystem'];
    }

    /**
     * @param FilesystemInterface $filesystem
     * @return string
     * @throws ConversionException
     */
    protected function getFilesystemName(FilesystemInterface $filesystem)
    {
        foreach ($this->filesystems as $name => $filesystemEntry) {
            if ($filesystemEntry['filesystem'] === $filesystem) {
                return $name;
            }
        }

        $msg = "The filesystem is not in the list of allowed Filesystems.";
        throw new ConversionException($msg);
    }
}