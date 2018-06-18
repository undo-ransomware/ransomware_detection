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

    /**
     * Calculates the standard deviation.
     *
     * @param array $array
     *
     * @return float
     */
    public function sd($array)
    {
        if (is_array($array) && count($array) > 0) {
            // square root of sum of squares devided by N-1
            return sqrt(array_sum(array_map(
                function ($x, $mean) {
                    return pow($x - $mean, 2);
                },
                $array,
                array_fill(
                    0,
                    count($array),
                (array_sum($array) / count($array))
                )
            )) / (count($array) - 1));
        }

        return 0.0;
    }
}
