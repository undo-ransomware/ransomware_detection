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

namespace OCA\RansomwareDetection\tests\Unit\BackgroundJob;

use OCA\RansomwareDetection\BackgroundJob\CleanUpJob;
use OCA\RansomwareDetection\Db\FileOperationMapper;
use OCA\RansomwareDetection\Service\FileOperationService;
use OCA\RansomwareDetection\Tests\Unit\Db\MapperTestUtility;
use OCP\IConfig;

class CleanUpJobTest extends MapperTestUtility
{
    /** @var FileOperationService|\PHPUnit_Framework_MockObject_MockObject */
    protected $fileOperationService;

    /** @var CleanUpJob */
    protected $cleanUpJob;

    /** @var IConfige|\PHPUnit_Framework_MockObject_MockObject */
    protected $config;

    public function setUp()
    {
        parent::setUp();

        $fileOperationMapper = $this->getMockBuilder(FileOperationMapper::class)
            ->setMethods(['deleteFileOperationsBefore'])
            ->setConstructorArgs([$this->db])
            ->getMock();
        $this->fileOperationService = $this->getMockBuilder(FileOperationService::class)
            ->setConstructorArgs([$fileOperationMapper, 'john'])
            ->getMock();
        $this->config = $this->createMock(IConfig::class);
        $this->cleanUpJob = new CleanUpJob($this->fileOperationService, $this->config);
    }

    /**
     * Run test.
     */
    public function testRun()
    {
        $backgroundJob = new CleanUpJob($this->fileOperationService, $this->config);
        $jobList = $this->getMockBuilder('\OCP\BackgroundJob\IJobList')->getMock();
        /* @var \OC\BackgroundJob\JobList $jobList */
        $backgroundJob->execute($jobList);
        $this->assertTrue(true);
    }
}
