<?php

/**
 * @copyright Copyright (c) 2017 Matthias Held <matthias.held@uni-konstanz.de>
 * @author Matthias Held <matthias.held@uni-konstanz.de>
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace OCA\RansomwareDetection\tests\Unit;

use OCA\RansomwareDetection\CacheWrapper;
use Test\TestCase;

class CacheWrapperTest extends TestCase
{
    /** @var \OCP\Files\Cache\ICache|\PHPUnit_Framework_MockObject_MockObject */
    protected $cache;

    /** @var \OCP\Files\Storage\IStorage|\PHPUnit_Framework_MockObject_MockObject */
    protected $storage;

    /** @var \OCA\RansomwareDetection\Monitor\Operation|\PHPUnit_Framework_MockObject_MockObject */
    protected $monitor;

    public function setUp(): void
    {
        parent::setUp();

        $this->cache = $this->getMockBuilder('OCP\Files\Cache\ICache')
            ->getMock();
        $this->storage = $this->getMockBuilder('OCP\Files\Storage\IStorage')
            ->getMock();
        $this->monitor = $this->getMockBuilder('OCA\RansomwareDetection\Monitor')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function dataFormatCacheEntry()
    {
        return [
            ['/admin'],
            ['/files'],
        ];
    }

    /**
     * @dataProvider dataFormatCacheEntry
     *
     * @param string $path
     */
    public function testFormatCacheEntry($path)
    {
        $formatCacheEntry = self::getMethod('formatCacheEntry');
        $cacheWrapper = new CacheWrapper($this->cache, $this->storage, $this->monitor);

        $result = $formatCacheEntry->invokeArgs($cacheWrapper, [['path' => $path]]);

        $this->assertEquals($result['path'], $path);
    }

    /**
     * Get protected method.
     *
     * @param string $name
     *
     * @return $method
     */
    protected static function getMethod($name)
    {
        $class = new \ReflectionClass(CacheWrapper::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }
}
