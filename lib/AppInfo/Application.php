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
use OCA\RansomwareDetection\Classifier;
use OCA\RansomwareDetection\Analyzer\SequenceAnalyzer;
use OCA\RansomwareDetection\Analyzer\SequenceSizeAnalyzer;
use OCA\RansomwareDetection\Analyzer\FileTypeFunnellingAnalyzer;
use OCA\RansomwareDetection\Analyzer\EntropyFunnellingAnalyzer;
use OCA\RansomwareDetection\Analyzer\FileExtensionAnalyzer;
use OCA\RansomwareDetection\Entropy\Entropy;
use OCA\RansomwareDetection\Notification\Notifier;
use OCA\RansomwareDetection\StorageWrapper;
use OCA\RansomwareDetection\Connector\Sabre\RequestPlugin;
use OCA\RansomwareDetection\Service\FileOperationService;
use OCA\RansomwareDetection\Mapper\FileOperationMapper;
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
                $c->query('ServerContainer')->getDb()
            );
        });

        // services
        $container->registerService('FileOperationService', function ($c) {
            return new FileOperationService(
                $c->query('FileOperationMapper'),
                $c->query('ServerContainer')->getUserSession()->getUser()->getUID()
            );
        });

        // classifier
        $container->registerService('Classifier', function ($c) {
            return new Classifier(
                $c->query(ILogger::class),
                $c->query(FileOperationMapper::class),
                $c->query(FileOperationService::class)
            );
        });

        // entropy
        $container->registerService('Entropy', function ($c) {
            return new Entropy(
                $c->query(ILogger::class)
            );
        });

        // analyzer
        $container->registerService('SequenceSizeAnalyzer', function ($c) {
            return new SequenceSizeAnalyzer();
        });

        $container->registerService('FileTypeFunnellingAnalyzer', function ($c) {
            return new FileTypeFunnellingAnalyzer();
        });

        $container->registerService('EntropyFunnellingAnalyzer', function ($c) {
            return new EntropyFunnellingAnalyzer(
                $c->query(ILogger::class)
            );
        });

        $container->registerService('FileExtensionAnalyzer', function ($c) {
            return new FileExtensionAnalyzer(
                $c->query(ILogger::class),
                $c->query(Entropy::class)

            );
        });

        $container->registerService('SequenceAnalyzer', function ($c) {
            return new SequenceAnalyzer(
                $c->query(SequenceSizeAnalyzer::class),
                $c->query(FileTypeFunnellingAnalyzer::class),
                $c->query(EntropyFunnellingAnalyzer::class)
            );
        });
    }

    /**
     * Register hooks.
     */
    public function register()
    {
        // register sabre plugin to catch the profind requests
        $eventDispatcher = $this->getContainer()->getServer()->getEventDispatcher();
        $eventDispatcher->addListener('OCA\DAV\Connector\Sabre::addPlugin', function (SabrePluginEvent $event) {
            $logger = $this->getContainer()->query(ILogger::class);
            $config = $this->getContainer()->query(IConfig::class);
            $userSession = $this->getContainer()->query(IUserSession::class);
            $session = $this->getContainer()->query(ISession::class);
            $service = $this->getContainer()->query(FileOperationService::class);
            $notifications = $this->getContainer()->query(IManager::class);
            $classifier = $this->getContainer()->query(Classifier::class);
            $sequenceAnalyzer = $this->getContainer()->query(SequenceAnalyzer::class);
            $event->getServer()->addPlugin(new RequestPlugin($logger, $config, $userSession, $session, $service, $notifications, $classifier, $sequenceAnalyzer));
        });
        Util::connectHook('OC_Filesystem', 'preSetup', $this, 'addStorageWrapper');
        $this->registerNotificationNotifier();
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

    protected function registerNotificationNotifier()
    {
        $this->getContainer()->getServer()->getNotificationManager()->registerNotifier(function () {
            return $this->getContainer()->query(Notifier::class);
        }, function () {
            $l = $this->getContainer()->getServer()->getL10NFactory()->get(self::APP_ID);

            return [
                'id' => self::APP_ID,
                'name' => $l->t('Ransomware recovery'),
            ];
        });
    }
}
