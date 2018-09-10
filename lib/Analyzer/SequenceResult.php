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

class SequenceResult implements \JsonSerializable
{
    /** @var int */
    protected $sequenceId;

    /** @var float */
    protected $fileSuspicion;

    /** @var float */
    protected $quantities;

    /** @var int */
    protected $fileTypeFunnelling;

    /** @var int */
    protected $entropyFunnelling;

    /** @var float */
    protected $suspicionScore;

    /** @var int */
    protected $sizeWritten;

    /** @var int */
    protected $sizeDeleted;

    /** @var array */
    protected $sequence;

    /**
     * @param int   $sequenceId
     * @param float $fileSuspicion
     * @param float $quantities
     * @param float $fileTypeFunnelling
     * @param float $suspicionScore
     * @param array $sequence
     */
    public function __construct(
        $sequenceId,
        $fileSuspicion,
        $quantities,
        $fileTypeFunnelling,
        $suspicionScore,
        $sequence
    ) {
        $this->sequenceId = $sequenceId;
        $this->fileSuspicion = $fileSuspicion;
        $this->quantities = $quantities;
        $this->fileTypeFunnelling = $fileTypeFunnelling;
        $this->suspicionScore = $suspicionScore;
        $this->sequence = $sequence;
    }

    public function getSequenceId()
    {
        return $this->sequenceId;
    }

    public function setSequenceId($sequenceId)
    {
        $this->sequenceId = $sequenceId;
    }

    public function getFileSuspicion()
    {
        return $this->fileSuspicion;
    }

    public function setFileSuspicion($fileSuspicion)
    {
        $this->fileSuspicion = $fileSuspicion;
    }

    public function getQuantities()
    {
        return $this->quantities;
    }

    public function setQuantities($quantities)
    {
        $this->quantities = $quantities;
    }

    public function getFileTypeFunnelling()
    {
        return $this->fileTypeFunnelling;
    }

    public function setFileTypeFunnelling($fileTypeFunnelling)
    {
        $this->fileTypeFunnelling = $fileTypeFunnelling;
    }

    public function getEntropyFunnelling()
    {
        return $this->entropyFunnelling;
    }

    public function setEntropyFunnelling($entropyFunnelling)
    {
        $this->entropyFunnelling = $entropyFunnelling;
    }

    public function getSuspicionScore()
    {
        return $this->suspicionScore;
    }

    public function setSuspicionScore($suspicionScore)
    {
        $this->suspicionScore = $suspicionScore;
    }

    public function setSizeWritten($sizeWritten)
    {
        $this->sizeWritten = $sizeWritten;
    }

    public function getSizeWritten()
    {
        return $this->sizeWritten;
    }

    public function setSizeDeleted($sizeDeleted)
    {
        $this->sizeDeleted = $sizeDeleted;
    }

    public function getSizeDeleted()
    {
        return $this->sizeDeleted;
    }

    public function getSequence()
    {
        return $sequence;
    }

    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
