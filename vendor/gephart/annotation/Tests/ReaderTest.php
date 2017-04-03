<?php

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * @Route /home/
 */
class SuperClass
{
    /**
     * @ORM\Type INT(6)
     */
    private $property;

    /**
     * @Template {
     *     "url": "index.html"
     * }
     * @Route /index
     * @ORM\Table table
     */
    public function index()
    {
    }
}

class ReaderTest extends \PHPUnit\Framework\TestCase
{
    public function testClass()
    {
        $reader = new \Gephart\Annotation\Reader();
        $annotation = $reader->get("Route", SuperClass::class);
        $this->assertEquals("/home/", $annotation);
    }

    public function testMethod()
    {
        $reader = new \Gephart\Annotation\Reader();
        $annotation = $reader->get("Template", SuperClass::class, "index");
        $this->assertEquals(["url" => "index.html"], $annotation);

        $annotation = $reader->get("Route", SuperClass::class, "index");
        $this->assertEquals("/index", $annotation);

        $annotation = $reader->get("ORM\\Table", SuperClass::class, "index");
        $this->assertEquals("table", $annotation);

        $annotation = $reader->getAllProperty(SuperClass::class, "property");
        $this->assertEquals("INT(6)", $annotation["ORM\\Type"]);
    }

    public function testGetAll()
    {
        $reader = new \Gephart\Annotation\Reader();
        $annotations = $reader->getAll(SuperClass::class, "index");

        $this->assertEquals(["url" => "index.html"], $annotations["Template"]);
        $this->assertEquals("/index", $annotations["Route"]);
    }
}
