<?php
/**
 * Created by PhpStorm.
 * User: marco
 * Date: 16.12.14
 * Time: 17:58
 */

namespace Nemo64\DatabaseFlysystemBundle\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use League\Flysystem\File;
use Nemo64\DatabaseFlysystemBundle\EventArgs\SerializeFileEventArgs;
use Nemo64\DatabaseFlysystemBundle\EventArgs\UnserializeFileEventArgs;

class FileType extends Type
{
    const TYPE = 'flyfile';

    // /usr/src/linux-headers-2.6.38-10/include/linux/limits.h
    //   #define NAME_MAX         255    /* # chars in a file name */
    //   #define PATH_MAX        4096    /* # chars in a path name including nul */
    // it is very unlikely that there will be longer paths needed.
    const MAX_NAME_LENGTH = 255;
    const MAX_PATH_LENGTH = 4095; // -1 because there is no nul in sql/php
    const MAX_FS_LENGTH = 255;
    const MAX_COMBINED_LENGTH = 4351; // path length + fs length + 1 combination character

    /**
     * Gets the SQL declaration snippet for a field of this type.
     *
     * @param array $fieldDeclaration The field declaration.
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform The currently used database platform.
     *
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        if ($fieldDeclaration['length'] === null) {
            $fieldDeclaration['length'] = $this->getDefaultLength($platform);
        }
        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * @param AbstractPlatform $platform
     * @return int
     */
    public function getDefaultLength(AbstractPlatform $platform)
    {
        return min(self::MAX_COMBINED_LENGTH, $platform->getVarcharMaxLength());
    }

    /**
     * @param mixed $value
     * @param AbstractPlatform $platform
     * @return string
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof File) {
            $type = is_object($value) ? get_class($value) : gettype($value);
            throw new ConversionException("Expected File, got $type");
        }

        $args = new SerializeFileEventArgs($value);
        $platform->getEventManager()->dispatchEvent('serializeFile', $args);

        $filesystemName = $args->getFilesystemName();
        if ($filesystemName === null) {
            throw new ConversionException("Couldn't find filesystem name for file " . $value->getPath());
        }

        return $value->getPath() . '?' . $filesystemName;
    }

    /**
     * @param mixed $value
     * @param AbstractPlatform $platform
     * @return File
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            ConversionException::conversionFailed($value, $this->getName());
        }

        $parts = explode('?', $value);
        if (count($parts) !== 2) {
            ConversionException::conversionFailedFormat($value, $this->getName(), 'filename?filesystem');
        }

        list($path, $filesystemName) = $parts;
        $file = new File(null, $path);

        // the event should set the filesystem in the file
        $args = new UnserializeFileEventArgs($file, $filesystemName);
        $platform->getEventManager()->dispatchEvent('unserializeFile', $args);

        return $file;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
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