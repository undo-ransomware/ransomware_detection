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
use OCA\RansomwareDetection\Analyzer\SequenceAnalyzer;
use OCA\RansomwareDetection\AppInfo\Application;
use OCA\RansomwareDetection\Db\FileOperation;
use OCA\RansomwareDetection\Service\FileOperationService;
use OCA\Files_Trashbin\Trashbin;
use OCA\Files_Trashbin\Helper;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\OCSController;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\IConfig;
use OCP\IUserSession;
use OCP\IRequest;
use OCP\ILogger;

class MonitoringController extends OCSController
{
    /** @var IConfig */
    protected $config;

    /** @var IUserSession */
    protected $userSession;

    /** @var Classifier */
    protected $classifier;

    /** @var ILogger */
    protected $logger;

    /** @var Folder */
    protected $userFolder;

    /** @var FileOperationService */
    protected $service;

    /** @var SequenceAnalyzer */
    protected $sequenceAnalyzer;

    /** @var string */
    protected $userId;

    /**
     * @param string               $appName
     * @param IRequest             $request
     * @param IUserSession         $userSession
     * @param IConfig              $config
     * @param Classifier           $classifier
     * @param ILogger              $logger
     * @param Folder               $userFolder
     * @param FileOperationService $service
     * @param SequenceAnalyzer     $sequenceAnalyzer
     * @param string               $userId
     */
    public function __construct(
        $appName,
        IRequest $request,
        IUserSession $userSession,
        IConfig $config,
        Classifier $classifier,
        ILogger $logger,
        Folder $userFolder,
        FileOperationService $service,
        SequenceAnalyzer $sequenceAnalyzer,
        $userId
    ) {
        parent::__construct($appName, $request);

        $this->config = $config;
        $this->userSession = $userSession;
        $this->classifier = $classifier;
        $this->userFolder = $userFolder;
        $this->logger = $logger;
        $this->service = $service;
        $this->sequenceAnalyzer = $sequenceAnalyzer;
        $this->userId = $userId;
    }

    /**
     * Lists the classified files and sequences.
     *
     * @NoAdminRequired
     *
     * @return JSONResponse
     */
    public function listFileOperations()
    {
        $files = $this->service->findAll();

        $sequences = [];

        // Classify files and put together the sequences.
        foreach ($files as $file) {
            $this->classifier->classifyFile($file);
            $sequences[$file->getSequence()][] = $file;
        }

        $result = [];

        foreach ($sequences as $sequenceId => $sequence) {
            if (sizeof($sequence) >= $this->config->getAppValue(Application::APP_ID, 'minimum_sequence_length', 0)) {
                usort($sequence, function ($a, $b) {
                    return $b->getId() - $a->getId();
                });
                $sequenceResult = $this->sequenceAnalyzer->analyze($sequenceId, $sequence);
                $sequenceInformation = ['id' => $sequenceId, 'suspicionScore' => $sequenceResult->getSuspicionScore(), 'sequence' => $sequence];
                $result[] = $sequenceInformation;
            }
        }

        usort($result, function ($a, $b) {
            return $b['id'] - $a['id'];
        });

        return new JSONResponse($result, Http::STATUS_ACCEPTED);
    }

    /**
     * Exports classification and analysis data.
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     *
     * @param int $sequence
     *
     * @return JSONResponse
     */
    public function export()
    {
        $files = $this->service->findAll();

        $sequences = [];

        // Classify files and put together the sequences.
        foreach ($files as $file) {
            $this->classifier->classifyFile($file);
            $sequences[$file->getSequence()][] = $file;
        }

        $result = [];

        foreach ($sequences as $sequenceId => $sequence) {
            if (sizeof($sequence) >= $this->config->getAppValue(Application::APP_ID, 'minimum_sequence_length', 0)) {
                $result[] = $this->sequenceAnalyzer->analyze($sequenceId, $sequence)->toArray();
            }
        }

        return new JSONResponse($result, Http::STATUS_ACCEPTED);
    }

    /**
     * Deletes a sequence from the database.
     *
     * @NoAdminRequired
     *
     * @param int $sequence
     *
     * @return JSONResponse
     */
    public function deleteSequence($sequence)
    {
        $files = $this->service->deleteSequenceById($sequence);

        return new JSONResponse(['status' => 'success'], Http::STATUS_ACCEPTED);
    }

