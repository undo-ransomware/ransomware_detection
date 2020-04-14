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
use OCA\RansomwareDetection\Classifier;
use OCA\RansomwareDetection\Analyzer\SequenceAnalyzer;
use OCP\IConfig;
use OCP\ILogger;

class DetectionService {

    /** @var ILogger */
    protected $logger;

    /** @var FileOperationService */
    protected $service;

    /** @var DetectionDeserializer */
    protected $deserializer;

    /** @var IConfig */
    protected $config;

    /** @var Classifier */
    protected $classifier;

    /** @var string */
    protected $userId;

    /**
     * @param ILogger              $logger
     * @param FileOperationService  $service
     * @param IConfig               $config
     * @param Classifier            $classifier
     * @param SequenceAnalyzer      $sequenceAnalyzer
     * @param string                $userId
     */
    public function __construct(
        ILogger $logger,
        FileOperationService $service,
        DetectionDeserializer $deserializer,
        IConfig $config,
        Classifier $classifier,
        SequenceAnalyzer $sequenceAnalyzer,
        $userId
    ) 
    {
        $this->logger = $logger;
        $this->service = $service;
        $this->deserializer = $deserializer;
        $this->config = $config;
        $this->classifier = $classifier;
        $this->sequenceAnalyzer = $sequenceAnalyzer;
        $this->userId = $userId;
    }

    public function getDetections() {
        $files = $this->service->findAll();

        $sequences = array();
        $detectionObjects = array();

        // Classify files and put together the sequences.
        foreach ($files as $file) {
            $this->classifier->classifyFile($file);
            $sequences[$file->getSequence()][] = $file;
        }

        foreach ($sequences as $id => $sequence) {
            if (sizeof($sequence) >= $this->config->getAppValue(Application::APP_ID, 'minimum_sequence_length', 0)) {
                usort($sequence, function ($a, $b) {
                    return $b->getId() - $a->getId();
                });
                $result = $this->sequenceAnalyzer->analyze($id, $sequence);
                $this->logger->debug('detection: suspicion score of sequence ' . $id . ' is ' . $result->getSuspicionScore() . '.', array('app' => Application::APP_ID));
                if ($result->getSuspicionScore() >= 0.5) {
                    $detection = new Detection($id, $sequence);
                    array_push($detectionObjects, $detection);
                }
            }
        }
        
        return $detectionObjects;
    }

    public function getDetection($id) {
        return DetectionSerializer::deserialize(json_decode(new Detection(1, array())));
    }
}