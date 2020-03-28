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
use OCA\RansomwareDetection\Analyzer\SequenceOperationsAnalyzer;
use OCA\RansomwareDetection\Db\FileOperation;
use Test\TestCase;

class SequenceOperationsAnalyzerTest extends TestCase
{
    /** @var SequenceOperationsAnalyzer */
    protected $sequenceOperationsAnalyzer;

    public function setUp(): void
    {
        parent::setUp();

        $this->sequenceOperationsAnalyzer = new SequenceOperationsAnalyzer();
    }

    public function dataAnalyze()
    {
        $fileOperation1 = new FileOperation();
        $fileOperation1->setCommand(Monitor::WRITE);

        $fileOperation2 = new FileOperation();
        $fileOperation2->setCommand(Monitor::DELETE);

        $fileOperation3 = new FileOperation();
        $fileOperation3->setCommand(Monitor::RENAME);

        $fileOperation4 = new FileOperation();
        $fileOperation4->setCommand(Monitor::READ);

        return [
            ['sequence' => [], 'result' => SequenceOperationsAnalyzer::NO_WRITE_AND_DELETE],
            ['sequence' => [$fileOperation3], 'result' => SequenceOperationsAnalyzer::NO_WRITE_AND_DELETE],
            ['sequence' => [$fileOperation4], 'result' => SequenceOperationsAnalyzer::NO_WRITE_AND_DELETE],
            ['sequence' => [$fileOperation3, $fileOperation4], 'result' => SequenceOperationsAnalyzer::NO_WRITE_AND_DELETE],
            ['sequence' => [$fileOperation1], 'result' => SequenceOperationsAnalyzer::ONLY_WRITE],
            ['sequence' => [$fileOperation2], 'result' => SequenceOperationsAnalyzer::ONLY_DELETE],
            ['sequence' => [$fileOperation1, $fileOperation2], 'result' => SequenceOperationsAnalyzer::EQUAL_WRITE_AND_DELETE],
            ['sequence' => [$fileOperation1, $fileOperation2, $fileOperation2], 'result' => SequenceOperationsAnalyzer::DIFF_WRITE_AND_DELETE],
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
        $ratio = $this->sequenceOperationsAnalyzer->analyze($sequence);
        $this->assertEquals($ratio, $result);
    }
}
