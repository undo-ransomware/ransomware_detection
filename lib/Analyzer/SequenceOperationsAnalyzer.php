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

class SequenceOperationsAnalyzer
{
    /**
     * Sequence operation class.
     *
     * @var int
     */
    const NO_WRITE_AND_DELETE = 0;
    const ONLY_WRITE = 1;
    const ONLY_DELETE = 2;
    const EQUAL_WRITE_AND_DELETE = 3;
    const DIFF_WRITE_AND_DELETE = 4;

    /**
     * Classifies the operations in a sequence.
     *
     * @param array $sequence
     *
     * @return int
     */
    public function analyze($sequence)
    {
        $numberOfWrittenFiles = 0;
        $numberOfDeletedFiles = 0;
        $numberOfRenamedFiles = 0;

        $sequenceClass = self::NO_WRITE_AND_DELETE;

        foreach ($sequence as $fileOperation) {
            switch ($fileOperation->getCommand()) {
                case Monitor::WRITE:
                    $numberOfWrittenFiles++;
                    break;
                case Monitor::READ:
                    break;
                case Monitor::RENAME:
                    $numberOfRenamedFiles++;
                    break;
                case Monitor::DELETE:
                    $numberOfDeletedFiles++;
                    break;
                default:
                    break;
            }
        }

        if ($numberOfWrittenFiles > 0) {
            if ($numberOfDeletedFiles > 0) {
                if ($numberOfWrittenFiles === $numberOfDeletedFiles) {
                    $sequenceClass = self::EQUAL_WRITE_AND_DELETE;
                } else {
                    $sequenceClass = self::DIFF_WRITE_AND_DELETE;
                }
            } else {
                $sequenceClass = self::ONLY_WRITE;
            }
        } else {
            if ($numberOfDeletedFiles > 0) {
                $sequenceClass = self::ONLY_DELETE;
            }
        }

        return $sequenceClass;
    }
}
