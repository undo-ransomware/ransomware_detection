<?php
/**
 * @copyright Copyright (c) 2019 Matthias Held <matthias.held@uni-konstanz.de>
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

namespace OCA\RansomwareDetection\Service;

use OCA\RansomwareDetection\AppInfo\Application;
use OCA\RansomwareDetection\RequestTemplate;
use OCA\RansomwareDetection\Model\Service;
use OCA\RansomwareDetection\Model\ServiceStatus;
use OCP\IConfig;

class ServiceWatcher implements IServiceWatcher {

    protected $services = array();

    /** @var IConfig */
    protected $config;

    public function __construct(
        IConfig $config
    ) {
        $this->config = $config;
        array_push($this->services, $this->getDetectionService());
        array_push($this->services, $this->getMonitorService());
    }

    public function getServices() {
        return $this->services;
    }

    public function getService($id) {
        return $this->services[$id];
    }

    private function getDetectionService() {
        $requestTemplate = new RequestTemplate();
        $serviceUri = $this->config->getAppValue(Application::APP_ID, 'service_uri', 'http://localhost:8080/api');
        $result = $requestTemplate->get($serviceUri . "/status");
        if ($result === false) {
            return new Service("Detection Service", ServiceStatus::OFFLINE);
        } else {
            //TODO: use json object and check for status info and not only if some thing is returned.
            return new Service("Detection Service", ServiceStatus::ONLINE);
        }
    }

    private function getMonitorService() {
        return new Service("Monitor Service", ServiceStatus::ONLINE);
    }
}