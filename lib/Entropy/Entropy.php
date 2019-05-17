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

namespace OCA\RansomwareDetection\Entropy;

use OCP\ILogger;

class Entropy
{
    /** @var ILogger */
    private $logger;

    /**
     * @param IConfig $config
     */
    public function __construct(
        ILogger $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Calculates the entropy of data.
     *
     * @param string $data
     *
     * @return float
     */
    public function calculateEntropy($data)
    {
        $entropy = 0;
        $size = strlen($data);

        foreach (count_chars($data, 1) as $value) {
            $p = $value / $size;
            $entropy -= $p * log($p) / log(2);
        }

        return $entropy;
    }

    public function streamStandardDeviation($n, $sum, $mean) {
        return sqrt((1 / $n) * $sum - pow($mean, 2));
    }

    public function streamMean($oldMean, $value, $step) {
        $mean = 0;
        if ($step === 1) {
            $mean = (($step - 1) / $step) + ($value / $step);
        } else {
            $mean = $oldMean * (($step - 1) / $step) + ($value / $step);
        }
        return $mean;
    }
}
