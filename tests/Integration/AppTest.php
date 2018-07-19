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

namespace OCA\RansomwareDetection\tests\Integration;

use PHPUnit_Framework_TestCase;
use OCA\RansomwareDetection\AppInfo\Application;
use OCA\RansomwareDetection\Db\FileOperationMapper;
use OCA\RansomwareDetection\Tests\Integration\Fixtures\FileOperationFixture;
use OCP\AppFramework\IAppContainer;
use OCP\IDBConnection;

/**
 * This test shows how to make a small Integration Test. Query your class
 * directly from the container, only pass in mocks if needed and run your tests
 * against the database.
 */
abstract class AppTest extends PHPUnit_Framework_TestCase
{
    /** @var FileOperationMapper */
    protected $fileOperationMapper;

    /** @var IAppContainer */
    protected $container;

    protected $userId = 'john';

    protected function setUp()
    {
        parent::setUp();

        $app = new Application();
        $this->container = $app->getContainer();

        // only replace the user id
        $this->container->registerService('userId', function () {
            return $this->userId;
        });

        // set up database layers
        $this->fileOperationMapper = $this->container->query(FileOperationMapper::class);
    }

    public function testAppInstalled()
    {
        $appManager = $this->container->query('OCP\App\IAppManager');
        $this->assertTrue($appManager->isInstalled('ransomware_detection'));
    }

    protected function loadFixtures(array $fixtures = [])
    {
        foreach ($fixtures as $fixture) {
            $fileOperation = new FileOperationFixture($fixture);
            $this->fileOperationMapper->insert($fileOperation);
        }
    }

    protected function clearDatabase($user)
    {
        $sql = [
            'DELETE FROM `*PREFIX*ransomware_detection` WHERE `user_id` = ?',
        ];
        $db = $this->container->query(IDBConnection::class);
        foreach ($sql as $query) {
            $db->prepare($query)->execute([$this->userId]);
        }
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->clearDatabase($this->userId);
    }
}
