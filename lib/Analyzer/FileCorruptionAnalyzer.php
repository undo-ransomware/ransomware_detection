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

namespace OCA\RansomwareDetection\Analyzer;

use OCA\RansomwareDetection\AppInfo\Application;
use OCA\RansomwareDetection\FileSignatures;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\Files\File;
use OCP\ILogger;

class FileCorruptionAnalyzer
{
    /** @var ILogger */
    private $logger;

    /** @var IRootFolder */
    private $rootFolder;

    /** @var string */
    private $userId;

    /**
     * @param ILogger     $logger
     * @param IRootFolder $rootFolder
     * @param string      $userId
     */
    public function __construct(
        ILogger $logger,
        IRootFolder $rootFolder,
        $userId
    ) {
        $this->logger = $logger;
        $this->rootFolder = $rootFolder;
        $this->userId = $userId;
    }

    /**
     * Analysis a file if it's corrupted or not.
     *
     * @param  File $node
     * @return FileCorruptionResult
     */
    public function analyze($node)
    {
        return $this->isCorrupted($node);
    }

    /**
     * Checks the file for existing file header informations and compares them,
     * if found, to the file extension.
     *
     * @param  File    $node
     * @return FileCorruptionResult
     */
    protected function isCorrupted(File $node)
    {
        $signatures = FileSignatures::getSignatures();

        try {
            $data = $node->getContent();
            $pathInfo = pathinfo($node->getPath());
            foreach ($signatures as $signature) {
                $isFileCorrupted = true;
                if (in_array(strtolower($pathInfo['extension']), $signature['extensions'])) {
                    // txt file extension has no signature, but is not corrupted
                    if (array_key_exists('exists', $signature['signature'])) {
                        if ($signature['signature']['exists'] === false) {
                            return new FileCorruptionResult(false);
                        }
                    }
                    // starting byte sequence
                    if (array_key_exists('starting', $signature['signature'])) {
						foreach ($signature['signature']['starting']['bytes'] as $bytes) {
							if (preg_match($bytes, strtolower(bin2hex(substr($data, $signature['signature']['starting']['offset'], strlen($bytes) / 2))))) {
									$isFileCorrupted = false;
							}
						}
                    }
                    // trailing byte sequence
                    if (array_key_exists('trailing', $signature['signature'])) {
                        $trailingIsNotMatching = true;
						foreach ($signature['signature']['trailing']['bytes'] as $bytes) {
							$trailingOffset = strlen($data) - $signature['signature']['trailing']['offset'] - strlen($bytes) / 2;
							if (preg_match($bytes, strtolower(bin2hex(substr($data, $trailingOffset, strlen($bytes) / 2))))) {
									$trailingIsNotMatching = false;
							}
						}
						$isFileCorrupted = $isFileCorrupted || $trailingIsNotMatching;
                        return new FileCorruptionResult($isFileCorrupted);
                    }
                    return new FileCorruptionResult($isFileCorrupted);
                }
            }

            return new FileCorruptionResult(false);
        } catch (\OCP\Files\NotPermittedException $exception) {
            $this->logger->debug('isCorrupted: Not permitted.', array('app' => Application::APP_ID));

            return new FileCorruptionResult(false);
        } catch (\OCP\Lock\LockedException $exception) {
            $this->logger->debug('isCorrupted: File is locked.', array('app' => Application::APP_ID));

            return new FileCorruptionResult(false);
        }
    }
}
