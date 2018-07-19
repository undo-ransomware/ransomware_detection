<?php

/**
 * @copyright Copyright (c) 2018 Matthias Held <matthias.held@uni-konstanz.de>
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

namespace OCA\RansomwareDetection\Connector\Sabre;

use OCA\RansomwareDetection\Classifier;
use OCA\RansomwareDetection\AppInfo\Application;
use OCA\RansomwareDetection\Analyzer\SequenceAnalyzer;
use OCA\RansomwareDetection\Service\FileOperationService;
use OCP\Notification\IManager;
use OCP\IUserSession;
use OCP\ISession;
use OCP\ILogger;
use OCP\IConfig;
use Sabre\DAV\Server;
use Sabre\DAV\ServerPlugin;
use Sabre\HTTP\RequestInterface;
use Sabre\HTTP\ResponseInterface;

class RequestPlugin extends ServerPlugin
{
    /** @var Server */
    protected $server;

    /** @var ILogger */
    protected $logger;

    /** @var IConfig */
    protected $config;

    /** @var IUserSession */
    protected $userSession;

    /** @var ISession */
    protected $session;

    /** @var FileOperationService */
    protected $service;

    /** @var IManager */
    protected $notifications;

    /** @var Classifier */
    protected $classifier;

    /** @var SequenceAnalyzer */
    protected $sequenceAnalyzer;

    const PROPFIND_COUNT = 6;

    /**
     * @param ILogger              $logger
     * @param IConfig              $config
     * @param IUserSession         $userSession
     * @param ISession             $session
     * @param FileOperationService $service
     * @param IManager             $notifications
     * @param Classifier           $classifer
     * @param SequenceAnalyzer     $sequenceAnalyzer
     */
    public function __construct(
        ILogger $logger,
        IConfig $config,
        IUserSession $userSession,
        ISession $session,
        FileOperationService $service,
        IManager $notifications,
        Classifier $classifier,
        SequenceAnalyzer $sequenceAnalyzer
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->userSession = $userSession;
        $this->session = $session;
        $this->service = $service;
        $this->notifications = $notifications;
        $this->classifier = $classifier;
        $this->sequenceAnalyzer = $sequenceAnalyzer;
    }

    public function initialize(Server $server)
    {
        $this->server = $server;
        $server->on('method:PROPFIND', [$this, 'beforeHttpPropFind'], 100);
        $server->on('method:PUT', [$this, 'beforeHttpPut'], 100);
        $server->on('method:DELETE', [$this, 'beforeHttpDelete'], 100);
        $server->on('method:GET', [$this, 'beforeHttpGet'], 100);
        $server->on('method:POST', [$this, 'beforeHttpPost'], 100);
    }

    public function beforeHttpPropFind(RequestInterface $request, ResponseInterface $response)
    {
        $propfindCount = $this->config->getUserValue($this->userSession->getUser()->getUID(), Application::APP_ID, 'propfind_count:'.$this->session->getId(), 0);
        if ($propfindCount + 1 < self::PROPFIND_COUNT) {
            // less than PROPFIND_COUNT + 1 PROPFIND requests
            $this->config->setUserValue($this->userSession->getUser()->getUID(), Application::APP_ID, 'propfind_count:'.$this->session->getId(), $propfindCount + 1);
        } else {
            // more than PROPFIND_COUNT PROPFIND requests and no file is uploading
            $sequenceId = $this->config->getUserValue($this->userSession->getUser()->getUID(), Application::APP_ID, 'sequence_id', 0);
            $sequence = $this->service->findSequenceById([$sequenceId]);
            if (sizeof($sequence) > 0) {
                // get old sequence id
                // start a new sequence by increasing the sequence id
                $this->config->setUserValue($this->userSession->getUser()->getUID(), Application::APP_ID, 'sequence_id', $sequenceId + 1);
                $this->config->setUserValue($this->userSession->getUser()->getUID(), Application::APP_ID, 'propfind_count:'.$this->session->getId(), 0);
                $this->classifySequence($sequence);
            }
        }
    }

    public function beforeHttpPut(RequestInterface $request, ResponseInterface $response)
    {
        // extend if necessary
    }

    public function beforeHttpDelete(RequestInterface $request, ResponseInterface $response)
    {
        // extend if necessary
    }

    public function beforeHttpGet(RequestInterface $request, ResponseInterface $response)
    {
        // extend if necessary
    }

    public function beforeHttpPost(RequestInterface $request, ResponseInterface $response)
    {
        // extend if necessary
    }

    /**
     * Triggers a notification.
     */
    private function triggerNotification($notification)
    {
        $notification->setApp(Application::APP_ID);
        $notification->setDateTime(new \DateTime());
        $notification->setObject('ransomware', 'ransomware');
        $notification->setSubject('ransomware_attack_detected', []);
        $notification->setUser($this->userSession->getUser()->getUID());

        $this->notifications->notify($notification);
    }

    /**
     * Classify sequence and if suspicion level is reached
     * trigger a notification.
     *
     * @param array $sequence
     */
    private function classifySequence($sequence)
    {
        $sequenceSuspicionLevel = $this->config->getAppValue(Application::APP_ID, 'suspicionLevel', 3);

        foreach ($sequence as $file) {
            $this->classifier->classifyFile($file);
        }

        // sequence suspicion level
        if ($sequenceSuspicionLevel === 1) {
            $level = 3;
        } elseif ($sequenceSuspicionLevel === 2) {
            $level = 5;
        } elseif ($sequenceSuspicionLevel === 3) {
            $level = 6;
        }

        // sequence id is irrelevant so we use 0
        $sequenceResult = $this->sequenceAnalyzer->analyze(0, $sequence);
        if ($sequenceResult->getSuspicionScore() >= $level) {
            $this->triggerNotification($this->notifications->createNotification());
        }
    }
}
