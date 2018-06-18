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

namespace OCA\RansomwareDetection\tests\Unit\Analyzer;

use OCA\RansomwareDetection\Analyzer\SequenceResult;
use Test\TestCase;

class SequenceResultTest extends TestCase
{
    /** @var SequenceResult */
    protected $sequenceResult;

    public function setUp()
    {
        parent::setUp();

        $this->sequenceResult = new SequenceResult(0.0, 0.0, 0.0, 0.0, 0.0, 0.0);
    }

    public function testConstruct()
    {
        $this->assertEquals($this->sequenceResult->getFileSuspicion(), 0.0);
        $this->assertEquals($this->sequenceResult->getQuantities(), 0.0);
        $this->assertEquals($this->sequenceResult->getFileTypeFunnelling(), 0.0);
        $this->assertEquals($this->sequenceResult->getSuspicionScore(), 0.0);
    }

    public function testFileSuspicion()
    {
        $this->sequenceResult->setFileSuspicion(1.0);
        $this->assertEquals($this->sequenceResult->getFileSuspicion(1.0), 1.0);
    }

    public function testQuantities()
    {
        $this->sequenceResult->setQuantities(1.0);
        $this->assertEquals($this->sequenceResult->getQuantities(1.0), 1.0);
    }

    public function testFileTypeFunnelling()
    {
        $this->sequenceResult->setFileTypeFunnelling(1.0);
        $this->assertEquals($this->sequenceResult->getFileTypeFunnelling(1.0), 1.0);
    }

    public function testSuspicionScore()
    {
        $this->sequenceResult->setSuspicionScore(1.0);
        $this->assertEquals($this->sequenceResult->getSuspicionScore(1.0), 1.0);
    }

    public function testSizeWritten()
    {
        $this->sequenceResult->setSizeWritten(1.0);
        $this->assertEquals($this->sequenceResult->getSizeWritten(1.0), 1.0);
    }

    public function testSizeDeleted()
    {
        $this->sequenceResult->setSizeDeleted(1.0);
        $this->assertEquals($this->sequenceResult->getSizeDeleted(1.0), 1.0);
    }
}
