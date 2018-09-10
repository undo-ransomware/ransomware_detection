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

namespace OCA\RansomwareDetection\Analyzer;

use OCA\RansomwareDetection\Classifier;
use OCA\RansomwareDetection\Monitor;

class FileTypeFunnellingAnalyzer
{
    /**
     * Analyzes if the written files fulfill the property of
     * file type funneling.
     *
     * Therefor classifies the sequence in three funneling classes:
     *
     * Class 1: All file extensions are unknown and the same.
     * Class 2: All file extensions are unknown and every extensions is distinct.
     * Class 3: All file extensions are unknown.
     * Class 4: All file extensions are known, but all files are corrupted.
     *
     * @param array $sequence
     *
     * @return int Class of file type funneling
     */
    public function analyze($sequence)
    {
        $writtenExtensions = [];
        $deletedExtensions = [];
        $corruptedFiles = [];
        $writtenFiles = [];
        $numberOfKnownFileExtensions = 0;
        $numberOfInfoFiles = 0;

        foreach ($sequence as $file) {
            if ($file->getType() === 'file') {
                switch ($file->getCommand()) {
                    case Monitor::WRITE:
                        if ($file->getSuspicionClass() === Classifier::NOT_SUSPICIOUS) {
                            if ($numberOfInfoFiles < SequenceAnalyzer::NUMBER_OF_INFO_FILES) {
                                $numberOfInfoFiles++;
                                break;
                            }
                        }
                        $numberOfKnownFileExtensions += $this->countKnownFileExtensions($file);
                        $pathInfo = pathinfo($file->getOriginalName());
                        $writtenExtensions[$pathInfo['extension']] = 1;
                        $writtenFiles[] = $file;
                        if ($file->getCorrupted()) {
                            $corruptedFiles[] = $file;
                        }
                        break;
                    case Monitor::READ:
                        break;
                    case Monitor::RENAME:
                        break;
                    case Monitor::DELETE:
                        $pathInfo = pathinfo($file->getOriginalName());
                        $deletedExtensions[] = $pathInfo['extension'];
                        break;
                    case Monitor::CREATE:
                        break;
                    default:
                        break;
                }
            }
        }

        // File type funneling must be at least 2 files
        if (sizeof($writtenFiles) > 2) {
            // Some files were written
            if ($numberOfKnownFileExtensions === 0) {
                if (sizeof($writtenExtensions) === sizeof($writtenFiles)) {
                    // All files have distinct unknown extensions
                    return 2;
                }
                if (sizeof($writtenExtensions) === 1) {
                    // All files have the same extension
                    return 2;
                }
                // All file extensions are unknown
                return 1;
            } elseif ($numberOfKnownFileExtensions === sizeof($writtenFiles)) {
                if ($numberOfKnownFileExtensions === sizeof($corruptedFiles)) {
                    // All files are corrupted
                    return 2;
                }
                // All written files have known extensions
                return 0;
            }
            // Some files are known
            return 0;
        }

        return 0;
    }

    /**
     * Count the known file extensions.
     *
     * @param Entity $file
     */
    private function countKnownFileExtensions($file)
    {
        if (intval($file->getFileExtensionClass()) === FileExtensionResult::NOT_SUSPICIOUS) {
            return 1;
        }
    }
}
