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

namespace OCA\RansomwareDetection\Tests\Integration\Fixtures;

use OCA\RansomwareDetection\Db\FileOperation;

class FileOperationFixture extends FileOperation
{
    use Fixture;

    public function __construct(array $defaults = [])
    {
        parent::__construct();
        $defaults = array_merge([
            'userId' => 'john',
            'path' => 'files/',
            'originalName' => 'cat.gif',
            'newName' => 'cat.gif',
            'type' => 'file',
            'mimeType' => 'image/gif',
            'size' => 148000,
            'corrupted' => true,
            'timestamp' => date_timestamp_get(date_create()),
            'command' => 2,
            'sequence' => 1,
            'entropy' => 7.9123595,
            'standardDeviation' => 0.04,
            'fileNameEntropy' => 4.1,
            'fileClass' => 2,
            'fileNameClass' => 3,
        ], $defaults);
        $this->fillDefaults($defaults);
    }
}
