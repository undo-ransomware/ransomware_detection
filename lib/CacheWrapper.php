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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OCA\RansomwareDetection;

use OC\Files\Cache\Wrapper\CacheWrapper as Wrapper;
use OCP\Constants;
use OCP\Files\Cache\ICache;
use OCP\Files\Storage\IStorage;

class CacheWrapper extends Wrapper
{
    /** @var Monitor */
    protected $monitor;

    /** @var StorageWrapper */
    protected $storage;

    /** @var int */
    protected $mask;

    /**
     * @param ICache   $cache
     * @param IStorage $storage
     * @param Monitor  $monitor
     */
    public function __construct(
        ICache $cache,
        IStorage $storage,
        Monitor $monitor
    ) {
        parent::__construct($cache);
        $this->storage = $storage;
        $this->monitor = $monitor;
        $this->mask = Constants::PERMISSION_ALL;
        $this->mask &= ~Constants::PERMISSION_READ;
        $this->mask &= ~Constants::PERMISSION_CREATE;
        $this->mask &= ~Constants::PERMISSION_UPDATE;
    }

    protected function formatCacheEntry($entry)
    {
        if (isset($entry['path'])) {
            $this->monitor->analyze($this->storage, [$entry['path']], Monitor::READ);
        }

        return $entry;
    }
}
