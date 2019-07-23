<?php
/**
 * @copyright Copyright (c) 2019 Matthias Held <matthias.held@uni-konstanz.de>
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

namespace OCA\RansomwareDetection\Service;

use OCA\RansomwareDetection\AppInfo\Application;
use OCA\RansomwareDetection\RequestTemplate;
use OCA\RansomwareDetection\Model\Detection;
use OCA\RansomwareDetection\Model\DetectionDeserializer;
use OCP\IConfig;

class DetectionService {

    /** @var FileOperationService */
    protected $service;

    /** @var DetectionDeserializer */
    protected $deserializer;

    /** @var IConfig */
    protected $config;

    /** @var string */
    protected $userId;

    /**
     * @param FileOperationService $service
     * @param IConfig             $config
     * @param string              $userId
     */
    public function __construct(
        FileOperationService $service,
        DetectionDeserializer $deserializer,
        IConfig $config,
        $userId
    ) 
    {
        $this->service = $service;
        $this->deserializer = $deserializer;
        $this->config = $config;
        $this->userId = $userId;
    }

    public function getDetections() {
        try {
            $serviceUri = $this->config->getAppValue(Application::APP_ID, 'service_uri', 'http://localhost:5000');
            try {
                $detections = RequestTemplate::get($serviceUri . "/detection?userId=" . $this->userId);
                $detectionObjects = array();
                foreach($detections as $detection) {
                    array_push($detectionObjects, $this->deserializer->deserialize(json_decode($detection)));
                }
                return $detectionObjects;
            } catch (ClientException $ex) {
                if ($ex->getResponse()->getStatusCode() === 404) {
                    $this->logger->error("The detection service is not working correctly.");
                } else {
                    $this->logger->error("No connection to the detection service.");
                }
            } catch (ServerException $ex) {
                $this->logger->error("The detection service is not working correctly.");
            }
        } catch (ConnectException $ex) {
            //TODO: Notify the use by the Notifier
            $this->logger->error("No connection to the detection service.");
        }
    }

    public function getDetection($id) {
        try {
            $serviceUri = $this->config->getAppValue(Application::APP_ID, 'service_uri', 'http://localhost:5000');
            try {
                $detections= RequestTemplate::get($serviceUri . "/detection/" . $id);
            } catch (ClientException $ex) {
                if ($ex->getResponse()->getStatusCode() === 404) {
                    $this->logger->error("The detection service is not working correctly.");
                } else {
                    return DetectionSerializer::deserialize(json_decode($detection));
                }
            } catch (ServerException $ex) {
                $this->logger->error("The detection service is not working correctly.");
            }
        } catch (ConnectException $ex) {
            //TODO: Notify the use by the Notifier
            $this->logger->error("No connection to the detection service.");
        }
    }
}