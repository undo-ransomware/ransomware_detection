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

use OCA\RansomwareDetection\FileSignatures;
use OCA\RansomwareDetection\Entropy\Entropy;
use OCP\ILogger;

class FileExtensionAnalyzer
{

    /** @var ILogger */
    private $logger;

    /**
     * @param ILogger $logger
     */
    public function __construct(
        ILogger $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Classifies a file extension in NOT_SUSPICIOUS or SUSPICIOUS,
     * if the file extension are suspicious.
     *
     * @param string $path
     *
     * @return FileExtensionResult
     */
    public function analyze($path)
    {
        $extension = $this->getFileExtension($path);
        $class = FileExtensionResult::NOT_SUSPICIOUS;

        $isFileExtensionKnown = $this->isFileExtensionKnown($extension);
        if (!$isFileExtensionKnown) {
            $class = FileExtensionResult::SUSPICIOUS;
        }

        return new FileExtensionResult($class);
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
        $signatures = FileSignatures::getSignatures();
        foreach ($signatures as $signature) {
            if (in_array(strtolower($extension), $signature['extensions'])) {
                return true;
            }
        }

        return false;
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

        return isset($file['extension']) ? $file['extension'] : '';
    }
}
