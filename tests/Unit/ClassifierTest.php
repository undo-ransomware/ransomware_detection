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

use OCA\RansomwareDetection\Monitor;
use OCA\RansomwareDetection\Analyzer\EntropyResult;
use OCA\RansomwareDetection\Analyzer\FileNameResult;
use OCA\RansomwareDetection\Classifier;
use OCA\RansomwareDetection\Db\FileOperationMapper;
use OCA\RansomwareDetection\Service\FileOperationService;
use OCA\RansomwareDetection\Db\FileOperation;
use OCP\ILogger;
use Test\TestCase;

class ClassifierTest extends TestCase
{
    /** @var ILogger|\PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    /** @var FileOperationMapper|\PHPUnit_Framework_MockObject_MockObject */
    protected $fileOperationMapper;

    /** @var FileOperationService|\PHPUnit_Framework_MockObject_MockObject */
    protected $fileOperationService;

    /** @var Classifier */
    protected $classifier;

    public function setUp()
    {
        parent::setUp();

        $this->logger = $this->createMock(ILogger::class);
        $this->fileOperationMapper = $this->createMock(FileOperationMapper::class);
        $this->fileOperationService = $this->createMock(FileOperationService::class);

        $this->classifier = new Classifier($this->logger, $this->fileOperationMapper, $this->fileOperationService);
    }

