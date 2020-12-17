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

use OCA\RansomwareDetection\Monitor;
use OCA\RansomwareDetection\Classifier;
use OCA\RansomwareDetection\AppInfo\Application;
use OCA\RansomwareDetection\Db\FileOperation;
use OCA\RansomwareDetection\Service\FileOperationService;
use OCA\Files_Trashbin\Trashbin;
use OCA\Files_Trashbin\Helper;
use OCA\Files_Trashbin\Trash\ITrashManager;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Controller;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\IConfig;
use OCP\IUserSession;
use OCP\IRequest;
use OCP\ILogger;
use OCP\IUserManager;

class FileOperationController extends Controller
{
    /** @var IConfig */
    protected $config;

    /** @var IUserSession */
    protected $userSession;

    /** @var ILogger */
    protected $logger;

    /** @var Folder */
    protected $userFolder;

    /** @var FileOperationService */
    protected $service;

    /** @var Classifier */
    protected $classifier;

    /** @var ITrashManager */
    protected $trashManager;

    /** @var IUserManager */
    protected $userManager;

    /** @var string */
    protected $userId;

    /**
     * @param string               $appName
     * @param IRequest             $request
     * @param IUserSession         $userSession
     * @param IConfig              $config
     * @param ILogger              $logger
     * @param Folder               $userFolder
     * @param FileOperationService $service
     * @param Classifier           $classifier
     * @param ITrashManager        $trashManager
     * @param IUserManager         $userManager
     * @param string               $userId
     */
    public function __construct(
        $appName,
        IRequest $request,
        IUserSession $userSession,
        IConfig $config,
        ILogger $logger,
        Folder $userFolder,
        FileOperationService $service,
        Classifier $classifier,
        ITrashManager $trashManager,
        IUserManager $userManager,
        $userId
    ) {
        parent::__construct($appName, $request);

        $this->config = $config;
        $this->userSession = $userSession;
        $this->userFolder = $userFolder;
        $this->logger = $logger;
        $this->service = $service;
        $this->classifier = $classifier;
        $this->trashManager = $trashManager;
        $this->userManager = $userManager;
        $this->userId = $userId;
    }

    /**
     * Lists the files.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function findAll()
    {
        $files = $this->service->findAll();

        foreach ($files as $file) {
            $this->classifier->classifyFile($file);
        }

        return new JSONResponse($files, Http::STATUS_OK);
    }

    /**
     * Find file with id.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @return JSONResponse
     */
    public function find($id)
    {
        $file = $this->service->find($id);

        $this->classifier->classifyFile($file);

        return new JSONResponse($file, Http::STATUS_OK);
    }

    /**
     * Recover files from trashbin or remove them from normal storage.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param array $ids file operation id
     *
     * @return JSONResponse
     */
    public function recover($ids)
    {
        $deleted = 0;
        $recovered = 0;
        $filesRecovered = array();
        $error = false;
        $badRequest = false;

        foreach ($ids as $id) {
            try {
                $file = $this->service->find($id);
                if (is_null($file->getPath()) || $file->getId() === $userFolder->getId() || is_null($file->getOriginalName())) {
                    $this->logger->warning('recover: File path or name is null or user folder.', array('app' => Application::APP_ID));
                    return;
                }
                switch ($file->getCommand()) {
                    case Monitor::WRITE:
                        if ($this->deleteFromStorage($file->getFileId())) {
                            $this->service->deleteById($id, true);

                            $deleted++;
                            array_push($filesRecovered, $id);
                        } else {
                            // File cannot be deleted
                            $error = true;
                        }
                        break;
                    case Monitor::DELETE:
                        // Recover deleted files by restoring them from the trashbin
                        // It's not necessary to use the real path
                        $trashItem = $this->trashManager->getTrashNodeById($this->userManager->get($this->userId), $file->getFileId());
                        $name = substr($trashItem->getName(), 0, strrpos($trashItem->getName(), "."));
                        if (strpos($trashItem->getInternalPath(), "files_trashbin/files/") !== false) {
                            $path = str_replace("files_trashbin/files/", "", $trashItem->getInternalPath());
                            $time = str_replace($name.".d", "", $path);
                            if (Trashbin::restore($path, $name, $time) !== false) {
                                $this->service->deleteById($id, true);

                                $recovered++;
                                array_push($filesRecovered, $id);
                            }
                            // File does not exist
                            $badRequest = false;
                        } else {
                            $this->logger->warning('recover: File or folder is not located in the trashbin.', array('app' => Application::APP_ID));
                            return;
                        }
                        break;
                    case Monitor::RENAME:
                        $this->service->deleteById($id, true);

                        $deleted++;
                        array_push($filesRecovered, $id);
                        break;
                    case Monitor::CREATE:
                        // Recover new created files/folders
                        if ($this->deleteFromStorage($file->getFileId())) {
                            $this->service->deleteById($id, true);

                            $deleted++;
                            array_push($filesRecovered, $id);
                        } else {
                            // File cannot be deleted
                            $error = true;
                        }
                        break;
                    default:
                        // All other commands need no recovery
                        $this->service->deleteById($id, false);

                        $deleted++;
                        array_push($filesRecovered, $id);
                        break;
                    }
            } catch (\OCP\AppFramework\Db\MultipleObjectsReturnedException $exception) {
                // Found more than one with the same file name
                $this->logger->debug('recover: Found more than one with the same file name.', array('app' => Application::APP_ID));

                $badRequest = false;
            } catch (\OCP\AppFramework\Db\DoesNotExistException $exception) {
                // Nothing found
                $this->logger->debug('recover: Files does not exist.', array('app' => Application::APP_ID));

                $badRequest = false;
            }
        }
        if ($error) {
            return new JSONResponse(array('recovered' => $recovered, 'deleted' => $deleted, 'filesRecovered' => $filesRecovered), Http::STATUS_INTERNAL_SERVER_ERROR);
        }
        if ($badRequest) {
            return new JSONResponse(array('recovered' => $recovered, 'deleted' => $deleted, 'filesRecovered' => $filesRecovered), Http::STATUS_BAD_REQUEST);
        }
        return new JSONResponse(array('recovered' => $recovered, 'deleted' => $deleted, 'filesRecovered' => $filesRecovered), Http::STATUS_OK);
    }

    /**
     * Deletes a file from the storage.
     *
     * @param string $id
     *
     * @return bool
     */
    private function deleteFromStorage($id)
    {
        try {
            $nodes = $this->userFolder->getById($id);
            if (sizeof($nodes) > 1) {
                return false;
            }
            $node = array_pop($nodes);
            if ($node->isDeletable()) {
                $node->delete();
            } else {
                return false;
            }

            return true;
        } catch (\OCP\Files\NotFoundException $exception) {
            // Nothing found
            $this->logger->debug('deleteFromStorage: Not found exception.', array('app' => Application::APP_ID));

            return true;
        }
    }

}