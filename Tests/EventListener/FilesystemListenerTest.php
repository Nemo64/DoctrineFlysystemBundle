<?php
/**
 * Created by PhpStorm.
 * User: marco
 * Date: 17.12.14
 * Time: 19:06
 */

namespace Nemo64\DoctrineFlysystemBundle\Tests\EventListener;


use League\Flysystem\Adapter;
use Nemo64\DoctrineFlysystemBundle\Tests\TestBase;
use Nemo64\DoctrineFlysystemBundle\Type\FileType;

class FilesystemListenerTest extends TestBase
{
    public function testSaveAndReceiveData()
    {
        $entity = $this->createTestData('Per guest prepare two and a half teaspoons of honey with warmed zucchini for dessert.');
        $em = $this->createTestEntityManagerWithData(array($entity));

        $entities = $em->getRepository('Test:TestData')->findAll();
        $this->assertEquals(array($entity), $entities);
    }

    public function testArrayResult()
    {
        $entity = $this->createTestData('Observare inciviliter ducunt ad noster demolitione.');
        $em = $this->createTestEntityManagerWithData(array($entity));

        $selectQuery = $em->createQuery('SELECT t.file, t.data FROM Test:TestData t');
        $result = $selectQuery->getArrayResult();

        $this->assertEquals(array(array('file' => $entity->getFile(), 'data' => $entity->getData())), $result);
    }

    public function testSelectByFile()
    {
        $entity = $this->createTestData('Ellipse at the port that is when solid captains experiment.');
        $em = $this->createTestEntityManagerWithData(array($entity));

        $selectQuery = $em->createQuery('SELECT t.file FROm Test:TestData t WHERE t.file = :file');
        $selectQuery->setParameter('file', $entity->getFile(), FileType::TYPE);
        $result = $selectQuery->getArrayResult();

        $this->assertEquals(array(array('file' => $entity->getFile())), $result);
    }
}