    public function dataClassifyFile()
    {
        return [
            ['command' => Monitor::WRITE, 'fileClass' => EntropyResult::ENCRYPTED, 'fileNameClass' => FileNameResult::NORMAL, 'suspicionClass' => Classifier::NOT_SUSPICIOUS],
            ['command' => Monitor::WRITE, 'fileClass' => EntropyResult::ENCRYPTED, 'fileNameClass' => FileNameResult::SUSPICIOUS_FILE_EXTENSION, 'suspicionClass' => Classifier::MIDDLE_LEVEL_OF_SUSPICION],
            ['command' => Monitor::WRITE, 'fileClass' => EntropyResult::ENCRYPTED, 'fileNameClass' => FileNameResult::SUSPICIOUS_FILE_NAME, 'suspicionClass' => Classifier::MIDDLE_LEVEL_OF_SUSPICION],
            ['command' => Monitor::WRITE, 'fileClass' => EntropyResult::ENCRYPTED, 'fileNameClass' => FileNameResult::SUSPICIOUS, 'suspicionClass' => Classifier::HIGH_LEVEL_OF_SUSPICION],
            ['command' => Monitor::WRITE, 'fileClass' => EntropyResult::COMPRESSED, 'fileNameClass' => FileNameResult::NORMAL, 'suspicionClass' => Classifier::NOT_SUSPICIOUS],
            ['command' => Monitor::WRITE, 'fileClass' => EntropyResult::COMPRESSED, 'fileNameClass' => FileNameResult::SUSPICIOUS_FILE_EXTENSION, 'suspicionClass' => Classifier::LOW_LEVEL_OF_SUSPICION],
            ['command' => Monitor::WRITE, 'fileClass' => EntropyResult::COMPRESSED, 'fileNameClass' => FileNameResult::SUSPICIOUS_FILE_NAME, 'suspicionClass' => Classifier::LOW_LEVEL_OF_SUSPICION],
            ['command' => Monitor::WRITE, 'fileClass' => EntropyResult::COMPRESSED, 'fileNameClass' => FileNameResult::SUSPICIOUS, 'suspicionClass' => Classifier::MIDDLE_LEVEL_OF_SUSPICION],
            ['command' => Monitor::WRITE, 'fileClass' => EntropyResult::NORMAL, 'fileNameClass' => FileNameResult::NORMAL, 'suspicionClass' => Classifier::NOT_SUSPICIOUS],
            ['command' => Monitor::WRITE, 'fileClass' => EntropyResult::NORMAL, 'fileNameClass' => FileNameResult::SUSPICIOUS_FILE_EXTENSION, 'suspicionClass' => Classifier::NOT_SUSPICIOUS],
            ['command' => Monitor::WRITE, 'fileClass' => EntropyResult::NORMAL, 'fileNameClass' => FileNameResult::SUSPICIOUS_FILE_NAME, 'suspicionClass' => Classifier::NOT_SUSPICIOUS],
            ['command' => Monitor::WRITE, 'fileClass' => EntropyResult::NORMAL, 'fileNameClass' => FileNameResult::SUSPICIOUS, 'suspicionClass' => Classifier::NOT_SUSPICIOUS],
            ['command' => Monitor::READ, 'fileClass' => EntropyResult::ENCRYPTED, 'fileNameClass' => FileNameResult::NORMAL, 'suspicionClass' => Classifier::NO_INFORMATION],
            ['command' => Monitor::READ, 'fileClass' => EntropyResult::ENCRYPTED, 'fileNameClass' => FileNameResult::SUSPICIOUS_FILE_EXTENSION, 'suspicionClass' => Classifier::NO_INFORMATION],
            ['command' => Monitor::READ, 'fileClass' => EntropyResult::ENCRYPTED, 'fileNameClass' => FileNameResult::SUSPICIOUS_FILE_NAME, 'suspicionClass' => Classifier::NO_INFORMATION],
            ['command' => Monitor::READ, 'fileClass' => EntropyResult::ENCRYPTED, 'fileNameClass' => FileNameResult::SUSPICIOUS, 'suspicionClass' => Classifier::NO_INFORMATION],
            ['command' => Monitor::READ, 'fileClass' => EntropyResult::COMPRESSED, 'fileNameClass' => FileNameResult::NORMAL, 'suspicionClass' => Classifier::NO_INFORMATION],
            ['command' => Monitor::READ, 'fileClass' => EntropyResult::COMPRESSED, 'fileNameClass' => FileNameResult::SUSPICIOUS_FILE_EXTENSION, 'suspicionClass' => Classifier::NO_INFORMATION],
            ['command' => Monitor::READ, 'fileClass' => EntropyResult::COMPRESSED, 'fileNameClass' => FileNameResult::SUSPICIOUS_FILE_NAME, 'suspicionClass' => Classifier::NO_INFORMATION],
            ['command' => Monitor::READ, 'fileClass' => EntropyResult::COMPRESSED, 'fileNameClass' => FileNameResult::SUSPICIOUS, 'suspicionClass' => Classifier::NO_INFORMATION],
            ['command' => Monitor::READ, 'fileClass' => EntropyResult::NORMAL, 'fileNameClass' => FileNameResult::NORMAL, 'suspicionClass' => Classifier::NO_INFORMATION],
            ['command' => Monitor::READ, 'fileClass' => EntropyResult::NORMAL, 'fileNameClass' => FileNameResult::SUSPICIOUS_FILE_EXTENSION, 'suspicionClass' => Classifier::NO_INFORMATION],
            ['command' => Monitor::READ, 'fileClass' => EntropyResult::NORMAL, 'fileNameClass' => FileNameResult::SUSPICIOUS_FILE_NAME, 'suspicionClass' => Classifier::NO_INFORMATION],
            ['command' => Monitor::READ, 'fileClass' => EntropyResult::NORMAL, 'fileNameClass' => FileNameResult::SUSPICIOUS, 'suspicionClass' => Classifier::NO_INFORMATION],
            ['command' => Monitor::DELETE, 'fileClass' => EntropyResult::ENCRYPTED, 'fileNameClass' => FileNameResult::NORMAL, 'suspicionClass' => Classifier::NOT_SUSPICIOUS],
            ['command' => Monitor::DELETE, 'fileClass' => EntropyResult::ENCRYPTED, 'fileNameClass' => FileNameResult::SUSPICIOUS_FILE_EXTENSION, 'suspicionClass' => Classifier::MIDDLE_LEVEL_OF_SUSPICION],
            ['command' => Monitor::DELETE, 'fileClass' => EntropyResult::ENCRYPTED, 'fileNameClass' => FileNameResult::SUSPICIOUS_FILE_NAME, 'suspicionClass' => Classifier::MIDDLE_LEVEL_OF_SUSPICION],
            ['command' => Monitor::DELETE, 'fileClass' => EntropyResult::ENCRYPTED, 'fileNameClass' => FileNameResult::SUSPICIOUS, 'suspicionClass' => Classifier::HIGH_LEVEL_OF_SUSPICION],
            ['command' => Monitor::DELETE, 'fileClass' => EntropyResult::COMPRESSED, 'fileNameClass' => FileNameResult::NORMAL, 'suspicionClass' => Classifier::NOT_SUSPICIOUS],
            ['command' => Monitor::DELETE, 'fileClass' => EntropyResult::COMPRESSED, 'fileNameClass' => FileNameResult::SUSPICIOUS_FILE_EXTENSION, 'suspicionClass' => Classifier::LOW_LEVEL_OF_SUSPICION],
            ['command' => Monitor::DELETE, 'fileClass' => EntropyResult::COMPRESSED, 'fileNameClass' => FileNameResult::SUSPICIOUS_FILE_NAME, 'suspicionClass' => Classifier::LOW_LEVEL_OF_SUSPICION],
            ['command' => Monitor::DELETE, 'fileClass' => EntropyResult::COMPRESSED, 'fileNameClass' => FileNameResult::SUSPICIOUS, 'suspicionClass' => Classifier::MIDDLE_LEVEL_OF_SUSPICION],
            ['command' => Monitor::DELETE, 'fileClass' => EntropyResult::NORMAL, 'fileNameClass' => FileNameResult::NORMAL, 'suspicionClass' => Classifier::NOT_SUSPICIOUS],
            ['command' => Monitor::DELETE, 'fileClass' => EntropyResult::NORMAL, 'fileNameClass' => FileNameResult::SUSPICIOUS_FILE_EXTENSION, 'suspicionClass' => Classifier::NOT_SUSPICIOUS],
            ['command' => Monitor::DELETE, 'fileClass' => EntropyResult::NORMAL, 'fileNameClass' => FileNameResult::SUSPICIOUS_FILE_NAME, 'suspicionClass' => Classifier::NOT_SUSPICIOUS],
            ['command' => Monitor::DELETE, 'fileClass' => EntropyResult::NORMAL, 'fileNameClass' => FileNameResult::SUSPICIOUS, 'suspicionClass' => Classifier::NOT_SUSPICIOUS],
            ['command' => Monitor::RENAME, 'fileClass' => EntropyResult::ENCRYPTED, 'fileNameClass' => FileNameResult::NORMAL, 'suspicionClass' => Classifier::NOT_SUSPICIOUS],
            ['command' => Monitor::RENAME, 'fileClass' => EntropyResult::ENCRYPTED, 'fileNameClass' => FileNameResult::SUSPICIOUS_FILE_EXTENSION, 'suspicionClass' => Classifier::MIDDLE_LEVEL_OF_SUSPICION],
            ['command' => Monitor::RENAME, 'fileClass' => EntropyResult::ENCRYPTED, 'fileNameClass' => FileNameResult::SUSPICIOUS_FILE_NAME, 'suspicionClass' => Classifier::MIDDLE_LEVEL_OF_SUSPICION],
            ['command' => Monitor::RENAME, 'fileClass' => EntropyResult::ENCRYPTED, 'fileNameClass' => FileNameResult::SUSPICIOUS, 'suspicionClass' => Classifier::HIGH_LEVEL_OF_SUSPICION],
            ['command' => Monitor::RENAME, 'fileClass' => EntropyResult::COMPRESSED, 'fileNameClass' => FileNameResult::NORMAL, 'suspicionClass' => Classifier::NOT_SUSPICIOUS],
            ['command' => Monitor::RENAME, 'fileClass' => EntropyResult::COMPRESSED, 'fileNameClass' => FileNameResult::SUSPICIOUS_FILE_EXTENSION, 'suspicionClass' => Classifier::LOW_LEVEL_OF_SUSPICION],
            ['command' => Monitor::RENAME, 'fileClass' => EntropyResult::COMPRESSED, 'fileNameClass' => FileNameResult::SUSPICIOUS_FILE_NAME, 'suspicionClass' => Classifier::LOW_LEVEL_OF_SUSPICION],
            ['command' => Monitor::RENAME, 'fileClass' => EntropyResult::COMPRESSED, 'fileNameClass' => FileNameResult::SUSPICIOUS, 'suspicionClass' => Classifier::MIDDLE_LEVEL_OF_SUSPICION],
            ['command' => Monitor::RENAME, 'fileClass' => EntropyResult::NORMAL, 'fileNameClass' => FileNameResult::NORMAL, 'suspicionClass' => Classifier::NOT_SUSPICIOUS],
            ['command' => Monitor::RENAME, 'fileClass' => EntropyResult::NORMAL, 'fileNameClass' => FileNameResult::SUSPICIOUS_FILE_EXTENSION, 'suspicionClass' => Classifier::NOT_SUSPICIOUS],
            ['command' => Monitor::RENAME, 'fileClass' => EntropyResult::NORMAL, 'fileNameClass' => FileNameResult::SUSPICIOUS_FILE_NAME, 'suspicionClass' => Classifier::NOT_SUSPICIOUS],
            ['command' => Monitor::RENAME, 'fileClass' => EntropyResult::NORMAL, 'fileNameClass' => FileNameResult::SUSPICIOUS, 'suspicionClass' => Classifier::NOT_SUSPICIOUS],
        ];
    }

    /**
     * @dataProvider dataClassifyFile
     *
     * @param int $command
     * @param int $fileClass
     * @param int $fileNameClass
     * @param int $suspicionClass
     */
    public function testClassifyFile($command, $fileClass, $fileNameClass, $suspicionClass)
    {
        $fileOperation = new FileOperation();
        $fileOperation->setCommand($command);
        $fileOperation->setFileClass($fileClass);
        $fileOperation->setFileNameClass($fileNameClass);

        $result = $this->classifier->classifyFile($fileOperation);
        $this->assertEquals($result->getSuspicionClass(), $suspicionClass);
    }
}
