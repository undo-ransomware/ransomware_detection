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

use OCA\RansomwareDetection\Monitor;
use OCA\RansomwareDetection\Analyzer\SequenceSizeAnalyzer;
use OCA\RansomwareDetection\Db\FileOperation;
use Test\TestCase;

class SequenceSizeAnalyzerTest extends TestCase
{
    /** @var SequenceSizeAnalyzer */
    protected $sequenceSizeAnalyzer;

    public function setUp()
    {
        parent::setUp();

        $this->sequenceSizeAnalyzer = new SequenceSizeAnalyzer();
    }

    public function dataAnalyze()
    {
        $fileOperation1 = new FileOperation();
        $fileOperation1->setCommand(Monitor::WRITE);
        $fileOperation1->setSize(100);

        $fileOperation2 = new FileOperation();
        $fileOperation2->setCommand(Monitor::DELETE);
        $fileOperation2->setSize(100);

        $fileOperation3 = new FileOperation();
        $fileOperation3->setCommand(Monitor::DELETE);
        $fileOperation3->setSize(150);

        $fileOperation4 = new FileOperation();
        $fileOperation4->setCommand(Monitor::RENAME);
        $fileOperation4->setSize(150);

        $fileOperation5 = new FileOperation();
        $fileOperation5->setCommand(Monitor::READ);
        $fileOperation5->setSize(150);

        return [
            ['sequence' => [$fileOperation1, $fileOperation2], 'result' => SequenceSizeAnalyzer::EQUAL_SIZE],
            ['sequence' => [$fileOperation1, $fileOperation3], 'result' => SequenceSizeAnalyzer::DIFF_SIZE],
            ['sequence' => [$fileOperation1, $fileOperation3], 'result' => SequenceSizeAnalyzer::DIFF_SIZE],
            ['sequence' => [$fileOperation4], 'result' => SequenceSizeAnalyzer::EQUAL_SIZE],
            ['sequence' => [$fileOperation5], 'result' => SequenceSizeAnalyzer::EQUAL_SIZE],
        ];
    }

    /**
     * @dataProvider dataAnalyze
     *
     * @param array $sequence
     * @param int   $result
     */
    public function testAnalyze($sequence, $result)
    {
        $ratio = $this->sequenceSizeAnalyzer->analyze($sequence);
        $this->assertEquals($ratio, $result);
    }
}
