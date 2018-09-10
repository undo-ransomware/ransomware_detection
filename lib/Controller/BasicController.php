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

namespace OCA\RansomwareDetection\Controller;

use OCA\RansomwareDetection\AppInfo\Application;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\OCSController;
use OCP\IConfig;
use OCP\IUserSession;
use OCP\IRequest;

class BasicController extends OCSController
{
    /** @var IConfig */
    protected $config;

    /** @var IUserSession */
    protected $userSession;

    /** @var int */
    private $userId;

    /**
     * @param string               $appName
     * @param IRequest             $request
     * @param IUserSession         $userSession
     * @param IConfig              $config
     * @param string               $userId
     */
    public function __construct(
        $appName,
        IRequest $request,
        IUserSession $userSession,
        IConfig $config,
        $userId
    ) {
        parent::__construct($appName, $request);

        $this->config = $config;
        $this->userSession = $userSession;
        $this->userId = $userId;
    }

    /**
     * Get debug mode.
     *
     * @NoAdminRequired
     *
     * @return JSONResponse
     */
    public function getDebugMode()
    {
        $debugMode = $this->config->getAppValue(Application::APP_ID, 'debug', 0);

        return new JSONResponse(['status' => 'success', 'message' => 'Get debug mode.', 'debugMode' => $debugMode], Http::STATUS_ACCEPTED);
    }

    /**
     * Get color mode.
     *
     * @NoAdminRequired
     *
     * @return JSONResponse
     */
    public function getColorMode()
    {
        $colorMode = $this->config->getUserValue($this->userId, Application::APP_ID, 'color_mode', 0);

        return new JSONResponse(['status' => 'success', 'message' => 'Get color mode.', 'colorMode' => $colorMode], Http::STATUS_ACCEPTED);
    }

    /**
     * Changes color mode.
     *
     * @NoAdminRequired
     *
     * @param int $colorMode
     *
     * @return JSONResponse
     */
    public function changeColorMode($colorMode)
    {
        $this->config->setUserValue($this->userId, Application::APP_ID, 'color_mode', $colorMode);

        return new JSONResponse(['status' => 'success', 'message' => 'Color mode changed.'], Http::STATUS_ACCEPTED);
    }
}
