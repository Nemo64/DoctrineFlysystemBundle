<?php
/**
 * Created by PhpStorm.
 * User: marco
 * Date: 19.12.14
 * Time: 17:47
 */

namespace Nemo64\DatabaseFlysystemBundle\FileManager;


use League\Flysystem\File;

interface DatabaseFileManagerInterface
{
    /**
     * Tests if the specified files exist in the database.
     * Files that are absent are included in the returned array.
     *
     * @param File[] $files
     * @return File[]
     */
    public function filterRemovableFiles(array $files);
}