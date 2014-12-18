<?php
/**
 * Created by PhpStorm.
 * User: marco
 * Date: 17.12.14
 * Time: 19:06
 */

namespace Nemo64\DoctrineFlysystemBundle\Tests\EventListener;


use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\SchemaTool;
use League\Flysystem\File;
use League\Flysystem\Filesystem;
use Nemo64\DoctrineFlysystemBundle\Tests\Entity\TestData;
use League\Flysystem\Adapter;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class HandlerListenerTest extends WebTestCase
{
    /**
     * @var int
     */
    protected static $fileId = 0;

    /**
     * @var File[]
     */
    protected static $files = array();
    
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

    public function tearDown()
    {
        foreach (self::$files as $file) {
            $file->delete();
        }
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

    public function createTestFile($content)
    {
        $path = 'file' . self::$fileId++;
        $this->filesystem->write($path, $content);
        $file = new File($this->filesystem, $path);
        self::$files[] = $file;
        return $file;
    }

    public function testSaveData()
    {
        $em = $this->createTestEntityManager(array('Test:TestData'));

        $entity = new TestData('obj1');
        $file = $this->createTestFile('hi1');
        $entity->setData('foo1');
        $entity->setFile($file);

        $em->persist($entity);
        $em->flush();
        $em->clear(); // important so the entity gets newly generated

        $entities = $em->getRepository('Test:TestData')->findAll();
        $this->assertEquals(array($entity), $entities);
    }
}