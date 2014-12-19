<?php
/**
 * Created by PhpStorm.
 * User: marco
 * Date: 20.12.14
 * Time: 00:13
 */

namespace Nemo64\DatabaseFlysystemBundle\FileManager;


use League\Flysystem\FilesystemInterface;

class FilesystemManager implements FilesystemManagerInterface
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
     * @return FilesystemInterface[]
     */
    public function getAllFilesystems()
    {
        $result = array();

        foreach ($this->filesystems as $filesystemEntry) {
            $result[] = $filesystemEntry['filesystem'];
        }

        return $result;
    }

    /**
     * @return FilesystemInterface[]
     */
    public function getAllOrphanRemovalFilesystems()
    {
        $result = array();

        foreach ($this->filesystems as $filesystemEntry) {

            if (!$filesystemEntry['config']['orphan_removal']) {
                continue;
            }

            $result[] = $filesystemEntry['filesystem'];
        }

        return $result;
    }

    /**
     * @param string $name
     * @return FilesystemInterface|null
     */
    public function getFilesystemByName($name)
    {
        if (array_key_exists($name, $this->filesystems)) {
            return $this->filesystems[$name]['filesystem'];
        }

        return null;
    }

    /**
     * @param FilesystemInterface $filesystem
     * @return string|null
     */
    public function getNameOfFilesystem(FilesystemInterface $filesystem)
    {
        foreach ($this->filesystems as $name => $filesystemEntry) {
            if ($filesystemEntry['filesystem'] !== $filesystem) {
                continue;
            }

            return $name;
        }

        return null;
    }
}