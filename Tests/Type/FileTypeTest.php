<?php
/**
 * Created by PhpStorm.
 * User: marco
 * Date: 17.12.14
 * Time: 19:06
 */

namespace Nemo64\DatabaseFlysystemBundle\Tests\Type;


use League\Flysystem\Adapter;
use Nemo64\DatabaseFlysystemBundle\Tests\TestBase;
use Nemo64\DatabaseFlysystemBundle\Type\FileType;

class FileTypeTest extends TestBase
{
    public function testSaveAndReceiveData()
    {
        $em = $this->createTestEntityManager(array('Test:TestData'));
        $filesystem = $this->createFilesystem($em);
        $entity = $this->createTestData('Per guest prepare two and a half teaspoons of honey with warmed zucchini for dessert.', $filesystem);

        $em->persist($entity);
        $em->flush();
        $em->clear();

        $entities = $em->getRepository('Test:TestData')->findAll();
        $this->assertEquals(array($entity), $entities);
    }

    public function testArrayResult()
    {
        $em = $this->createTestEntityManager(array('Test:TestData'));
        $filesystem = $this->createFilesystem($em);
        $entity = $this->createTestData('Observare inciviliter ducunt ad noster demolitione.', $filesystem);

        $em->persist($entity);
        $em->flush();
        $em->clear();

        $selectQuery = $em->createQuery('SELECT t.file, t.data FROM Test:TestData ™t');
        $result = $selectQuery->getArrayResult();

        $this->assertEquals(array(array('file' => $entity->getFile(), 'data' => $entity->getData())), $result);
    }

    public function testSelectByFile()
    {
        $em = $this->createTestEntityManager(array('Test:TestData'));
        $filesystem = $this->createFilesystem($em);
        $entity = $this->createTestData('Ellipse at the port that is when solid captains experiment.', $filesystem);

        $em->persist($entity);
        $em->flush();
        $em->clear();

        $selectQuery = $em->createQuery('SELECT t.file FROm Test:TestData t WHERE t.file = :file');
        $selectQuery->setParameter('file', $entity->getFile(), FileType::TYPE);
        $result = $selectQuery->getArrayResult();

        $this->assertEquals(array(array('file' => $entity->getFile())), $result);
    }
}