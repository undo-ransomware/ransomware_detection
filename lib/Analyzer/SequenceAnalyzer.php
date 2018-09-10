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
     * Suspicious - 1
     * Maybe suspicious - 0.5
     * Not suspicious - 0.25
     *
     * summed up and divided by the sum of all written files. The higher the result,
     * the higher is the suspicion of the hole sequence.
     *
     * The last part is the file type funneling analysis.
     *
     * @param int   $sequenceId
     * @param array $sequence
     *
     * @return SequenceResult
     */
    public function analyze($sequenceId, $sequence)
    {
        $sequenceResult = new SequenceResult($sequenceId, 0, 0, 0, 0, $sequence);
        if (sizeof($sequence) === 0) {
            return $sequenceResult;
        }

        $files = ['written' => [], 'size_written' => 0, 'deleted' => [], 'size_deleted' => 0, 'suspicious' => [], 'maybe_suspicious' => [], 'not_suspicious' => []];
        $suspicionScore = 0;

        foreach ($sequence as $file) {
            if ($file->getType() === 'file') {
                switch ($file->getCommand()) {
                    case Monitor::WRITE:
                        $files['written'][] = $file;
                        $files['size_written'] = $files['size_written'] + $file->getSize();
                        break;
                    case Monitor::READ:
                        break;
                    case Monitor::RENAME:
                        break;
                    case Monitor::DELETE:
                        $files['deleted'][] = $file;
                        $files['size_deleted'] = $files['size_deleted'] + $file->getSize();
                        break;
                    case Monitor::CREATE:
                        break;
                    default:
                        break;
                }
                switch ($file->getSuspicionClass()) {
                    case Classifier::SUSPICIOUS:
                        $files['suspicious'][] = $file;
                        break;
                    case Classifier::MAYBE_SUSPICIOUS:
                        $files['maybe_suspicious'][] = $file;
                        break;
                    case Classifier::NOT_SUSPICIOUS:
                        $files['not_suspicious'][] = $file;
                        break;
                    case Classifier::NO_INFORMATION:
                        break;
                    default:
                        break;
                }
            }
        }

        // compare files written and files deleted
        if (sizeof($files['written']) > 0 && sizeof($files['deleted']) > 0) {
            $sequenceResult->setSizeWritten($files['size_written']);
            $sequenceResult->setSizeDeleted($files['size_deleted']);
            $upperBound = sizeof($files['deleted']) + self::NUMBER_OF_INFO_FILES;
            if (sizeof($writtenFiles) <= $upperBound && sizeof($files['written']) >= sizeof($files['deleted'])) {
                if ($this->sequenceSizeAnalyzer->analyze($sequence) === SequenceSizeAnalyzer::EQUAL_SIZE) {
                    $sequenceResult->setQuantities(2);
                    $suspicionScore += 2;
                } else {
                    $sequenceResult->setQuantities(1);
                    $suspicionScore += 1;
                }
            }
        }

        $numberOfWrittenFiles = sizeof($files['suspicious']) + sizeof($files['maybe_suspicious']) + sizeof($files['not_suspicious']);

        // remove info files from the weight
        $numberOfInfoFiles = self::NUMBER_OF_INFO_FILES;
        if (sizeof($files['not_suspicious']) < self::NUMBER_OF_INFO_FILES) {
            $numberOfInfoFiles = sizeof($files['not_suspicious']);
        }

        // weight the suspicion levels.
        $suspicionSum = (sizeof($files['suspicious']) * 1) + (sizeof($files['maybe_suspicious']) * 0.5) + ((sizeof($files['not_suspicious']) - $numberOfInfoFiles) * 0.25);

        // check for division by zero.
        if (($numberOfWrittenFiles - $numberOfInfoFiles) > 0) {
            $sequenceResult->setFileSuspicion($suspicionSum / ($numberOfWrittenFiles - $numberOfInfoFiles));
            $suspicionScore += $suspicionSum / ($numberOfWrittenFiles - $numberOfInfoFiles);
        }

        // entropy funnelling
        $entropyFunnelling = $this->entropyFunnellingAnalyzer->analyze($files['deleted'], $files['written']);
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
