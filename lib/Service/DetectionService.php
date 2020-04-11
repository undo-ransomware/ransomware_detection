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
        $detectionObjects = array();
        return $detectionObjects;
    }

    public function getDetection($id) {
        return DetectionSerializer::deserialize(json_decode(new Detection()));
    }
}