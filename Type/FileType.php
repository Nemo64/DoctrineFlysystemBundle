<?php
/**
 * Created by PhpStorm.
 * User: marco
 * Date: 16.12.14
 * Time: 17:58
 */

namespace Nemo64\DoctrineFlysystemBundle\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use League\Flysystem\File;
use Nemo64\DoctrineFlysystemBundle\EventArgs\SerializeFileEventArgs;
use Nemo64\DoctrineFlysystemBundle\EventArgs\UnserializeFileEventArgs;
use Nemo64\DoctrineFlysystemBundle\Exception\FilesystemConversionException;

class FileType extends StringType
{
    const TYPE = 'flyfile';

    /**
     * @param mixed $value
     * @param AbstractPlatform $platform
     * @return string
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!$value instanceof File) {
            $type = is_object($value) ? get_class($value) : gettype($value);
            throw new FilesystemConversionException("Expected File, got $type");
        }

        $args = new SerializeFileEventArgs($value);
        $platform->getEventManager()->dispatchEvent('serializeFile', $args);

        $filesystemName = $args->getFilesystemName();
        if ($filesystemName === null) {
            throw new FilesystemConversionException("Couldn't find filesystem name for file " . $value->getPath());
        }

        return $value->getPath() . '?' . $filesystemName;
    }

    /**
     * @param mixed $value
     * @param AbstractPlatform $platform
     * @return File
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $parts = explode('?', $value);
        if (count($parts) !== 2) {
            throw new FilesystemConversionException("Couldn't convert '$value' to File Instance.");
        }

        list($path, $filesystemName) = $parts;
        $file = new File(null, $path);

        // the event should set the filesystem in the file
        $args = new UnserializeFileEventArgs($file, $filesystemName);
        $platform->getEventManager()->dispatchEvent('unserializeFile', $args);

        return $file;
    }

    /**
     * Gets the name of this type.
     *
     * @return string
     */
    public function getName()
    {
        self::TYPE;
    }
}