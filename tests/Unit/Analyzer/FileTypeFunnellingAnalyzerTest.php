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
use OCA\RansomwareDetection\Analyzer\FileTypeFunnellingAnalyzer;
use OCA\RansomwareDetection\Analyzer\FileExtensionResult;
use OCA\RansomwareDetection\Db\FileOperation;
use Test\TestCase;

class FileTypeFunnellingAnalyzerTest extends TestCase
{
    /** @var FileTypeFunnellingAnalyzer */
    protected $fileTypeFunnellingAnalyzer;

    public function setUp(): void
    {
        parent::setUp();

        $this->fileTypeFunnellingAnalyzer = new FileTypeFunnellingAnalyzer();
    }

    public function dataAnalyze()
    {
        $fileOperation1 = new FileOperation();
        $fileOperation1->setCommand(Monitor::WRITE);
        $fileOperation1->setOriginalName('file.unknown');
        $fileOperation1->setFileExtensionClass(FileExtensionResult::SUSPICIOUS);
        $fileOperation1->setCorrupted(false);
        $fileOperation1->setType('file');

        $fileOperation11 = new FileOperation();
        $fileOperation11->setCommand(Monitor::WRITE);
        $fileOperation11->setOriginalName('file.unknown1');
        $fileOperation11->setFileExtensionClass(FileExtensionResult::SUSPICIOUS);
        $fileOperation11->setCorrupted(false);
        $fileOperation11->setType('file');

        $fileOperation12 = new FileOperation();
        $fileOperation12->setCommand(Monitor::WRITE);
        $fileOperation12->setOriginalName('file.unknown2');
        $fileOperation12->setFileExtensionClass(FileExtensionResult::SUSPICIOUS);
        $fileOperation12->setCorrupted(false);
        $fileOperation12->setType('file');

        $fileOperation13 = new FileOperation();
        $fileOperation13->setCommand(Monitor::WRITE);
        $fileOperation13->setOriginalName('file.unknown3');
        $fileOperation13->setFileExtensionClass(FileExtensionResult::SUSPICIOUS);
        $fileOperation13->setCorrupted(false);
        $fileOperation13->setType('file');

        $fileOperation14 = new FileOperation();
        $fileOperation14->setCommand(Monitor::WRITE);
        $fileOperation14->setOriginalName('file.unknown4');
        $fileOperation14->setFileExtensionClass(FileExtensionResult::SUSPICIOUS);
        $fileOperation14->setCorrupted(false);
        $fileOperation14->setType('file');

        $fileOperation15 = new FileOperation();
        $fileOperation15->setCommand(Monitor::WRITE);
        $fileOperation15->setOriginalName('file.unknown5');
        $fileOperation15->setFileExtensionClass(FileExtensionResult::SUSPICIOUS);
        $fileOperation15->setCorrupted(false);
        $fileOperation15->setType('file');

        $fileOperation16 = new FileOperation();
        $fileOperation16->setCommand(Monitor::WRITE);
        $fileOperation16->setOriginalName('file.unknown6');
        $fileOperation16->setFileExtensionClass(FileExtensionResult::SUSPICIOUS);
        $fileOperation16->setCorrupted(false);
        $fileOperation16->setType('file');

        $fileOperation2 = new FileOperation();
        $fileOperation2->setCommand(Monitor::WRITE);
        $fileOperation2->setOriginalName('file.csv');
        $fileOperation2->setFileExtensionClass(FileExtensionResult::NOT_SUSPICIOUS);
        $fileOperation2->setCorrupted(false);
        $fileOperation2->setType('file');

        $fileOperation3 = new FileOperation();
        $fileOperation3->setCommand(Monitor::WRITE);
        $fileOperation3->setOriginalName('file.csv');
        $fileOperation3->setFileExtensionClass(FileExtensionResult::NOT_SUSPICIOUS);
        $fileOperation3->setCorrupted(true);
        $fileOperation3->setType('file');

        $fileOperation4 = new FileOperation();
        $fileOperation4->setCommand(Monitor::RENAME);
        $fileOperation4->setOriginalName('file.csv');
        $fileOperation4->setFileExtensionClass(FileExtensionResult::NOT_SUSPICIOUS);
        $fileOperation4->setCorrupted(true);
        $fileOperation4->setType('file');

        $fileOperation5 = new FileOperation();
        $fileOperation5->setCommand(Monitor::DELETE);
        $fileOperation5->setOriginalName('file.csv');
        $fileOperation5->setFileExtensionClass(FileExtensionResult::NOT_SUSPICIOUS);
        $fileOperation5->setCorrupted(true);
        $fileOperation5->setType('file');

        $fileOperation6 = new FileOperation();
        $fileOperation6->setCommand(100);
        $fileOperation6->setOriginalName('file.csv');
        $fileOperation6->setFileExtensionClass(FileExtensionResult::NOT_SUSPICIOUS);
        $fileOperation6->setCorrupted(true);
        $fileOperation6->setType('file');

        $fileOperation7 = new FileOperation();
        $fileOperation7->setCommand(Monitor::READ);
        $fileOperation7->setOriginalName('file.unknown');
        $fileOperation7->setFileExtensionClass(FileExtensionResult::SUSPICIOUS);
        $fileOperation7->setCorrupted(false);
        $fileOperation7->setType('file');

		$fileOperation8 = new FileOperation();
        $fileOperation8->setCommand(Monitor::CREATE);
        $fileOperation8->setOriginalName('file.unknown');
        $fileOperation8->setFileExtensionClass(FileExtensionResult::SUSPICIOUS);
        $fileOperation8->setCorrupted(false);
        $fileOperation8->setType('file');
        // not a sequence
        $sequence1 = [$fileOperation1];
        $sequence2 = [$fileOperation1, $fileOperation1];
        // a sequence
        $sequence3 = [$fileOperation1, $fileOperation1, $fileOperation1];
        $sequence4 = [$fileOperation1, $fileOperation1, $fileOperation1, $fileOperation1];
        $sequence5 = [$fileOperation1, $fileOperation1, $fileOperation1, $fileOperation1, $fileOperation1];
        // written files which have all the same unknown file extensions => file type funneling
        $sequence6 = [$fileOperation1, $fileOperation1, $fileOperation1, $fileOperation1, $fileOperation1, $fileOperation1];
        // written files which have a distinct unknown file extensions => file type funneling
        $sequence7 = [$fileOperation11, $fileOperation12, $fileOperation13, $fileOperation14, $fileOperation15, $fileOperation16];
        // written files have a unknown file extensions => file type funneling
        $sequence8 = [$fileOperation1, $fileOperation1, $fileOperation1, $fileOperation1, $fileOperation12, $fileOperation13];
        // all written files have known extensions
        $sequence9 = [$fileOperation2, $fileOperation2, $fileOperation2, $fileOperation2, $fileOperation2, $fileOperation2];
        // Only delete and rename => no file type funneling
        $sequence10 = [$fileOperation4, $fileOperation4, $fileOperation5, $fileOperation5, $fileOperation4, $fileOperation5];
        // unkown command => no file type funneling
        $sequence11 = [$fileOperation6, $fileOperation6, $fileOperation6, $fileOperation6, $fileOperation6, $fileOperation6];
        // some files are known
        $sequence12 = [$fileOperation1, $fileOperation2, $fileOperation1, $fileOperation1, $fileOperation1, $fileOperation2, $fileOperation1];
        // all written files have known extensions but are corrupted
        $sequence13 = [$fileOperation3, $fileOperation3, $fileOperation3, $fileOperation3, $fileOperation3, $fileOperation3, $fileOperation3];
        // only read access
        $sequence14 = [$fileOperation7, $fileOperation7, $fileOperation7, $fileOperation7, $fileOperation7, $fileOperation7, $fileOperation7, $fileOperation8];

        return [
            ['sequence' => [], 'fileTypeFunnelingClass' => 0],
            ['sequence' => $sequence1, 'fileTypeFunnelingClass' => 0],
            ['sequence' => $sequence2, 'fileTypeFunnelingClass' => 0],
            ['sequence' => $sequence3, 'fileTypeFunnelingClass' => 2],
            ['sequence' => $sequence4, 'fileTypeFunnelingClass' => 2],
            ['sequence' => $sequence5, 'fileTypeFunnelingClass' => 2],
            ['sequence' => $sequence6, 'fileTypeFunnelingClass' => 2],
            ['sequence' => $sequence7, 'fileTypeFunnelingClass' => 2],
            ['sequence' => $sequence8, 'fileTypeFunnelingClass' => 1],
            ['sequence' => $sequence9, 'fileTypeFunnelingClass' => 0],
            ['sequence' => $sequence10, 'fileTypeFunnelingClass' => 0],
            ['sequence' => $sequence11, 'fileTypeFunnelingClass' => 0],
            ['sequence' => $sequence12, 'fileTypeFunnelingClass' => 0],
            ['sequence' => $sequence13, 'fileTypeFunnelingClass' => 2],
            ['sequence' => $sequence14, 'fileTypeFunnelingClass' => 0],
        ];
    }

    /**
     * @dataProvider dataAnalyze
     *
     * @param array $sequence
     * @param int   $fileTypeFunnelingClass
     */
    public function testAnalyze($sequence, $fileTypeFunnelingClass)
    {
        $result = $this->fileTypeFunnellingAnalyzer->analyze($sequence);

        $this->assertEquals($result, $fileTypeFunnelingClass);
    }
}
