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
 * but WITHOUT ANY WARRANTY; without even the implied warranty
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace OCA\RansomwareDetection;

use OC\Files\Storage\Wrapper\Wrapper;
use OCP\Files\Storage\IStorage;

class StorageWrapper extends Wrapper
{
    /** @var Monitor */
    protected $monitor;

    /** @var string */
    public $mountPoint;

    /**
     * @param array $parameters
     */
    public function __construct(
        $parameters
    ) {
        parent::__construct($parameters);
        $this->monitor = $parameters['monitor'];
        $this->mountPoint = $parameters['mountPoint'];
    }

    /**
     * Makes it easier to test.
     *
     * @param IStorage $storage
     * @param string   $path
     * @param int      $mode
     */
    protected function analyze(IStorage $storage, $path, $mode)
    {
        return $this->monitor->analyze($storage, $path, $mode);
    }

    /**
     * see http://php.net/manual/en/function.mkdir.php.
     *
     * @param string $path
     *
     * @return bool
     */
    public function mkdir($path)
    {
        $this->analyze($this, [$path], Monitor::CREATE);

        return $this->storage->mkdir($path);
    }

    /**
     * see http://php.net/manual/en/function.rmdir.php.
     *
     * @param string $path
     *
     * @return bool
     */
    public function rmdir($path)
    {
        $this->analyze($this, [$path], Monitor::DELETE);

        return $this->storage->rmdir($path);
    }

    /**
     * see http://php.net/manual/en/function.file_get_contents.php.
     *
     * @param string $path
     *
     * @return string
     */
    public function file_get_contents($path)
    {
        $this->analyze($this, [$path], Monitor::READ);

        return $this->storage->file_get_contents($path);
    }

    /**
     * see http://php.net/manual/en/function.file_put_contents.php.
     *
     * @param string $path
     * @param string $data
     *
     * @return bool
     */
    public function file_put_contents($path, $data)
    {
        $this->analyze($this, [$path], Monitor::WRITE);

        return $this->storage->file_put_contents($path, $data);
    }

    /**
     * see http://php.net/manual/en/function.unlink.php.
     *
     * @param string $path
     *
     * @return bool
     */
    public function unlink($path)
    {
        $this->analyze($this, [$path], Monitor::DELETE);

        return $this->storage->unlink($path);
    }

    /**
     * see http://php.net/manual/en/function.rename.php.
     *
     * @param string $path1
     * @param string $path2
     *
     * @return bool
     */
    public function rename($path1, $path2)
    {
        $this->analyze($this, [$path1, $path2], Monitor::RENAME);

        return $this->storage->rename($path1, $path2);
    }

    /**
     * see http://php.net/manual/en/function.copy.php.
     *
     * @param string $path1
     * @param string $path2
     *
     * @return bool
     */
    public function copy($path1, $path2)
    {
        $this->analyze($this, [$path1, $path2], Monitor::WRITE);

        return $this->storage->copy($path1, $path2);
    }

    /**
     * see http://php.net/manual/en/function.fopen.php.
     *
     * @param string $path
     * @param string $mode
     *
     * @return resource
     */
    public function fopen($path, $mode)
    {
        $fileMode = Monitor::READ;
        switch ($mode) {
            case 'r+':
            case 'rb+':
            case 'w+':
            case 'wb+':
            case 'x+':
            case 'xb+':
            case 'a+':
            case 'ab+':
            case 'w':
            case 'wb':
            case 'x':
            case 'xb':
            case 'a':
            case 'ab':
            $fileMode = Monitor::WRITE;
        }
        $this->analyze($this, [$path], $fileMode);

        return $this->storage->fopen($path, $mode);
    }

    /**
     * see http://php.net/manual/en/function.touch.php
     * If the backend does not support the operation, false should be returned.
     *
     * @param string $path
     * @param int    $mtime
     *
     * @return bool
     */
    public function touch($path, $mtime = null)
    {
        $this->analyze($this, [$path], Monitor::WRITE);

        return $this->storage->touch($path, $mtime);
    }

    /**
     * get a cache instance for the storage.
     *
     * @param string $path
     * @param \OC\Files\Storage\Storage (optional) the storage to pass to the cache
     *
     * @return \OC\Files\Cache\Cache
     */
    public function getCache($path = '', $storage = null)
    {
        if (!$storage) {
            $storage = $this;
        }
        $cache = $this->storage->getCache($path, $storage);

        return new CacheWrapper($cache, $storage, $this->monitor);
    }

    /**
     * @param \OCP\Files\Storage $sourceStorage
     * @param string             $sourceInternalPath
     * @param string             $targetInternalPath
     *
     * @return bool
     */
    public function copyFromStorage(IStorage $sourceStorage, $sourceInternalPath, $targetInternalPath)
    {
        if ($sourceStorage === $this) {
            return $this->copy($sourceInternalPath, $targetInternalPath);
        }
        $this->analyze($this, [$targetInternalPath], Monitor::WRITE);

        return $this->storage->copyFromStorage($sourceStorage, $sourceInternalPath, $targetInternalPath);
    }

    /**
     * @param \OCP\Files\Storage $sourceStorage
     * @param string             $sourceInternalPath
     * @param string             $targetInternalPath
     *
     * @return bool
     */
    public function moveFromStorage(IStorage $sourceStorage, $sourceInternalPath, $targetInternalPath)
    {
        if ($sourceStorage === $this) {
            return $this->rename($sourceInternalPath, $targetInternalPath);
        }
        $this->analyze($this, [$targetInternalPath], Monitor::WRITE);

        return $this->storage->moveFromStorage($sourceStorage, $sourceInternalPath, $targetInternalPath);
    }
}
