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

namespace OCA\RansomwareDetection\BackgroundJob;

use OCA\RansomwareDetection\Service\FileOperationService;
use OC\BackgroundJob\TimedJob;
use OCP\IConfig;

class CleanUpJob extends TimedJob
{
    /** @var IConfig */
    protected $config;

    /** @var FileOperationService */
    protected $fileOperationService;

    /**
     * @param FileOperationService $fileOperationService
     * @param IConfig              $config
     */
    public function __construct(
        FileOperationService $fileOperationService,
        IConfig $config
    ) {
        // Run once a day
        $this->setInterval(24 * 60 * 60);
        $this->fileOperationService = $fileOperationService;
        $this->config = $config;
    }

    public function run($argument)
    {
        $expireDays = $this->config->getAppValue('ransomware_detection', 'expire_days', 7);
        $this->fileOperationService->deleteFileOperationsBefore(strtotime('-'.$expireDays.' day'));
    }
}
