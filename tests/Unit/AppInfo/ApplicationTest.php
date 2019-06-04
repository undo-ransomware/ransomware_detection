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

namespace OCA\RansomwareDetection\tests\Unit\AppInfo;

use OCA\RansomwareDetection\AppInfo\Application;
use OCP\Files\Storage\IStorage;
use Test\TestCase;

class ApplicationTest extends TestCase
{
    /** @var Application */
    private $application;

    /** @var \OCP\AppFramework\IAppContainer */
    protected $container;

    public function setUp()
    {
        parent::setUp();

        $this->application = new Application();
        $this->container = $this->application->getContainer();
    }

    public function testContainerAppName()
    {
        $this->assertEquals('ransomware_detection', $this->container->getAppName());
    }

    public function dataContainerQuery()
    {
        return [
        ];
    }

    /**
     * @dataProvider dataContainerQuery
     *
     * @param string $service
     * @param string $expected
     */
    public function testContainerQuery($service, $expected)
    {
        $this->assertTrue($this->container->query($service) instanceof $expected);
    }

    public function testAddStorageWrapperCallback()
    {
        $storage = $this->getMockBuilder('OCP\Files\Storage\IStorage')->getMock();

        $result = $this->application->addStorageWrapperCallback('mountPoint', $storage);
        // Request from CLI, so $results is instanceof IStorage and not StorageWrapper
        $this->assertTrue($result instanceof IStorage);
    }
}
