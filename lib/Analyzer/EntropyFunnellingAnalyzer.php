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
use OCP\ILogger;

class EntropyFunnellingAnalyzer
{
    /** @var ILogger */
    protected $logger;

    /**
     * @param ILogger $logger
     */
    public function __construct(
        ILogger $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * The entropy funnelling can be divided into two parts:.
     *
     * 1. The range of the entropy of the deleted files must be wider
     *    than the range of the entropy of the written files without the info files.
     * 2. The range of the standard deviation of the deleted files must be wider
     *    than the range of the standard deviation of the written files without the info files.
     *
     * Each of the indicators increase the entropy funnelling score by one.
     *
     * @param array $deletedFiles
     * @param array $writtenFiles
     *
     * @return EntropyFunnellingResult
     */
    public function analyze($deletedFiles, $writtenFiles)
    {
        // prepare data
        $entropyOfDeletedFiles = [];
        $entropyOfWrittenFiles = [];
        $standardDeviationOfDeletedFiles = [];
        $standardDeviationOfWrittenFiles = [];
        $numberOfInfoFiles = 0;

        foreach ($deletedFiles as $deletedFile) {
            array_push($entropyOfDeletedFiles, $deletedFile->getEntropy());
            array_push($standardDeviationOfDeletedFiles, $deletedFile->getStandardDeviation());
        }
        foreach ($writtenFiles as $writtenFile) {
            // remove the entropy of info files from $entropyOfWrittenFiles
            if ($writtenFile->getSuspicionClass() === Classifier::NOT_SUSPICIOUS) {
                if ($numberOfInfoFiles < SequenceAnalyzer::NUMBER_OF_INFO_FILES) {
                    $numberOfInfoFiles++;
                    break;
                }
            }
            array_push($entropyOfWrittenFiles, $writtenFile->getEntropy());
            array_push($standardDeviationOfWrittenFiles, $writtenFile->getStandardDeviation());
        }

        // analyze data
        $entropyFunnellingResult = new EntropyFunnellingResult(0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0);
        $medianOfEntropyDeleted = $this->median($entropyOfDeletedFiles);
        $medianOfEntropyWritten = $this->median($entropyOfWrittenFiles);
        $entropyFunnellingResult->setMedianDeleted($medianOfEntropyDeleted);
        $entropyFunnellingResult->setMedianWritten($medianOfEntropyWritten);

        $entropyFunnellingScore = 0;

        $rangeOfEntropyDeleted = $this->range($entropyOfDeletedFiles);
        $rangeOfStandardDeviationDeleted = $this->average($standardDeviationOfDeletedFiles);
        $rangeOfEntropyWritten = $this->range($entropyOfWrittenFiles);
        $rangeOfStandardDeviationWritten = $this->average($standardDeviationOfWrittenFiles);
        $entropyFunnellingResult->setRangeOfEntropyDeleted($rangeOfEntropyDeleted);
        $entropyFunnellingResult->setRangeOfEntropyWritten($rangeOfEntropyWritten);
        $entropyFunnellingResult->setRangeOfStandardDeviationDeleted($rangeOfStandardDeviationDeleted);
        $entropyFunnellingResult->setRangeOfStandardDeviationWritten($rangeOfStandardDeviationWritten);

        // range of $entropyOfDeletedFiles must be wider than range of $entropyOfWrittenFiles
        if ($rangeOfEntropyDeleted > $rangeOfEntropyWritten) {
            $entropyFunnellingScore = $entropyFunnellingScore + 1;
            // range of $standardDeviationOfDeletedFiles must be wider than range of $standardDeviationOfWrittenFiles
            if ($rangeOfStandardDeviationDeleted > $rangeOfStandardDeviationWritten) {
                $entropyFunnellingScore = $entropyFunnellingScore + 1;
            }
        }

        $entropyFunnellingResult->setEntropyFunnelling($entropyFunnellingScore);

        return $entropyFunnellingResult;
    }

    /**
     * Calculates the average of an array.
     *
     * @param array $array
     *
     * @return float
     */
    private function average($array)
    {
        if (is_array($array) && count($array) > 0) {
            return array_sum($array) / count($array);
        }

        return 0.0;
    }

    /**
     * Calculates the range of an array.
     *
     * @param array $array
     *
     * @return float
     */
    private function range($array)
    {
        if (is_array($array) && count($array) > 0) {
            sort($array);

            return $array[count($array) - 1] - $array[0];
        }

        return 0.0;
    }

     /**
      * Calculates the median of an array.
      *
      * @param  array $array
      *
      * @return float
      */
     public function median($array)
     {
         if (is_array($array) && count($array) > 0) {
             $count = count($array);
             sort($array);
             $mid = floor(($count - 1) / 2);

             return ($array[$mid] + $array[$mid + 1 - $count % 2]) / 2;
         }

         return 0.0;
     }
}
