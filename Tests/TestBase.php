<?php
/**
 * Created by PhpStorm.
 * User: marco
 * Date: 19.12.14
 * Time: 15:32
 */

namespace Nemo64\DoctrineFlysystemBundle\Tests;


use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\SchemaTool;
use League\Flysystem\File;
use League\Flysystem\Filesystem;
use Nemo64\DoctrineFlysystemBundle\Tests\Entity\TestData;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use League\Flysystem\Adapter;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TestBase extends WebTestCase {

    /**
     * @var int
     */
    protected static $fileId = 0;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->container = self::$kernel->getContainer();
        $this->filesystem = new Filesystem(new Adapter\Local(sys_get_temp_dir()));
    }

    public function createTestEntityManager(array $classes)
    {
        if (!class_exists('PDO') || !in_array('sqlite', \PDO::getAvailableDrivers())) {
            self::markTestSkipped('This test requires SQLite support in your environment');
        }

        $config = new Configuration();
        $config->setEntityNamespaces(array('Test' => 'Nemo64\DoctrineFlysystemBundle\Tests\Entity'));
        $config->setAutoGenerateProxyClasses(true);
        $config->setProxyDir(\sys_get_temp_dir());
        $config->setProxyNamespace('SerializerBundleTests\Entity');
        $config->setMetadataDriverImpl(new AnnotationDriver(new AnnotationReader()));
        $config->setQueryCacheImpl(new ArrayCache());
        $config->setMetadataCacheImpl(new ArrayCache());

        $connection = array('driver' => 'pdo_sqlite', 'memory' => true);
        $em = EntityManager::create($connection, $config);

        $filesystemListener = $this->container->get('nemo64_doctrine_flysystem.filesystem_listener');
        $filesystemListener->addFilesystem('my_local_tmp', $this->filesystem);
        $em->getEventManager()->addEventListener('unserializeFile', $filesystemListener);
        $em->getEventManager()->addEventListener('serializeFile', $filesystemListener);

        $tool = new SchemaTool($em);
        $tool->createSchema(array_map(array($em, 'getClassMetadata'), $classes));

        return $em;
    }

    protected function createTestFile($content)
    {
        $path = 'file' . self::$fileId++;

        if ($this->filesystem->has($path)) {
            $this->filesystem->delete($path);
        }

        $this->filesystem->write($path, $content);

        $file = new File($this->filesystem, $path);

        return $file;
    }

    protected function createTestData($content)
    {
        $entity = new TestData($content);
        $file = $this->createTestFile($content);
        $entity->setData($content);
        $entity->setFile($file);
        return $entity;
    }

    /**
     * @param array $entities
     * @return EntityManager
     */
    protected function createTestEntityManagerWithData(array $entities)
    {
        $em = $this->createTestEntityManager(array('Test:TestData'));

        foreach ($entities as $entity) {
            $em->persist($entity);
        }

        $em->flush();
        $em->clear();
        return $em;
    }
}