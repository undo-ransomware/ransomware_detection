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

namespace OCA\RansomwareDetection\tests\Unit\Analyzer;

use OCA\RansomwareDetection\Analyzer\EntropyResult;
use Test\TestCase;

class EntropyResultTest extends TestCase
{
    /** @var EntropyResult */
    protected $entropyResult;

    public function setUp()
    {
        parent::setUp();

        $this->entropyResult = new EntropyResult(7.99, 0.004);
    }

    public function testConstruct()
    {
        $this->assertEquals($this->entropyResult->getEntropy(), 7.99);
        $this->assertEquals($this->entropyResult->getStandardDeviation(), 0.004);
    }

    public function testEntropy()
    {
        $this->assertEquals($this->entropyResult->getEntropy(), 7.99);
        $this->entropyResult->setEntropy(3.00);
        $this->assertEquals($this->entropyResult->getEntropy(), 3.00);
    }

    public function testStandardDeviation()
    {
        $this->assertEquals($this->entropyResult->getStandardDeviation(), 0.004);
        $this->entropyResult->setStandardDeviation(3.00);
        $this->assertEquals($this->entropyResult->getStandardDeviation(), 3.00);
    }
}
