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
use League\Flysystem\Adapter;
use League\Flysystem\File;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Nemo64\DoctrineFlysystemBundle\Tests\Entity\TestData;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
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
     * @var FilesystemInterface[]
     */
    protected $filesystems;

    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->container = self::$kernel->getContainer();
    }

    public function tearDown()
    {
//        foreach ($this->filesystems as $filesystem) {
//            $filesystem->deleteDir('.');
//        }
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
        $config->setMetadataDriverImpl(new AnnotationDriver(new AnnotationReader(), __DIR__ . '/Entity'));
        $config->setQueryCacheImpl(new ArrayCache());
        $config->setMetadataCacheImpl(new ArrayCache());

        $connection = array('driver' => 'pdo_sqlite', 'memory' => true);
        $em = EntityManager::create($connection, $config);

        $tool = new SchemaTool($em);
        $tool->createSchema(array_map(array($em, 'getClassMetadata'), $classes));

        return $em;
    }

    /**
     * @param EntityManager $em
     * @return FilesystemInterface
     */
    protected function createFilesystem(EntityManager $em)
    {
        $filesystem = new Filesystem(new Adapter\Local(sys_get_temp_dir() . '/flysystem' . count($this->filesystems)));
        $this->filesystems[] = $filesystem;

        $filesystemListener = clone $this->container->get('nemo64_doctrine_flysystem.filesystem_listener');
        $filesystemListener->addFilesystem('my_local_tmp', $filesystem, array(
            'orphan_removal' => true
        ));

        $em->getEventManager()->addEventListener('unserializeFile', $filesystemListener);
        $em->getEventManager()->addEventListener('serializeFile', $filesystemListener);

        return $filesystem;
    }

    protected function createTestFile($content, FilesystemInterface $filesystem)
    {
        $path = 'file' . self::$fileId++;

        if ($filesystem->has($path)) {
            $filesystem->delete($path);
        }

        $filesystem->write($path, $content);

        $file = new File($filesystem, $path);

        return $file;
    }

    protected function createTestData($content, FilesystemInterface $filesystem)
    {
        $entity = new TestData($content);
        $file = $this->createTestFile($content, $filesystem);
        $entity->setData($content);
        $entity->setFile($file);
        return $entity;
    }
}