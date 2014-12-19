<?php
/**
 * Created by PhpStorm.
 * User: marco
 * Date: 19.12.14
 * Time: 21:13
 */

namespace Nemo64\DoctrineFlysystemBundle\Tests\FileManager;


use Nemo64\DoctrineFlysystemBundle\FileManager\DoctrineFileManager;
use Nemo64\DoctrineFlysystemBundle\Tests\TestBase;

class DoctrineFileManagerTest extends TestBase
{
    public function testFileDetection()
    {
        $entityManager = $this->createTestEntityManager(array('Test:TestData'));
        $filesystem = $this->createFilesystem($entityManager);

        $entityManager->persist($data1 = $this->createTestData("Itineris tramitems ridetis, tanquam ferox onus.", $filesystem));
        $entityManager->persist($data2 = $this->createTestData("Sunt nixes experientia secundus, dexter amicitiaes.", $filesystem));
        $entityManager->persist($data3 = $this->createTestData("Camerarius toruss ducunt ad verpa.", $filesystem));

        $entityManager->flush();
        $entityManager->clear();

        $fileManager = new DoctrineFileManager();
        $fileManager->addEntityManager($entityManager);

        $fakeFile = $this->createTestFile('Neuter boreass ducunt ad bursa.', $filesystem);
        $lostFiles = $fileManager->filterRemovableFiles(array($fakeFile, $data1->getFile()));

        $this->assertEquals(array($fakeFile), $lostFiles);
    }
}