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

namespace OCA\RansomwareDetection;

use OCA\RansomwareDetection\AppInfo\Application;
use OCA\RansomwareDetection\Events\FilesEvents;

class FilesHooks {

	/**
	 * Retrieve the FilesEvents' Controller.
	 *
	 * @return FilesEvents
	 */
	protected static function getController(): FilesEvents {
		$app = new Application();

		return $app->getContainer()
				   ->query(FilesEvents::class);
	}

	/**
	 * Hook events: file is updated.
	 *
	 * @param array $params
	 */
	public static function onFileUpdate(array $params) {
		self::getController()
			->onFileUpdate($params);
	}


	/**
	 * Hook events: file is renamed.
	 *
	 * @param array $params
	 */
	public static function onFileRename(array $params) {
		self::getController()
			->onFileRename($params);
    }
	
	/**
	 * Hook events: file is created.
	 *
	 * @param array $params
	 */
    public static function onFileCreate(array $params) {
		self::getController()
			->onFileCreate($params);
    }
	
	/**
	 * Hook events: file is written.
	 *
	 * @param array $params
	 */
    public static function onFileWrite(array $params) {
		self::getController()
			->onFileWrite($params);
    }
	
	/**
	 * Hook events: file is deleted.
	 *
	 * @param array $params
	 */
    public static function onFileDelete(array $params) {
		self::getController()
			->onFileDelete($params);
    }
	
	/**
	 * Hook events: file is touched.
	 *
	 * @param array $params
	 */
    public static function onFileTouch(array $params) {
		self::getController()
			->onFileTouch($params);
    }
	
	/**
	 * Hook events: file is copied.
	 *
	 * @param array $params
	 */
    public static function onFileCopy(array $params) {
		self::getController()
			->onFileCopy($params);
	}
}