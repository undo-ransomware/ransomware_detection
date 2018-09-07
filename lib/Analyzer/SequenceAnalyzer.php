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

use OCA\RansomwareDetection\Monitor;
use OCA\RansomwareDetection\Classifier;

class SequenceAnalyzer
{
    /**
     * Number of information files.
     *
     * @var int
     */
    const NUMBER_OF_INFO_FILES = 10;

    /** @var SequenceSizeAnalyzer */
    private $sequenceSizeAnalyzer;

    /** @var FileTypeFunnellingAnalyzer */
    private $fileTypeFunnellingAnalyzer;

    /** @var EntropyFunnellingAnalyzer */
    private $entropyFunnellingAnalyzer;

    /**
     * SequenceAnalyzer constructor.
     *
     * @param SequenceSizeAnalyzer       $sequenceSizeAnalyzer
     * @param FileTypeFunnellingAnalyzer $fileTypeFunnellingAnalyzer
     * @param EntropyFunnellingAnalyzer  $entropyFunnellingAnalyzer
     */
    public function __construct(
        SequenceSizeAnalyzer $sequenceSizeAnalyzer,
        FileTypeFunnellingAnalyzer $fileTypeFunnellingAnalyzer,
        EntropyFunnellingAnalyzer $entropyFunnellingAnalyzer
    ) {
        $this->sequenceSizeAnalyzer = $sequenceSizeAnalyzer;
        $this->fileTypeFunnellingAnalyzer = $fileTypeFunnellingAnalyzer;
        $this->entropyFunnellingAnalyzer = $entropyFunnellingAnalyzer;
    }

    /**
     * The analysis of the sequence is seperated in three parts:
     * The analysis: If the same number of files is deletedFilesd as written,
     * with the special addition that the number of written files is in the
     * range of [number of deletedFilesd files, number of deletedFilesd files + 4].
     * To enhance this analysis the sum of the size of the files deletedFilesd and
     * the sum of the size of the written files is compared.
     *
     * The next part is the analysis of the suspicion levels of the files written.
     * Therefor the suspicions levels are weighted:
     * High - 1
     * Middle - 0.75
     * Low - 0.5
     * None - 0.25
     *
     * summed up and divided by the sum of all written files. The higher the result,
     * the higher is the suspicion of the hole sequence.
     *
     * The last part is the file type funneling analysis.
     *
     * @param int   $sequenceId
     * @param array $sequence
     *
     * @return int The level of suspicion, if a sequence is malicious or not.
     */
    public function analyze($sequenceId, $sequence)
    {
        $sequenceResult = new SequenceResult($sequenceId, 0, 0, 0, 0, $sequence);
        if (sizeof($sequence) === 0) {
            return $sequenceResult;
        }

        $highSuspicionFiles = [];
        $middleSuspicionFiles = [];
        $lowSuspicionFiles = [];
        $noSuspicionFiles = [];
        $writtenFiles = [];
        $sizeOfWrittenFiles = 0;
        $deletedFiles = [];
        $sizeOfDeletedFiles = 0;
        $suspicionScore = 0;

        foreach ($sequence as $file) {
            if ($file->getType() === 'file') {
                switch ($file->getCommand()) {
                    case Monitor::WRITE:
                        $writtenFiles[] = $file;
                        $sizeOfWrittenFiles = $sizeOfWrittenFiles + $file->getSize();
                        break;
                    case Monitor::READ:
                        break;
                    case Monitor::RENAME:
                        break;
                    case Monitor::DELETE:
                        $deletedFiles[] = $file;
                        $sizeOfDeletedFiles = $sizeOfDeletedFiles + $file->getSize();
                        break;
                    case Monitor::CREATE:
                        break;
                    default:
                        break;
                }
                switch ($file->getSuspicionClass()) {
                    case Classifier::SUSPICIOUS:
                        $highSuspicionFiles[] = $file;
                        break;
                    case Classifier::MAYBE_SUSPICIOUS:
                        $middleSuspicionFiles[] = $file;
                        break;
                    case Classifier::NOT_SUSPICIOUS:
                        $noSuspicionFiles[] = $file;
                        break;
                    case Classifier::NO_INFORMATION:
                        break;
                    default:
                        break;
                }
            }
        }

        // compare files written and files deleted
        if (sizeof($writtenFiles) > 0 && sizeof($deletedFiles) > 0) {
            $sequenceResult->setSizeWritten($sizeOfWrittenFiles);
            $sequenceResult->setSizeDeleted($sizeOfDeletedFiles);
            $upperBound = sizeof($deletedFiles) + self::NUMBER_OF_INFO_FILES;
            if (sizeof($writtenFiles) <= $upperBound && sizeof($writtenFiles) >= sizeof($deletedFiles)) {
                if ($this->sequenceSizeAnalyzer->analyze($sequence) === SequenceSizeAnalyzer::EQUAL_SIZE) {
                    $sequenceResult->setQuantities(2);
                    $suspicionScore += 2;
                } else {
                    $sequenceResult->setQuantities(1);
                    $suspicionScore += 1;
                }
            }
        }

        $numberOfWrittenFiles = sizeof($highSuspicionFiles) + sizeof($middleSuspicionFiles)
                                + sizeof($lowSuspicionFiles) + sizeof($noSuspicionFiles);

        // remove info files from the weight
        $numberOfInfoFiles = self::NUMBER_OF_INFO_FILES;
        if (sizeof($noSuspicionFiles) < self::NUMBER_OF_INFO_FILES) {
            $numberOfInfoFiles = sizeof($noSuspicionFiles);
        }

        // weight the suspicion levels.
        $suspicionSum = (sizeof($highSuspicionFiles) * 1) + (sizeof($middleSuspicionFiles) * 0.75)
                            + (sizeof($lowSuspicionFiles) * 0.5) + ((sizeof($noSuspicionFiles) - $numberOfInfoFiles) * 0.25);

        // check for division by zero.
        if (($numberOfWrittenFiles - $numberOfInfoFiles) > 0) {
            $sequenceResult->setFileSuspicion($suspicionSum / ($numberOfWrittenFiles - $numberOfInfoFiles));
            $suspicionScore += $suspicionSum / ($numberOfWrittenFiles - $numberOfInfoFiles);
        }

        // entropy funnelling
        $entropyFunnelling = $this->entropyFunnellingAnalyzer->analyze($deletedFiles, $writtenFiles);
        $sequenceResult->setEntropyFunnelling($entropyFunnelling);
        $suspicionScore += $entropyFunnelling->getEntropyFunnelling();

        // check for file type funneling
        $fileTypeFunnelling = $this->fileTypeFunnellingAnalyzer->analyze($sequence);
        $sequenceResult->setFileTypeFunnelling($fileTypeFunnelling);
        $suspicionScore += $fileTypeFunnelling;

        $sequenceResult->setSuspicionScore($suspicionScore);

        return $sequenceResult;
    }
}
