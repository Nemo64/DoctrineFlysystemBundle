<?php
/**
 * Created by PhpStorm.
 * User: marco
 * Date: 17.12.14
 * Time: 19:10
 */

namespace Nemo64\DoctrineFlysystemBundle\Tests\Entity;


use Doctrine\ORM\Mapping as ORM;
use League\Flysystem\File;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table()
 * @ORM\Entity()
 **/
class TestData
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255)
     */
    private $data;

    /**
     * @var File
     *
     * @ORM\Column(type="flyfile")
     */
    private $file;

    /**
     * @param string|null $data
     */
    function __construct($data = null)
    {
        $this->setData($data);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string|null $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param File $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }
}