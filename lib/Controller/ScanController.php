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
use OCA\RansomwareDetection\Analyzer\EntropyAnalyzer;
use OCA\RansomwareDetection\Analyzer\FileCorruptionAnalyzer;
use OCA\RansomwareDetection\Analyzer\FileNameAnalyzer;
use OCA\RansomwareDetection\AppInfo\Application;
use OCA\RansomwareDetection\Db\FileOperation;
use OCA\RansomwareDetection\Exception\NotAFileException;
use OCA\RansomwareDetection\Service\FileOperationService;
use OCA\RansomwareDetection\Scanner\StorageStructure;
use OCP\Files\NotFoundException;
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
use OCP\IDBConnection;
use OCP\ILogger;

class ScanController extends OCSController
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

    /** @var EntropyAnalyzer */
    protected $entropyAnalyzer;

    /** @var FileCorruptionAnalyzer */
    protected $fileCorruptionAnalyzer;

    /** @var FileNameAnalyzer */
    protected $fileNameAnalyzer;

    /** @var IDBConnection */
	protected $connection;

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
     * @param EntropyAnalyzer      $entropyAnalyzer
     * @param FileCorruptionAnalyzer $fileCorruptionAnalyzer
     * @param FileNameAnalyzer     $fileNameAnalyzer
     * @param IDBConnection        $connection
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
        EntropyAnalyzer $entropyAnalyzer,
        FileCorruptionAnalyzer $fileCorruptionAnalyzer,
        FileNameAnalyzer $fileNameAnalyzer,
        IDBConnection $connection,
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
        $this->entropyAnalyzer = $entropyAnalyzer;
        $this->fileCorruptionAnalyzer = $fileCorruptionAnalyzer;
        $this->fileNameAnalyzer = $fileNameAnalyzer;
        $this->connection = $connection;
        $this->userId = $userId;
    }

    /**
     * Post scan recovery.
     *
     * @NoAdminRequired
     *
     * @param  integer $id
     * @param  integer $sequence
     * @param  integer $command
     * @param  string  $path
     * @param  string  $name
     * @param  integer $timestamp
     *
     * @return JSONResponse
     */
    public function recover($id, $sequence, $command, $path, $name, $timestamp)
    {
        if ($command === Monitor::WRITE) {
            // Delete file
            if ($this->deleteFromStorage($path . '/' . $name)) {
                return new JSONResponse(['status' => 'success', 'id' => $id, 'sequence' => $sequence], Http::STATUS_OK);
            } else {
                return new JSONResponse(['status' => 'error', 'message' => 'File cannot be deleted.'], Http::STATUS_OK);
            }
        } else if ($command === Monitor::DELETE) {
            // Restore file
            $trashPath = '/'.$name.'.d'.$timestamp;;
            if ($this->restoreFromTrashbin($trashPath, $name, $timestamp) !== false) {
                return new JSONResponse(['status' => 'success', 'id' => $id, 'sequence' => $sequence], Http::STATUS_OK);
            }

            return new JSONResponse(['status' => 'error', 'message' => 'File does not exist.', 'path' => $trashPath, 'name' => $name, 'mtime' => $timestamp], Http::STATUS_OK);
        } else {
            // wubalubadubdub
            // Scan can only detect WRITE and DELETE this should never happen.
            $this->logger->error('postRecover: RENAME or CREATE operation.', array('app' => Application::APP_ID));
            return new JSONResponse(['status' => 'error', 'message' => 'Wrong command.'], Http::STATUS_BAD_REQUEST);
        }

    }

    /**
     * The files to scan.
     *
     * @NoAdminRequired
     *
     * @return JSONResponse
     */
    public function filesToScan()
    {
        $start = time();
        $storageStructure = $this->getStorageStructure($this->userFolder);
        $trashStorageStructure = $this->getTrashStorageStructure();

        $allFiles = array();

        // convert file to json and merge into one array
        $files = $storageStructure->getFiles();
        for ($i = 0; $i < count($files); $i++) {
            $allFiles[] = ['id' => $files[$i]->getId(), 'path' => $files[$i]->getInternalPath(), 'timestamp' => $this->getLastActivity($files[$i]->getId())['timestamp']];
        }
        $trashFiles = $trashStorageStructure->getFiles();
        for ($i = 0; $i < count($trashFiles); $i++) {
            $allFiles[] = ['id' => $trashFiles[$i]->getId(), 'path' => $trashFiles[$i]->getInternalPath(), 'timestamp' => $trashFiles[$i]->getMtime()];
        }

        // sort ASC for timestamp
        usort($allFiles, function ($a, $b) {
            if ($a['timestamp'] === $b['timestamp']) {
                return 0;
            }
            return $a['timestamp'] - $b['timestamp'];
        });

        // build sequences
        $sequencesArray = array();
        $sequence = array();
        for ($i = 0; $i < count($allFiles); $i++) {
            if ($i === 0) {
                $sequence = array();
            } else {
                if ($allFiles[$i]['timestamp'] - $allFiles[$i - 1]['timestamp'] > 180) {
                    $sequencesArray[] = $sequence;
                    $sequence = array();
                }
            }
            $sequence[] = $allFiles[$i];
        }
        $sequencesArray[] = $sequence;
        $end = time();

        return new JSONResponse(['status' => 'success', 'sequences' => $sequencesArray, 'number_of_files' => $storageStructure->getNumberOfFiles(), 'scan_duration' => $end - $start], Http::STATUS_OK);
    }

    /**
     * Scan sequence.
     *
     * @NoAdminRequired
     *
     * @param string  $sequence
     * @return JSONResponse
     */
    public function scanSequence($sequence) {
        if (sizeof($sequence) > $this->config->getAppValue(Application::APP_ID, 'minimum_sequence_length', 0)) {
            $sequenceResults = array();
            foreach ($sequence as $file) {
                try {
                    $fileOperation = $this->buildFileOperation($file);
                } catch (NotAFileException $exception) {
                    $this->logger->debug('scanSequence: Path to file doesn\'t lead to file object.', array('app' => Application::APP_ID));
                    continue;
                } catch (NotFoundException $exception) {
                    $this->logger->debug('scanSequence: Not found.', array('app' => Application::APP_ID));
                    continue;
                }

                $this->classifier->classifyFile($fileOperation);
                $jsonSequence[] = ['userId' => $fileOperation->getUserId(), 'path' => $fileOperation->getPath(), 'originalName' => preg_replace('/.d[0-9]{10}/', '', $fileOperation->getOriginalName()),
                    'type' => $fileOperation->getType(), 'mimeType' => $fileOperation->getMimeType(), 'size' => $fileOperation->getSize(), 'corrupted' => $fileOperation->getCorrupted(), 'timestamp' => $fileOperation->getTimestamp(), 'entropy' => $fileOperation->getEntropy(),
                    'standardDeviation' => $fileOperation->getStandardDeviation(), 'command' => $fileOperation->getCommand(), 'fileNameEntropy' => $fileOperation->getFileNameEntropy(), 'fileClass' => $fileOperation->getFileClass(), 'fileNameClass' => $fileOperation->getFileNameClass(), 'suspicionClass' => $fileOperation->getSuspicionClass()];
                $fileOperationSequence[] = $fileOperation;
            }
            if (count($fileOperationSequence) > 0) {
                $sequenceResult = $this->sequenceAnalyzer->analyze(0, $fileOperationSequence);
                return new JSONResponse(['status' => 'success', 'suspicion_score' => $sequenceResult->getSuspicionScore(), 'sequence' => $jsonSequence], Http::STATUS_OK);
            } else {
                return new JSONResponse(['status' => 'error', 'message' => 'The file(s) requested do(es) not exist.']);
            }
        } else {
            return new JSONResponse(['status' => 'error', 'message' => 'Sequence is to short.'], Http::STATUS_OK);
        }
    }

    /**
     * Builds a file operations from a file info array.
     *
     * @param  array $file
     * @return FileOperation
     */
    protected function buildFileOperation($file)
    {
        $fileOperation = new FileOperation();
        $fileOperation->setUserId($this->userId);
        if (strpos($file['path'], 'files_trashbin') !== false) {
            $node = $this->userFolder->getParent()->get($file['path'] . '.d' . $file['timestamp']);
            $fileOperation->setCommand(Monitor::DELETE);
            $fileOperation->setTimestamp($file['timestamp']);
            $pathInfo = pathinfo($node->getInternalPath());
            $fileOperation->setPath($pathInfo['dirname']);
        } else {
            $node = $this->userFolder->getParent()->get($file['path']);
            $lastActivity = $this->getLastActivity($file['id']);
            $fileOperation->setCommand(Monitor::WRITE);
            $fileOperation->setTimestamp($lastActivity['timestamp']);
            $pathInfo = pathinfo($node->getInternalPath());
            $fileOperation->setPath(str_replace('files', '', $pathInfo['dirname']));
        }
        if (!($node instanceof File)) {
            throw new NotAFileException();
        }
        $fileOperation->setOriginalName($node->getName());
        $fileOperation->setType('file');
        $fileOperation->setMimeType($node->getMimeType());
        $fileOperation->setSize($node->getSize());
        $fileOperation->setTimestamp($file['timestamp']);

        // file name analysis
        $fileNameResult = $this->fileNameAnalyzer->analyze($node->getInternalPath());
        $fileOperation->setFileNameClass($fileNameResult->getFileNameClass());
        $fileOperation->setFileNameEntropy($fileNameResult->getEntropyOfFileName());

        $fileCorruptionResult = $this->fileCorruptionAnalyzer->analyze($node);
        $fileOperation->setCorrupted($fileCorruptionResult->isCorrupted());

        // entropy analysis
        $entropyResult = $this->entropyAnalyzer->analyze($node);
        $fileOperation->setEntropy($entropyResult->getEntropy());
        $fileOperation->setStandardDeviation($entropyResult->getStandardDeviation());
        if ($fileCorruptionResult->isCorrupted()) {
            $fileOperation->setFileClass($entropyResult->getFileClass());
        } else {
            if ($fileCorruptionResult->getFileClass() !== -1) {
                $fileOperation->setFileClass($fileCorruptionResult->getFileClass());
            }
        }

        return $fileOperation;
    }

    /**
     * Get last activity.
     *
     * @param $objectId
     */
    protected function getLastActivity($objectId)
    {
        $query = $this->connection->getQueryBuilder();
		$query->select('*')->from('activity');
        $query->where($query->expr()->eq('affecteduser', $query->createNamedParameter($this->userId)))
            ->andWhere($query->expr()->eq('object_id', $query->createNamedParameter($objectId)));
        $result = $query->execute();
        while ($row = $result->fetch()) {
            $rows[] = $row;
        }
        $result->closeCursor();
        if (is_array($rows)) {
            return array_pop($rows);
        } else {
            $this->logger->debug('getLastActivity: No activity found.', array('app' => Application::APP_ID));
            return 0;
        }
    }

    /**
     * Get trash storage structure.
     *
     * @return StorageStructure
     */
    protected function getTrashStorageStructure()
    {
        $storageStructure = new StorageStructure(0, []);
        $nodes = Helper::getTrashFiles("/", $this->userId, 'mtime', false);
        foreach ($nodes as $node) {
            $storageStructure->addFile($node);
            $storageStructure->increaseNumberOfFiles();
        }
        return $storageStructure;
    }

    /**
     * Get storage structure recursively.
     *
     * @param INode $node
     *
     * @return StorageStructure
     */
    protected function getStorageStructure($node)
    {
        // set count for node to 0
        $storageStructure = new StorageStructure(0, []);
        if ($node instanceof Folder) {
            // it's a folder
            $nodes = $node->getDirectoryListing();
            if (count($nodes) === 0) {
                // folder is empty so nothing to do
                return $storageStructure;
            }
            foreach ($nodes as $tmpNode) {
                // analyse files in subfolder
                $tmpStorageStructure = $this->getStorageStructure($tmpNode);
                $storageStructure->setFiles(array_merge($storageStructure->getFiles(), $tmpStorageStructure->getFiles()));
                $storageStructure->setNumberOfFiles($storageStructure->getNumberOfFiles() + $tmpStorageStructure->getNumberOfFiles());
            }
            return $storageStructure;
        }
        else if ($node instanceof File) {
            // it's a file
            $storageStructure->addFile($node);
            $storageStructure->increaseNumberOfFiles();
            return $storageStructure;
        }
        else {
            // it's me Mario.
            // there is nothing else than file or folder
            $this->logger->error('getStorageStructure: Neither file nor folder.', array('app' => Application::APP_ID));
        }
    }

    /**
     * Deletes a file from the storage.
     *
     * @param string $path
     *
     * @return bool
     */
    protected function deleteFromStorage($path)
    {
        try {
            $node = $this->userFolder->get($path);
            if ($node instanceof File && $node->isDeletable()) {
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
     * Restores file from trash bin.
     *
     * @param  string   $trashPath
     * @param  array    $pathInfo
     * @param  integer  $timestamp
     * @return boolean
     */
    protected function restoreFromTrashbin($trashPath, $name, $timestamp)
    {
        return Trashbin::restore($trashPath, $name, $timestamp);
    }
}
