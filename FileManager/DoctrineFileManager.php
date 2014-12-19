<?php
/**
 * Created by PhpStorm.
 * User: marco
 * Date: 19.12.14
 * Time: 17:48
 */

namespace Nemo64\DoctrineFlysystemBundle\FileManager;


use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use League\Flysystem\File;
use Nemo64\DoctrineFlysystemBundle\Type\FileType;

class DoctrineFileManager implements DatabaseFileManagerInterface
{
    /**
     * @var EntityManager[]
     */
    protected $entityManagers;

    /**
     * @var array|null
     * @see \Nemo64\DoctrineFlysystemBundle\FileManager\DoctrineFileManager::getEntityManagerTableFieldList
     */
    private $entityManagerTableFieldList;

    /**
     * @param EntityManager $entityManager
     */
    public function addEntityManager(EntityManager $entityManager)
    {
        $this->entityManagers[] = $entityManager;
    }

    /**
     * Returns an array to specify which fields contain flyfile values.
     *
     * array('entity manager index' => array('entity name' => array('field name')))
     *
     * @return array
     */
    protected function getEntityManagerTableFieldList()
    {
        if ($this->entityManagerTableFieldList !== null) {
            return $this->entityManagerTableFieldList;
        }

        $entityManagerTableFieldList = array();

        foreach ($this->entityManagers as $entityManagerIndex => $entityManager) {
            $entityManagerTableFieldList[$entityManagerIndex] = array();

            /** @var ClassMetadata[] $classMappings */
            $classMappings = $entityManager->getMetadataFactory()->getAllMetadata();

            foreach ($classMappings as $classMapping) {
                $className = $classMapping->name;
                $entityManagerTableFieldList[$entityManagerIndex][$className] = array();

                foreach ($classMapping->fieldMappings as $fieldMapping) {

                    if ($fieldMapping['type'] !== FileType::TYPE) {
                        continue;
                    }

                    $entityManagerTableFieldList[$entityManagerIndex][$className][] = $fieldMapping['fieldName'];
                }
            }
        }

        return $this->entityManagerTableFieldList = $entityManagerTableFieldList;
    }

    /**
     * @param File $file
     * @param EntityManager $entityManager
     * @return string
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getFileDatabaseValue(File $file, EntityManager $entityManager)
    {
        $fileType = Type::getType(FileType::TYPE);
        $platform = $entityManager->getConnection()->getDatabasePlatform();

        return $fileType->convertToDatabaseValue($file, $platform);
    }

    /**
     * Checks the database for entries of that match the fileIdentifiers.
     * Found entries will be added to the result without filtering.
     * The result could contain duplicates or results which were not expected.
     *
     * @param string[] $fileIdentifiers
     * @param EntityManager $entityManager
     * @return File[]
     */
    protected function findFilesByIdentifiers(array $fileIdentifiers, EntityManager $entityManager)
    {
        $foundFiles = array();
        $entityManagerTableFieldList = $this->getEntityManagerTableFieldList();
        $tableFieldList = $entityManagerTableFieldList[array_search($entityManager, $this->entityManagers)];

        foreach ($tableFieldList as $table => $fieldList) {

            // if all identifiers are already found then stop
            if (empty($fileIdentifiers)) {
                return $foundFiles;
            }

            // TODO disable filters before executing queries
            $qb = $entityManager->createQueryBuilder();
            $qb->from($table, 'table');

            // select all file fields but reduce result by files that match
            foreach ($fieldList as $field) {
                $fieldName = 'table.' . $field;

                $qb->addSelect($fieldName);
                $qb->andWhere($fieldName . ' IN (:file_list)');
            }

            $qb->setParameter('file_list', $fileIdentifiers);

            $rows = $qb->getQuery()->getArrayResult();
            foreach ($rows as $row) {
                foreach ($row as $file) {
                    $foundFiles[] = $file;

                    // remove the already found files from the next queries
                    $identifier = $this->getFileDatabaseValue($file, $entityManager);
                    $index = array_search($identifier, $fileIdentifiers);
                    if ($index !== false) {
                        unset($fileIdentifiers[$index]);
                    }
                }
            }
        }
        return $foundFiles;
    }

    /**
     * @param File[] $files
     * @param EntityManager $entityManager
     * @return array
     */
    protected function createIndexedFileList(array $files, EntityManager $entityManager)
    {
        $indexedFiles = array();
        foreach ($files as $index => $file) {
            $identifier = $this->getFileDatabaseValue($file, $entityManager);
            $indexedFiles[$identifier] = array('file' => $file, 'index' => $index);
        }

        return $indexedFiles;
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
        foreach ($this->entityManagers as $entityManager) {

            if (empty($files)) {
                return array();
            }

            $indexedFiles = $this->createIndexedFileList($files, $entityManager);
            $foundFiles = $this->findFilesByIdentifiers(array_keys($indexedFiles), $entityManager);

            foreach ($foundFiles as $foundFile) {
                $identifier = $this->getFileDatabaseValue($foundFile, $entityManager);

                if (array_key_exists($identifier, $indexedFiles)) {
                    $originalIndex = $indexedFiles[$identifier]['index'];
                    unset($files[$originalIndex]);
                }
            }
        }

        return $files;
    }
}