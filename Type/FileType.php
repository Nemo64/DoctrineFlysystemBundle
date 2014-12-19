<?php
/**
 * Created by PhpStorm.
 * User: marco
 * Date: 16.12.14
 * Time: 17:58
 */

namespace Nemo64\DoctrineFlysystemBundle\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use League\Flysystem\File;
use Nemo64\DoctrineFlysystemBundle\EventArgs\SerializeFileEventArgs;
use Nemo64\DoctrineFlysystemBundle\EventArgs\UnserializeFileEventArgs;

class FileType extends Type
{
    const TYPE = 'flyfile';

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
        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * @param AbstractPlatform $platform
     * @return int
     */
    public function getDefaultLength(AbstractPlatform $platform)
    {
        return $platform->getVarcharDefaultLength();
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