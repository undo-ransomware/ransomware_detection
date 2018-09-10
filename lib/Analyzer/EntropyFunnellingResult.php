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

class EntropyFunnellingResult implements \JsonSerializable
{
    /** @var float */
    protected $medianWritten;

    /** @var float */
    protected $medianDeleted;

    /** @var float */
    protected $rangeOfEntropyWritten;

    /** @var float */
    protected $rangeOfEntropyDeleted;

    /** @var float */
    protected $rangeOfStandardDeviationWritten;

    /** @var float */
    protected $rangeOfStandardDeviationDeleted;

    /** @var int */
    protected $entropyFunnelling;

    /**
     * @param float $medianWritten
     * @param float $medianDeleted
     * @param float $rangeOfEntropyWritten
     * @param float $rangeOfEntropyDeleted
     * @param float $rangeOfStandardDeviationWritten
     * @param float $rangeOfStandardDeviationDeleted
     * @param int   $entropyFunnelling
     */
    public function __construct(
        $medianWritten,
        $medianDeleted,
        $rangeOfEntropyWritten,
        $rangeOfEntropyDeleted,
        $rangeOfStandardDeviationWritten,
        $rangeOfStandardDeviationDeleted,
        $entropyFunnelling
    ) {
        $this->medianWritten = $medianWritten;
        $this->medianDeleted = $medianDeleted;
        $this->rangeOfEntropyWritten = $rangeOfEntropyWritten;
        $this->rangeOfEntropyDeleted = $rangeOfEntropyDeleted;
        $this->rangeOfStandardDeviationWritten = $rangeOfStandardDeviationWritten;
        $this->$rangeOfStandardDeviationDeleted = $rangeOfStandardDeviationDeleted;
        $this->entropyFunnelling = $entropyFunnelling;
    }

    public function getMedianWritten()
    {
        return $this->medianWritten;
    }

    public function setMedianWritten($medianWritten)
    {
        $this->medianWritten = $medianWritten;
    }

    public function getMedianDeleted()
    {
        return $this->medianDeleted;
    }

    public function setMedianDeleted($medianDeleted)
    {
        $this->medianDeleted = $medianDeleted;
    }

    public function getRangeOfEntropyWritten()
    {
        return $this->rangeOfEntropyWritten;
    }

    public function setRangeOfEntropyWritten($rangeOfEntropyWritten)
    {
        $this->rangeOfEntropyWritten = $rangeOfEntropyWritten;
    }

    public function getRangeOfEntropyDeleted()
    {
        return $this->rangeOfEntropyDeleted;
    }

    public function setRangeOfEntropyDeleted($rangeOfEntropyDeleted)
    {
        $this->rangeOfEntropyDeleted = $rangeOfEntropyDeleted;
    }

    public function getRangeOfStandardDeviationWritten()
    {
        return $this->rangeOfStandardDeviationWritten;
    }

    public function setRangeOfStandardDeviationWritten($rangeOfStandardDeviationWritten)
    {
        $this->rangeOfStandardDeviationWritten = $rangeOfStandardDeviationWritten;
    }

    public function getRangeOfStandardDeviationDeleted()
    {
        return $this->rangeOfStandardDeviationDeleted;
    }

    public function setRangeOfStandardDeviationDeleted($rangeOfStandardDeviationDeleted)
    {
        $this->rangeOfStandardDeviationDeleted = $rangeOfStandardDeviationDeleted;
    }

    public function getEntropyFunnelling()
    {
        return $this->entropyFunnelling;
    }

    public function setEntropyFunnelling($entropyFunnelling)
    {
        $this->entropyFunnelling = $entropyFunnelling;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
