<?php

/**
 * @copyright Copyright (c) 2020 Matthias Held <matthias.held@uni-konstanz.de>
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

namespace OCA\RansomwareDetection\Events;

use OCA\RansomwareDetection\Monitor;
use OCA\RansomwareDetection\AppInfo\Application;
use OCP\ILogger;

class FilesEvents {

	/** @var string */
    private $userId;
    
    /** @var ILogger */
    private $logger;

    /** @var Monitor */
    private $monitor;


	/**
     * @param ILogger   $logger
     * @param Monitor   $monitor
	 * @param string    $userId
	 */
	public function __construct(
        ILogger $logger,
        Monitor $monitor,
        $userId

	) {
        $this->logger = $logger;
        $this->monitor = $monitor;
		$this->userId = $userId;
	}

	/**
	 * @param array $params
	 */
	public function onFileUpdate(array $params) {
        $this->logger->debug("Updating ".$params['path'].": Params: ".print_r($params, true), ['app' =>  Application::APP_ID]);
        $this->analyze([$params['path']], Monitor::WRITE);
	}


	/**
	 * @param array $params
	 */
	public function onFileRename(array $params) {
        $this->logger->debug("Renaming ".$params['oldpath']." to ".$params['newpath'].": Params: ".print_r($params, true), ['app' =>  Application::APP_ID]);
        $this->analyze([$params['oldpath'], $params['newpath']], Monitor::RENAME);
    }

    /**
	 * @param array $params
	 */
    public function onFileCreate(array $params) {
        $this->logger->debug("Creating ".$params['path'].": Params: ".print_r($params, true), ['app' =>  Application::APP_ID]);
        $this->analyze([$params['path']], Monitor::CREATE);
    }
    
    /**
	 * @param array $params
	 */
    public function onFileWrite(array $params) {
        $this->logger->debug("Writing ".$params['path'].": Params: ".print_r($params, true), ['app' =>  Application::APP_ID]);
        $this->analyze([$params['path']], Monitor::WRITE);
    }
    
    /**
	 * @param array $params
	 */
    public function onFileDelete(array $params) {
        $this->logger->debug("Deleting ".$params['path'].": Params: ".print_r($params, true), ['app' =>  Application::APP_ID]);
        $this->analyze([$params['path']], Monitor::DELETE);
    }
    
    /**
	 * @param array $params
	 */
    public function onFileCopy(array $params) {
        $this->logger->debug("Copying ".$params['oldpath']." to ".$params['newpath'].": Params: ".print_r($params, true), ['app' =>  Application::APP_ID]);
        $this->analyze([$params['oldpath'], $params['newpath']], Monitor::RENAME);
    }
    
    /**
	 * @param array $params
	 */
    public function onFileTouch(array $params) {
        $this->logger->debug("Touching ".$params['path'].": Params: ".print_r($params, true), ['app' =>  Application::APP_ID]);
        $this->analyze([$params['path']], Monitor::WRITE);
    }
    
    /**
     * Makes it easier to test.
     *
     * @param IStorage $storage
     * @param string   $path
     * @param int      $mode
     */
    protected function analyze($path, $mode)
    {
        return $this->monitor->analyze($path, $mode);
    }
}