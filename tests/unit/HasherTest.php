<?php

namespace Grav\Plugin\Newsletter\Test\Unit;

use Grav\Common\Config\Config;
use Grav\Plugin\Newsletter\Container;
use Grav\Plugin\Newsletter\Hasher;
use PHPUnit\Framework\TestCase;

class HasherTest extends TestCase
{
    /** @var Hasher */
    private $hasher;

    /** @var Container */
    private $container;

    public function setUp()
    {
        $this->container = new Container();
        $this->container['config'] = function() {
            return $this->getMockBuilder(Config::class)->setMethods(['value'])->disableOriginalConstructor()
                ->getMock();
        };
        $this->hasher = $this->container->getHasher();
    }

    public function testHashWillReturn32CharString()
    {
        $result = $this->hasher->hash('some string');
        $this->assertInternalType('string', $result);
        $this->assertSame(32, strlen($result));
    }
}