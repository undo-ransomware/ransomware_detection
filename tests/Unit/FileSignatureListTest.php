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

namespace OCA\RansomwareDetection\tests\Unit;

use OCA\RansomwareDetection\FileSignatureList;
use OCA\RansomwareDetection\Analyzer\EntropyResult;
use Test\TestCase;

class FileSignatureListTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function dataIsSignatureKnown()
    {
        $tests = [];

        foreach (FileSignatureList::getSignatures() as $signature) {
            $tests[] = [$signature, true];
        }

        $tests[] = [['byteSequence' => 'aaa', 'offset' => 0, 'extension' => ['test'], 'mimeType' => [], 'file_class' => EntropyResult::COMPRESSED], false];

        return $tests;
    }

    /**
     * @dataProvider dataIsSignatureKnown
     *
     * @param array $signature
     * @param bool  $return
     */
    public function testIsSignatureKown($signature, $return)
    {
        $this->assertEquals(in_array($signature, FileSignatureList::getSignatures()), $return);
    }
}
