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
use OCP\Files\IRootFolder;
use OCP\Files\Node;
use OCP\ILogger;

class FilesEvents {

	/** @var string */
    private $userId;
    
    /** @var ILogger */
    private $logger;

    /** @var IRootFolder */
    private $rootFolder;

    /** @var Monitor */
    private $monitor;


	/**
     * @param ILogger   $logger
     * @param Monitor   $monitor
	 * @param string    $userId
	 */
	public function __construct(
        ILogger $logger,
        IRootFolder $rootFolder, 
        Monitor $monitor,
        $userId

	) {
        $this->logger = $logger;
        $this->rootFolder = $rootFolder;
        $this->monitor = $monitor;
		$this->userId = $userId;
    }
    
    public function register() {
        $this->rootFolder->listen('\OC\Files', 'postWrite', [$this, 'onFileWrite']);
        $this->rootFolder->listen('\OC\Files', 'postRename', [$this, 'onFileRename']);
        $this->rootFolder->listen('\OC\Files', 'preDelete', [$this, 'onFileDelete']);
        $this->rootFolder->listen('\OC\Files', 'postCreate ', [$this, 'onFileCreate']);
	}


	/**
	 * @param array $params
	 */
	public function onFileRename(Node $source, Node $target) {
        $this->logger->debug("Renaming ".$source->getPath()." to ".$target->getPath(), ['app' =>  Application::APP_ID]);
        $this->analyze($source, $target, Monitor::RENAME);
    }

    /**
	 * @param array $params
	 */
    public function onFileCreate(Node $node) {
        $this->logger->debug("Creating ".$node->getPath(), ['app' =>  Application::APP_ID]);
        $this->analyze($node, null, Monitor::CREATE);
    }
    
    /**
	 * @param array $params
	 */
    public function onFileWrite(Node $node) {
        $this->logger->debug("Writing ".$node->getPath(), ['app' =>  Application::APP_ID]);
        $this->analyze($node, null, Monitor::WRITE);
    }
    
    /**
	 * @param array $params
	 */
    public function onFileDelete(Node $node) {
        $this->logger->debug("Deleting ".$node->getPath(), ['app' =>  Application::APP_ID]);
        $this->analyze($node, null, Monitor::DELETE);
    }
    
    /**
     * Makes it easier to test.
     *
     * @param Node     $source
     * @param Node     $target
     * @param int      $mode
     */
    protected function analyze($source, $target, $mode)
    {
        return $this->monitor->analyze($source, $target, $mode);
    }
}