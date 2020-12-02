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
use OCA\RansomwareDetection\Service\DetectionService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Controller;
use OCP\IConfig;
use OCP\IUserSession;
use OCP\IRequest;

class DetectionController extends Controller
{
    /** @var DetectionService */
    protected $detectionService;

    /**
     * @param string               $appName
     * @param IRequest             $request
     * @param DetectionService     $detectionService
     */
    public function __construct(
        $appName,
        IRequest $request,
        DetectionService $detectionService
    ) {
        parent::__construct($appName, $request);

        $this->detectionService = $detectionService;
    }

    /**
     * List detections.
     *
     * @NoCSRFRequired
     * @NoAdminRequired
     *
     * @return JSONResponse
     */
    public function findAll() {
        $detections = $this->detectionService->getDetections();

        return new JSONResponse($detections, Http::STATUS_OK);
    }

    /**
     * Find detection with $id.
     *
     * @NoCSRFRequired
     * @NoAdminRequired
     *
     * @return JSONResponse
     */
    public function find($id) {
        $detection = $this->detectionService->getDetection($id);

        return new JSONResponse($detection, Http::STATUS_OK);
    }
}