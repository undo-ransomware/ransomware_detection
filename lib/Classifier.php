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

namespace OCA\RansomwareDetection;

use OCA\RansomwareDetection\Analyzer\FileExtensionResult;
use OCA\RansomwareDetection\Analyzer\EntropyResult;
use OCA\RansomwareDetection\Db\FileOperationMapper;
use OCA\RansomwareDetection\Service\FileOperationService;
use OCP\ILogger;

class Classifier
{
    /**
     * File suspicion levels.
     *
     * @var int
     */
    const SUSPICIOUS = 1;
    const MAYBE_SUSPICIOUS = 2;
    const NOT_SUSPICIOUS = 4;
    const NO_INFORMATION = 5;

    /** @var ILogger */
    private $logger;

    /** @var FileOperationMapper */
    private $mapper;

    /** @var FileOperationService */
    private $service;

    /**
     * @param ILogger              $logger
     * @param FileOperationMapper  $mapper
     * @param FileOperationService $service
     */
    public function __construct(
        ILogger $logger,
        FileOperationMapper $mapper,
        FileOperationService $service
    ) {
        $this->logger = $logger;
        $this->mapper = $mapper;
        $this->service = $service;
    }

    /**
     * Classifies a file.
     *
     * @param Entity $file
     *
     * @return Entity Classified file.
     */
    public function classifyFile($file)
    {
        $file->setSuspicionClass(self::NO_INFORMATION);
        if ($file->getCommand() === Monitor::WRITE ||
            $file->getCommand() === Monitor::RENAME ||
            $file->getCommand() === Monitor::DELETE ||
            $file->getCommand() === Monitor::CREATE
        ) {
            if ($file->getFileClass() === EntropyResult::ENCRYPTED) {
                if ($file->getFileExtensionClass() === FileExtensionResult::SUSPICIOUS) {
                    $file->setSuspicionClass(self::SUSPICIOUS);
                } else {
                    $file->setSuspicionClass(self::MAYBE_SUSPICIOUS);
                }
            } elseif ($file->getFileClass() === EntropyResult::COMPRESSED) {
                if ($file->getFileExtensionClass() === FileExtensionResult::SUSPICIOUS) {
                    $file->setSuspicionClass(self::MAYBE_SUSPICIOUS);
                } else {
                    $file->setSuspicionClass(self::NOT_SUSPICIOUS);
                }
            } else {
                $file->setSuspicionClass(self::NOT_SUSPICIOUS);
            }
        }

        return $file;
    }
}