    /**
     * Recover files from trashbin or remove them from normal storage.
     *
     * @NoAdminRequired
     *
     * @param int $id file operation id
     *
     * @return JSONResponse
     */
    public function recover($id)
    {
        try {
            $file = $this->service->find($id);
            if ($file->getCommand() === Monitor::WRITE) {
                // Recover new created files by deleting them
                $filePath = $file->getPath().'/'.$file->getOriginalName();
                if ($this->deleteFromStorage($filePath)) {
                    $this->service->deleteById($id);

                    return new JSONResponse(['status' => 'success', 'id' => $id], Http::STATUS_OK);
                } else {
                    return new JSONResponse(['status' => 'error', 'message' => 'File cannot be deleted.'], Http::STATUS_BAD_REQUEST);
                }
            } elseif ($file->getCommand() === Monitor::DELETE) {
                // Recover deleted files by restoring them from the trashbin
                // It's not necessary to use the real path
                $dir = '/';
                $candidate = $this->findCandidateToRestore($dir, $file->getOriginalName());
                if ($candidate !== null) {
                    $path = $dir.'/'.$candidate['name'].'.d'.$candidate['mtime'];
                    if (Trashbin::restore($path, $candidate['name'], $candidate['mtime']) !== false) {
                        $this->service->deleteById($id);

                        return new JSONResponse(['status' => 'success', 'id' => $id], Http::STATUS_OK);
                    }

                    return new JSONResponse(['status' => 'error', 'message' => 'File does not exist.', 'path' => $path, 'name' => $candidate['name'], 'mtime' => $candidate['mtime']], Http::STATUS_BAD_REQUEST);
                } else {
                    return new JSONResponse(['status' => 'error', 'message' => 'No candidate found.'], Http::STATUS_BAD_REQUEST);
                }
            } elseif ($file->getCommand() === Monitor::RENAME) {
                $this->service->deleteById($id);

                return new JSONResponse(['status' => 'success', 'id' => $id], Http::STATUS_OK);
            } elseif ($file->getCommand() === Monitor::CREATE) {
                // Recover new created folders
                $filePath = $file->getPath().'/'.$file->getOriginalName();
                if ($this->deleteFromStorage($filePath)) {
                    $this->service->deleteById($id);

                    return new JSONResponse(['status' => 'success', 'id' => $id], Http::STATUS_OK);
                } else {
                    return new JSONResponse(['status' => 'error', 'message' => 'File cannot be deleted.'], Http::STATUS_BAD_REQUEST);
                }
            } else {
                // All other commands need no recovery
                $this->service->deleteById($id);

                return new JSONResponse(['id' => $id], Http::STATUS_OK);
            }
        } catch (\OCP\AppFramework\Db\MultipleObjectsReturnedException $exception) {
            // Found more than one with the same file name
            $this->logger->debug('recover: Found more than one with the same file name.', array('app' => Application::APP_ID));

            return new JSONResponse(['status' => 'error', 'message' => 'Found more than one with the same file name.'], Http::STATUS_BAD_REQUEST);
        } catch (\OCP\AppFramework\Db\DoesNotExistException $exception) {
            // Nothing found
            $this->logger->debug('recover: Files does not exist.', array('app' => Application::APP_ID));

            return new JSONResponse(['status' => 'error', 'message' => 'Files does not exist.'], Http::STATUS_BAD_REQUEST);
        }
    }

    /**
     * Deletes a file from the storage.
     *
     * @param string $path
     *
     * @return bool
     */
    private function deleteFromStorage($path)
    {
        try {
            $node = $this->userFolder->get($path);
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

    /**
     * Finds a candidate to restore if a file with the specific does not exist.
     *
     * @param string $dir
     * @param string $fileName
     *
     * @return FileInfo
     */
    private function findCandidateToRestore($dir, $fileName)
    {
        $files = array();
        $trashBinFiles = $this->getTrashFiles($dir);

        foreach ($trashBinFiles as $trashBinFile) {
            if (strcmp($trashBinFile['name'], $fileName) === 0) {
                $files[] = $trashBinFile;
            }
        }

        return array_pop($files);
    }

    /**
     * Workaround for testing.
     *
     * @param string $dir
     *
     * @return array
     */
    private function getTrashFiles($dir)
    {
        return Helper::getTrashFiles($dir, $this->userId, 'mtime', false);
    }
}
