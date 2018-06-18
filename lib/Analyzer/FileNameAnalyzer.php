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

use OCA\RansomwareDetection\FileSignatureList;
use OCA\RansomwareDetection\Entropy\Entropy;
use OCP\ILogger;

class FileNameAnalyzer
{
    /**
     * File name entropy cut-off point between normal and suspicious.
     *
     * @var float
     */
    const ENTROPY_CUT_OFF = 4.0;

    /** @var ILogger */
    private $logger;

    /** @var Entropy */
    private $entropy;

    /**
     * @param ILogger $logger
     * @param Entropy $entropy
     */
    public function __construct(
        ILogger $logger,
        Entropy $entropy
    ) {
        $this->logger = $logger;
        $this->entropy = $entropy;
    }

    /**
     * Classifies a file name in NORMAL, SUSPICIOUS_FILE_NAME,
     * SUSPICIOUS_FILE_EXTENSION or SUSPICIOUS, if the file name
     * and file extension are suspicious.
     *
     * @param string $path
     *
     * @return FileNameResult
     */
    public function analyze($path)
    {
        $fileName = $this->getFileName($path);
        $extension = $this->getFileExtension($path);
        $class = FileNameResult::NORMAL;

        $isFileExtensionKnown = $this->isFileExtensionKnown($extension);
        if (!$isFileExtensionKnown) {
            $class += FileNameResult::SUSPICIOUS_FILE_EXTENSION;
        }
        $entropyOfFileName = $this->calculateEntropyOfFileName($fileName);
        if ($entropyOfFileName > self::ENTROPY_CUT_OFF) {
            $class += FileNameResult::SUSPICIOUS_FILE_NAME;
        }

        return new FileNameResult($class, $isFileExtensionKnown, $entropyOfFileName);
    }

    /**
     * Checks if the file extension is known.
     *
     * @param string $extension
     *
     * @return bool
     */
    private function isFileExtensionKnown($extension)
    {
        $signatures = FileSignatureList::getSignatures();
        foreach ($signatures as $signature) {
            if (in_array(strtolower($extension), $signature['extension'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the file name of a path.
     *
     * @param string $path
     *
     * @return string
     */
    private function getFileName($path)
    {
        $file = pathinfo($path);

        return $file['basename'];
    }

    /**
     * Returns the file extension of a file name.
     *
     * @param string $fileName
     *
     * @return string
     */
    private function getFileExtension($fileName)
    {
        $file = pathinfo($fileName);

        return $file['extension'];
    }

    /**
     * Calculates the entropy of the a file name.
     *
     * @param string $fileName
     *
     * @return float
     */
    private function calculateEntropyOfFileName($fileName)
    {
        return $this->entropy->calculateEntropy($fileName);
    }
}
