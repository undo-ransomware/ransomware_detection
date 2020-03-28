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

use OCA\RansomwareDetection\Monitor;
use Test\TestCase;

class StorageWrapperTest extends TestCase
{
    /** @var \OCP\Files\Storage\IStorage|\PHPUnit_Framework_MockObject_MockObject */
    protected $storage;

    /** @var \OCA\RansomwareDetection\Monitor|\PHPUnit_Framework_MockObject_MockObject */
    protected $monitor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storage = $this->getMockBuilder('OCP\Files\Storage\IStorage')
            ->setConstructorArgs([array()])
            ->getMock();

        $this->monitor = $this->getMockBuilder('OCA\RansomwareDetection\Monitor')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getInstance(array $methods = [])
    {
        return $this->getMockBuilder('OCA\RansomwareDetection\StorageWrapper')
            ->setConstructorArgs([
                [
                    'storage' => $this->storage,
                    'mountPoint' => 'mountPoint',
                    'monitor' => $this->monitor,
                ],
            ])
            ->setMethods($methods)
            ->getMock();
    }

    public function dataAnalyze()
    {
        return [
            ['path1', Monitor::READ],
            ['path2', Monitor::WRITE],
            ['path3', Monitor::RENAME],
            ['path4', Monitor::DELETE],
        ];
    }

    /**
     * @dataProvider dataAnalyze
     *
     * @param string $path
     * @param int    $mode
     */
    public function testAnalyze($path, $mode)
    {
        $storage = $this->getInstance();

        $this->monitor->expects($this->once())
            ->method('analyze')
            ->with($storage, $path, $mode);

        $this->monitor->analyze($storage, $path, $mode);
    }

    public function dataSinglePath()
    {
        $tests = [];
        $tests[] = ['file_get_contents', 'path1', Monitor::READ, true];
        $tests[] = ['file_get_contents', 'path2', Monitor::READ, false];
        $tests[] = ['unlink', 'path1', Monitor::DELETE, true];
        $tests[] = ['unlink', 'path2', Monitor::DELETE, false];
		$tests[] = ['mkdir', 'path1', Monitor::CREATE, true];
        $tests[] = ['mkdir', 'path2', Monitor::CREATE, false];
		$tests[] = ['rmdir', 'path1', Monitor::DELETE, true];
        $tests[] = ['rmdir', 'path2', Monitor::DELETE, false];

        return $tests;
    }

    /**
     * @dataProvider dataSinglePath
     *
     * @param string $method
     * @param string $path
     * @param int    $mode
     * @param bool   $return
     */
    public function testSinglePath($method, $path, $mode, $return)
    {
        $storage = $this->getInstance(['analyze']);

        $storage->expects($this->once())
            ->method('analyze')
            ->with($storage, [$path], $mode);

        $this->storage->expects($this->once())
            ->method($method)
            ->with($path)
            ->willReturn($return);

        $this->assertSame($return, $this->invokePrivate($storage, $method, [$path, $mode]));
    }

    public function dataDoublePath()
    {
        $tests = [];
        $tests[] = ['rename', 'path1', 'path1', Monitor::RENAME, true];
        $tests[] = ['rename', 'path2', 'path2', Monitor::RENAME, false];
        $tests[] = ['copy', 'path1', 'path1', Monitor::WRITE, true];
        $tests[] = ['copy', 'path2', 'path2', Monitor::WRITE, false];

        return $tests;
    }

    /**
     * @dataProvider dataDoublePath
     *
     * @param string $method
     * @param string $path1
     * @param string $path2
     * @param int    $mode
     * @param bool   $return
     */
    public function testDoublePath($method, $path1, $path2, $mode, $return)
    {
        $storage = $this->getInstance(['analyze']);

        $storage->expects($this->once())
            ->method('analyze')
            ->with($storage, [$path2, $path1], $mode);

        $this->storage->expects($this->once())
            ->method($method)
            ->with($path1, $path2)
            ->willReturn($return);

        $this->assertSame($return, $this->invokePrivate($storage, $method, [$path1, $path2, $mode]));
    }

    public function dataTwoParameters()
    {
        $tests = [];
        $tests[] = ['file_put_contents', 'path1', 'data', Monitor::WRITE, true];
        $tests[] = ['file_put_contents', 'path1', 'data', Monitor::WRITE, false];
        $tests[] = ['fopen', 'path1', 'z', Monitor::READ, true];
        $tests[] = ['fopen', 'path1', 'z', Monitor::READ, false];
        $tests[] = ['fopen', 'path1', 'x', Monitor::WRITE, true];
        $tests[] = ['fopen', 'path1', 'x', Monitor::WRITE, false];
        $tests[] = ['touch', 'path1', null, Monitor::WRITE, true];
        $tests[] = ['touch', 'path1', null, Monitor::WRITE, false];

        return $tests;
    }

    /**
     * @dataProvider dataTwoParameters
     *
     * @param string $method
     * @param string $path
     * @param string $param2
     * @param int    $mode
     * @param bool   $return
     */
    public function testTwoParameters($method, $path, $param2, $mode, $return)
    {
        $storage = $this->getInstance(['analyze']);

        $storage->expects($this->once())
            ->method('analyze')
            ->with($storage, [$path], $mode);

        $this->storage->expects($this->once())
            ->method($method)
            ->with($path, $param2)
            ->willReturn($return);

        $this->assertSame($return, $this->invokePrivate($storage, $method, [$path, $param2, $mode]));
    }
}
