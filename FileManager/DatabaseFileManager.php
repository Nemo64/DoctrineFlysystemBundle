<?php
/**
 * Created by PhpStorm.
 * User: marco
 * Date: 19.12.14
 * Time: 23:34
 */

namespace Nemo64\DatabaseFlysystemBundle\FileManager;


use League\Flysystem\File;

class DatabaseFileManager implements DatabaseFileManagerInterface
{
    /**
     * @var DatabaseFileManagerInterface[]
     */
    private $fileManagers = array();

    /**
     * @param DatabaseFileManagerInterface $fileManager
     */
    public function addFileManager(DatabaseFileManagerInterface $fileManager)
    {
        $this->fileManagers[] = $fileManager;
    }

    /**
     * Tests if the specified files exist in the database.
     * Files that are absent are included in the returned array.
     *
     * @param File[] $files
     * @return File[]
     */
    public function filterRemovableFiles(array $files)
    {
        foreach ($this->fileManagers as $fileManager) {
            $files = $fileManager->filterRemovableFiles($files);
        }

        return $files;
    }
}