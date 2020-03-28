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
use OCA\RansomwareDetection\Analyzer\SequenceAnalyzer;
use OCA\RansomwareDetection\Analyzer\SequenceSizeAnalyzer;
use OCA\RansomwareDetection\Analyzer\FileTypeFunnellingAnalyzer;
use OCA\RansomwareDetection\Analyzer\EntropyFunnellingAnalyzer;
use OCA\RansomwareDetection\Classifier;
use OCA\RansomwareDetection\Db\FileOperation;
use OCP\ILogger;
use Test\TestCase;

class SequenceAnalyzerTest extends TestCase
{
    /** @var SequenceAnalyzer */
    protected $sequenceAnalyzer;

    /** @var FileTypeFunnellingAnalyzer */
    protected $fileTypeFunnellingAnalyzer;

    /** @var EntropyFunnellingAnalyzer */
    protected $entropyFunnellingAnalyzer;

    /** @var SequenceSizeAnalyzer */
    protected $sequenceSizeAnalyzer;

    public function setUp(): void
    {
        parent::setUp();

        $this->sequenceSizeAnalyzer = new SequenceSizeAnalyzer();
        $this->fileTypeFunnellingAnalyzer = new FileTypeFunnellingAnalyzer();
        $this->entropyFunnellingAnalyzer = new EntropyFunnellingAnalyzer($this->createMock(ILogger::class));
        $this->sequenceAnalyzer = new SequenceAnalyzer($this->sequenceSizeAnalyzer, $this->fileTypeFunnellingAnalyzer, $this->entropyFunnellingAnalyzer);
    }

    public function dataAnalyze()
    {
        $fileOperation1 = new FileOperation();
        $fileOperation1->setCommand(Monitor::WRITE);
        $fileOperation1->setOriginalName('test.csv');
        $fileOperation1->setSize(123000);
        $fileOperation1->setType('file');
        $fileOperation1->setSuspicionClass(Classifier::SUSPICIOUS);

        $fileOperation2 = new FileOperation();
        $fileOperation2->setCommand(Monitor::DELETE);
        $fileOperation2->setOriginalName('test.csv');
        $fileOperation2->setSize(123000);
        $fileOperation2->setType('file');
        $fileOperation2->setSuspicionClass(Classifier::SUSPICIOUS);

        $fileOperation3 = new FileOperation();
        $fileOperation3->setCommand(Monitor::WRITE);
        $fileOperation3->setOriginalName('test.csv');
        $fileOperation3->setSize(123000);
        $fileOperation3->setType('file');
        $fileOperation3->setSuspicionClass(Classifier::MAYBE_SUSPICIOUS);

        $fileOperation4 = new FileOperation();
        $fileOperation4->setCommand(Monitor::WRITE);
        $fileOperation4->setOriginalName('test.csv');
        $fileOperation4->setSize(123000);
        $fileOperation4->setType('file');
        $fileOperation4->setSuspicionClass(Classifier::NOT_SUSPICIOUS);

        $fileOperation5 = new FileOperation();
        $fileOperation5->setCommand(Monitor::WRITE);
        $fileOperation5->setOriginalName('test.csv');
        $fileOperation5->setSize(123000);
        $fileOperation5->setType('file');
        $fileOperation5->setSuspicionClass(Classifier::NOT_SUSPICIOUS);

        $fileOperation6 = new FileOperation();
        $fileOperation6->setCommand(Monitor::WRITE);
        $fileOperation6->setOriginalName('test.csv');
        $fileOperation6->setSize(123000);
        $fileOperation6->setType('file');
        $fileOperation6->setSuspicionClass(Classifier::NO_INFORMATION);

		$fileOperation7 = new FileOperation();
        $fileOperation7->setCommand(Monitor::DELETE);
        $fileOperation7->setType('file');
		$fileOperation7->setSize(123000);
        $fileOperation7->setOriginalName('test.csv');

		$fileOperation8 = new FileOperation();
        $fileOperation8->setCommand(Monitor::DELETE);
        $fileOperation8->setType('file');
		$fileOperation8->setSize(1230022);
        $fileOperation8->setOriginalName('test.csv');

        $fileOperationRead = new FileOperation();
        $fileOperationRead->setCommand(Monitor::READ);
        $fileOperationRead->setType('file');
		$fileOperationRead->setSize(123000);
        $fileOperationRead->setOriginalName('test.csv');

        $fileOperationRename = new FileOperation();
        $fileOperationRename->setCommand(Monitor::RENAME);
        $fileOperationRename->setType('file');
        $fileOperationRename->setOriginalName('test.csv');

        $fileOperationUnknown = new FileOperation();
        $fileOperationUnknown->setCommand(100);
        $fileOperationUnknown->setType('file');
        $fileOperationUnknown->setOriginalName('test.csv');

		$fileOperationCreate = new FileOperation();
        $fileOperationCreate->setCommand(Monitor::CREATE);
        $fileOperationCreate->setType('file');
        $fileOperationCreate->setOriginalName('test.csv');

        //TODO: extend tests
        return [
            ['sequence' => [], 'suspicionScore' => 0],
            ['sequence' => [$fileOperation1], 'suspicionScore' => 1],
            ['sequence' => [$fileOperation2], 'suspicionScore' => 1],
            ['sequence' => [$fileOperationRead], 'suspicionScore' => 0],
            ['sequence' => [$fileOperationRename], 'suspicionScore' => 0],
			['sequence' => [$fileOperationUnknown], 'suspicionScore' => 0],
            ['sequence' => [$fileOperationCreate], 'suspicionScore' => 0],
			['sequence' => [$fileOperation6], 'suspicionScore' => 0],
            ['sequence' => [$fileOperation3], 'suspicionScore' => 0.5],
            ['sequence' => [$fileOperation4], 'suspicionScore' => 0],
            ['sequence' => [$fileOperation5], 'suspicionScore' => 0],
            ['sequence' => [$fileOperation6], 'suspicionScore' => 0],
			['sequence' => [$fileOperation6, $fileOperation7], 'suspicionScore' => 1],
			['sequence' => [$fileOperation6, $fileOperation8], 'suspicionScore' => 0],
        ];
    }

    /**
     * @dataProvider dataAnalyze
     *
     * @param array $sequence
     * @param float $suspicionScore
     */
    public function testAnalyze($sequence, $suspicionScore)
    {
        $result = $this->sequenceAnalyzer->analyze(0, $sequence);

        $this->assertEquals($result->getSuspicionScore(), $suspicionScore);
    }
}
