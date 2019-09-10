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

namespace OCA\RansomwareDetection\AppInfo;

use OC\Files\Filesystem;
use OCA\RansomwareDetection\Monitor;
use OCA\RansomwareDetection\Entropy\Entropy;
use OCA\RansomwareDetection\StorageWrapper;
use OCA\RansomwareDetection\Service\FileOperationService;
use OCA\RansomwareDetection\Service\ServiceWatcher;
use OCA\RansomwareDetection\Service\DetectionService;
use OCA\RansomwareDetection\Controller\ServiceController;
use OCA\RansomwareDetection\Controller\DetectionController;
use OCA\RansomwareDetection\Db\FileOperationMapper;
use OCA\RansomwareDetection\Model\DetectionDeserializer;
use OCP\AppFramework\App;
use OCP\Files\Storage\IStorage;
use OCP\Notification\IManager;
use OCP\Util;
use OCP\SabrePluginEvent;
use OCP\ILogger;
use OCP\IConfig;
use OCP\IUserSession;
use OCP\ISession;

class Application extends App
{
    const APP_ID = 'ransomware_detection';

    public function __construct()
    {
        parent::__construct(self::APP_ID);

        $container = $this->getContainer();

        // mapper
        $container->registerService('FileOperationMapper', function ($c) {
            return new FileOperationMapper(
                $c->query('ServerContainer')->getDatabaseConnection()
            );
        });

        $container->registerService('DetectionDeserializer', function ($c) {
            return new DetectionDeserializer(
                $c->query('FileOperationMapper')
            );
        });

        // services
        $container->registerService('FileOperationService', function ($c) {
            return new FileOperationService(
                $c->query('FileOperationMapper'),
                $c->query('OCP\IConfig'),
                $c->query('OCP\ILogger'),
                $c->query('ServerContainer')->getUserSession()->getUser()->getUID()
            );
        });

        $container->registerService('ServiceWatcher', function ($c) {
            return new ServiceWatcher(
                $c->query('OCP\IConfig')
            );
        });

        $container->registerService('DetectionService', function ($c) {
            return new DetectionService(
                $c->query('FileOperationService'),
                $c->query('DetectionDeserializer'),
                $c->query('OCP\IConfig'),
                $c->query('ServerContainer')->getUserSession()->getUser()->getUID()
            );
        });

        // controller
        $container->registerService('ServiceController', function ($c) {
            return new ServiceController(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('ServiceWatcher')
            );
        });

        $container->registerService('DetectionController', function ($c) {
            return new DetectionController(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('DetectionService')
            );
        });

        // entropy
        $container->registerService('Entropy', function ($c) {
            return new Entropy(
                $c->query(ILogger::class)
            );
        });
    }

    /**
     * Register hooks.
     */
    public function register()
    {
        Util::connectHook('OC_Filesystem', 'preSetup', $this, 'addStorageWrapper');
    }

    /**
     * @internal
     */
    public function addStorageWrapper()
    {
        Filesystem::addStorageWrapper(self::APP_ID, [$this, 'addStorageWrapperCallback'], -10);
    }

    /**
     * @internal
     *
     * @param string   $mountPoint
     * @param IStorage $storage
     *
     * @return StorageWrapper|IStorage
     */
    public function addStorageWrapperCallback($mountPoint, IStorage $storage)
    {
        if (!\OC::$CLI && !$storage->instanceOfStorage('OCA\Files_Sharing\SharedStorage')) {
            /** @var Monitor $monitor */
            $monitor = $this->getContainer()->query(Monitor::class);

            return new StorageWrapper([
                'storage' => $storage,
                'mountPoint' => $mountPoint,
                'monitor' => $monitor,
            ]);
        }

        return $storage;
    }
}
