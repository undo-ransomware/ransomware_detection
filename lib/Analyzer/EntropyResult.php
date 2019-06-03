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

class EntropyResult
{
    /** @var float */
    private $entropy;

    /** @var float */
    private $standardDeviation;

    /**
     * @param int   $fileClass
     * @param float $entropy
     * @param float $standardDeviation
     */
    public function __construct(
        $entropy,
        $standardDeviation
    ) {
        $this->entropy = $entropy;
        $this->standardDeviation = $standardDeviation;
    }

    /**
     * @param float $entropy
     */
    public function setEntropy($entropy)
    {
        $this->entropy = $entropy;
    }

    /**
     * @return float
     */
    public function getEntropy()
    {
        return $this->entropy;
    }

    /**
     * @param float $standardDeviation
     */
    public function setStandardDeviation($standardDeviation)
    {
        $this->standardDeviation = $standardDeviation;
    }

    /**
     * @return float
     */
    public function getStandardDeviation()
    {
        return $this->standardDeviation;
    }
}
