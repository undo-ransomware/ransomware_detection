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
use OCA\RansomwareDetection\Events\FilesEvents;
use OCA\RansomwareDetection\FilesHooks;
use OCA\RansomwareDetection\Classifier;
use OCA\RansomwareDetection\Analyzer\EntropyAnalyzer;
use OCA\RansomwareDetection\Analyzer\SequenceAnalyzer;
use OCA\RansomwareDetection\Analyzer\SequenceSizeAnalyzer;
use OCA\RansomwareDetection\Analyzer\FileTypeFunnellingAnalyzer;
use OCA\RansomwareDetection\Analyzer\EntropyFunnellingAnalyzer;
use OCA\RansomwareDetection\Analyzer\FileCorruptionAnalyzer;
use OCA\RansomwareDetection\Analyzer\FileExtensionAnalyzer;
use OCA\RansomwareDetection\Entropy\Entropy;
use OCA\RansomwareDetection\Controller\DetectionController;
use OCA\RansomwareDetection\Notification\Notifier;
use OCA\RansomwareDetection\StorageWrapper;
use OCA\RansomwareDetection\Connector\Sabre\RequestPlugin;
use OCA\RansomwareDetection\Service\FileOperationService;
use OCA\RansomwareDetection\Service\RecoveredFileOperationService;
use OCA\RansomwareDetection\Service\DetectionService;
use OCA\RansomwareDetection\Mapper\FileOperationMapper;
use OCA\RansomwareDetection\Mapper\RecoveredFileOperationMapper;
use OCP\AppFramework\App;
use OCP\App\IAppManager;
use OCP\Files\IRootFolder;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Files\Storage\IStorage;
use OCP\Notification\IManager;
use OCP\Util;
use OCP\SabrePluginEvent;
use OCP\ILogger;
use OCP\IConfig;
use OCP\IUserSession;
use OCP\ISession;
use OCP\IRequest;

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

        $container->registerService('RecoveredFileOperationMapper', function ($c) {
            return new RecoveredFileOperationMapper(
                $c->query('ServerContainer')->getDb()
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
                $c->query(FileOperationMapper::class),
                $c->query('ServerContainer')->getUserSession()->getUser()->getUID()
            );
        });

        $container->registerService('RecoveredFileOperationService', function ($c) {
            return new RecoveredFileOperationService(
                $c->query(FileOperationMapper::class),
                $c->query(RecoveredFileOperationMapper::class),
                $c->query('ServerContainer')->getUserSession()->getUser()->getUID()
            );
        });

        $container->registerService('DetectionService', function ($c) {
            return new DetectionService(
                $c->query(ILogger::class),
                $c->query('FileOperationService'),
                $c->query('DetectionDeserializer'),
                $c->query('OCP\IConfig'),
                $c->query(Classifier::class),
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

        // controller
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

        $container->registerService('EntropyAnalyzer', function ($c) {
            return new EntropyAnalyzer(
                $c->query(ILogger::class),
                $c->query(IRootFolder::class),
                $c->query(Entropy::class),
                $c->query('ServerContainer')->getUserSession()->getUser()->getUID()
            );
        });

        $container->registerService('FileCorruptionAnalyzer', function ($c) {
            return new FileCorruptionAnalyzer(
                $c->query(ILogger::class),
                $c->query(IRootFolder::class),
                $c->query('ServerContainer')->getUserSession()->getUser()->getUID()
            );
        });

        $container->registerService('Monitor', function ($c) {
            return new Monitor(
                $c->query(IRequest::class),
                $c->query(IConfig::class),
                $c->query(ITimeFactory::class),
                $c->query(IAppManager::class),
                $c->query(ILogger::class),
                $c->query(IRootFolder::class),
                $c->query(EntropyAnalyzer::class),
                $c->query(FileOperationMapper::class),
                $c->query(FileExtensionAnalyzer::class),
                $c->query(FileCorruptionAnalyzer::class),
                $c->query('ServerContainer')->getUserSession()->getUser()->getUID()
            );
        });

        $container->registerService('FilesEvents', function ($c) {
            return new FilesEvents(
                $c->query(ILogger::class),
                $c->query(Monitor::class),
                $c->query('ServerContainer')->getUserSession()->getUser()->getUID()
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
        Util::connectHook('OC_Filesystem', 'post_create', FilesHooks::class, 'onFileCreate');
        // Util::connectHook('OC_Filesystem', 'post_update', FilesHooks::class, 'onFileUpdate');
        Util::connectHook('OC_Filesystem', 'post_rename', FilesHooks::class, 'onFileRename');
        Util::connectHook('OC_Filesystem', 'post_write', FilesHooks::class, 'onFileWrite');
        Util::connectHook('OC_Filesystem', 'post_delete', FilesHooks::class, 'onFileDelete');
        // Util::connectHook('OC_Filesystem', 'post_touch', FilesHooks::class, 'onFileTouch');
        // Util::connectHook('OC_Filesystem', 'post_copy', FilesHooks::class, 'onFileCopy');
        $this->registerNotificationNotifier();
    }

    protected function registerNotificationNotifier()
    {
        $this->getContainer()->getServer()->getNotificationManager()->registerNotifierService(Notifier::class);
    }
}
