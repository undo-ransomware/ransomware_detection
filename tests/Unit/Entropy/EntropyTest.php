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

namespace OCA\RansomwareDetection\tests\Unit\Entropy;

use OCA\RansomwareDetection\Entropy\Entropy;
use OCP\ILogger;
use Test\TestCase;

class EntropyTest extends TestCase
{
    /** @var \OCA\RansomwareDetection\Entropy\Entropy */
    protected $entropy;
    /** @var ILogger|\PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    public function setUp(): void
    {
        parent::setUp();

        $this->logger = $this->createMock(ILogger::class);
        $this->entropy = new Entropy($this->logger);
    }

    public function dataCalculateEntropy()
    {
        $tests = [];
        $tests[] = ['foo', 0.918296];
        $tests[] = ['1234567890', 3.321928];

        return $tests;
    }

    /**
     * @dataProvider dataCalculateEntropy
     *
     * @param string $data
     * @param float  $entropy
     */
    public function testCalculateEntropy($data, $entropy)
    {
        $this->assertEquals(number_format($this->entropy->calculateEntropy($data), 6), $entropy);
    }

    public function dataSd()
    {
        $tests = [];
        $tests[] = [[10, 2, 38, 23, 38, 23, 21], 12.298996];
        $tests[] = [[10, 12, 23, 23, 16, 23, 21, 16], 4.898979];
        $tests[] = [[-5, 1, 8, 7, 2], 4.673329];

        return $tests;
    }

    /**
     * @dataProvider dataSd
     *
     * @param string $data
     * @param float  $entropy
     */
    public function testSd($data, $sd)
    {
        $sum = 0.0;
        $mean = 0.0;
        $standardDeviation = 0.0;
        foreach($data as $key => $value) {
            $sum = $sum + pow($value, 2);
            $mean = $this->invokePrivate($this->entropy, 'calculateMeanOfSeries', [$mean, $value, $key + 1]);
            $standardDeviation = $this->invokePrivate($this->entropy, 'calculateStandardDeviationOfSeries', [$key + 1, $sum, $mean]);
        }
        $this->assertEquals(number_format($standardDeviation, 6), $sd);
    }
}
