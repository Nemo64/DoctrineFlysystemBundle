<?php
/**
 * Created by PhpStorm.
 * User: marco
 * Date: 20.12.14
 * Time: 00:11
 */

namespace Nemo64\DatabaseFlysystemBundle\FileManager;


use League\Flysystem\FilesystemInterface;

interface FilesystemManagerInterface
{
    /**
     * @return FilesystemInterface[]
     */
    public function getAllFilesystems();

    /**
     * @return FilesystemInterface[]
     */
    public function getAllOrphanRemovalFilesystems();

    /**
     * @param string $name
     * @return FilesystemInterface
     */
    public function getFilesystemByName($name);

    /**
     * @param FilesystemInterface $filesystem
     * @return string
     */
    public function getNameOfFilesystem(FilesystemInterface $filesystem);
}