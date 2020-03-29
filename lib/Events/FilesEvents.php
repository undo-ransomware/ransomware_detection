<?php
declare(strict_types=1);


/**
 * Files_FullTextSearch - Index the content of your files
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Maxence Lange <maxence@artificial-owl.com>
 * @copyright 2018
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */


namespace OCA\RansomwareDetection\Events;

use OCA\RansomwareDetection\Monitor;
use OCA\RansomwareDetection\AppInfo\Application;
use OCP\ILogger;

/**
 * Class FilesEvents
 *
 * @package OCA\Files_FullTextSearch\Events
 */
class FilesEvents {

	/** @var string */
    private $userId;
    
    private $logger;

    private $monitor;


	/**
	 * FilesEvents constructor.
	 *
	 * @param string $userId
	 */
	public function __construct(
        ILogger $logger,
        $monitor,
        $userId

	) {
        $this->logger = $logger;
        $this->monitor = $monitor;
		$this->userId = $userId;
	}

	/**
	 * @param array $params
	 *
	 * @throws InvalidPathException
	 * @throws NotFoundException
	 */
	public function onFileUpdate(array $params) {
        $this->analyze([$params['path']], Monitor::WRITE);
		$this->logger->error("Updating ".$params['path'], ['app' =>  Application::APP_ID]);
	}


	/**
	 * @param array $params
	 *
	 * @throws NotFoundException
	 * @throws InvalidPathException
	 */
	public function onFileRename(array $params) {
        $this->logger->error("Renaming ".$params['oldpath']." to ".$params['newpath'], ['app' =>  Application::APP_ID]);
        $this->analyze([$params['oldpath'], $params['newpath']], Monitor::RENAME);
    }

    public function onFileCreate(array $params) {
		$this->logger->error("Creating ".$params['path'], ['app' =>  Application::APP_ID]);
    }
    
    public function onFileWrite(array $params) {
		$this->logger->error("Writing ".$params['path'], ['app' =>  Application::APP_ID]);
    }
    
    public function onFileDelete(array $params) {
		$this->logger->error("Deleting ".$params['path'], ['app' =>  Application::APP_ID]);
    }
    
    public function onFileCopy(array $params) {
		$this->logger->error("Copying ".$params['path'], ['app' =>  Application::APP_ID]);
    }
    
    public function onFileTouch(array $params) {
		$this->logger->error("Touching ".$params['path'], ['app' =>  Application::APP_ID]);
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