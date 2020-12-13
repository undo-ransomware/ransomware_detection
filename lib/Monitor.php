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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace OCA\RansomwareDetection;

use OCA\RansomwareDetection\AppInfo\Application;
use OCA\RansomwareDetection\Analyzer\EntropyAnalyzer;
use OCA\RansomwareDetection\Analyzer\EntropyResult;
use OCA\RansomwareDetection\Analyzer\FileCorruptionAnalyzer;
use OCA\RansomwareDetection\Analyzer\FileExtensionAnalyzer;
use OCA\RansomwareDetection\Analyzer\FileExtensionResult;
use OCA\RansomwareDetection\Db\FileOperation;
use OCA\RansomwareDetection\Db\FileOperationMapper;
use OCP\App\IAppManager;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\Files\Storage\IStorage;
use OCP\Notification\IManager;
use OCP\IConfig;
use OCP\ILogger;
use OCP\IRequest;

class Monitor
{
    /** File access
     * @var int
     */
    const DELETE = 1;
    const RENAME = 2;
    const WRITE = 3;
    const READ = 4;
    const CREATE = 5;

    /** @var IRequest */
    protected $request;

    /** @var IConfig */
    protected $config;

    /** @var ITimeFactory */
    protected $time;

    /** @var IAppManager */
    protected $appManager;

    /** @var ILogger */
    protected $logger;

    /** @var IRootFolder */
    protected $rootFolder;

    /** @var EntropyAnalyzer */
    protected $entropyAnalyzer;

    /** @var FileOperationMapper */
    protected $mapper;

    /** @var FileExtensionAnalyzer */
    protected $fileExtensionAnalyzer;

    /** @var FileCorruptionAnalyzer */
    protected $fileCorruptionAnalyzer;

    /** @var string */
    protected $userId;

    /** @var int */
    protected $nestingLevel = 0;

    /**
     * @param IRequest                  $request
     * @param IConfig                   $config
     * @param ITimeFactory              $time
     * @param IAppManager               $appManager
     * @param ILogger                   $logger
     * @param IRootFolder               $rootFolder
     * @param EntropyAnalyzer           $entropyAnalyzer
     * @param FileOperationMapper       $mapper
     * @param FileExtensionAnalyzer     $fileExtensionAnalyzer
     * @param FileCorruptionAnalyzer    $fileCorruptionAnalyzer
     * @param string                    $userId
     */
    public function __construct(
        IRequest $request,
        IConfig $config,
        ITimeFactory $time,
        IAppManager $appManager,
        ILogger $logger,
        IRootFolder $rootFolder,
        EntropyAnalyzer $entropyAnalyzer,
        FileOperationMapper $mapper,
        FileExtensionAnalyzer $fileExtensionAnalyzer,
        FileCorruptionAnalyzer $fileCorruptionAnalyzer,
        $userId
    ) {
        $this->request = $request;
        $this->config = $config;
        $this->time = $time;
        $this->appManager = $appManager;
        $this->logger = $logger;
        $this->rootFolder = $rootFolder;
        $this->entropyAnalyzer = $entropyAnalyzer;
        $this->mapper = $mapper;
        $this->fileExtensionAnalyzer = $fileExtensionAnalyzer;
        $this->fileCorruptionAnalyzer = $fileCorruptionAnalyzer;
        $this->userId = $userId;
    }

    /**
     * Analyze file.
     *
     * @param array    $paths
     * @param int      $mode
     */
    public function analyze($paths, $mode)
    {
        $path = $paths[0];
		
		if ($path === '') {
			$this->logger->debug("Path is empty.");
			return;
		}

        $storage = $this->rootFolder->getUserFolder($this->userId)->get(dirname($path))->getStorage();
        if ($this->userId === null || $this->nestingLevel !== 0 || !$this->isUploadedFile($storage, $path) || $this->isCreatingSkeletonFiles()) {
            // check only cloud files and no system files
            return;
        }

        if (!$this->request->isUserAgent([
            IRequest::USER_AGENT_CLIENT_DESKTOP,
            IRequest::USER_AGENT_CLIENT_ANDROID,
            IRequest::USER_AGENT_CLIENT_IOS,
        ])) {
            // not a sync client
            return;
        }

        $this->nestingLevel++;

        switch ($mode) {
            case self::RENAME:
                $path = $paths[1];
                $this->logger->debug("Rename ".$paths[0]." to ".$paths[1], ['app' =>  Application::APP_ID]);
                if (preg_match('/.+\.d[0-9]+/', pathinfo($paths[1])['basename']) > 0) {
                    return;
                }
                // reset PROPFIND_COUNT
                $this->resetProfindCount();

                try {
                    $userRoot = $this->rootFolder->getUserFolder($this->userId);
                    $node = $userRoot->get($path);
                } catch (\OCP\Files\NotFoundException $exception) {
                    $this->logger->error("File Not Found ".$path, ['app' =>  Application::APP_ID]);
                    return;
                }

                // not a file no need to analyze
                if (!($node instanceof File)) {
                    $this->addFolderOperation($paths, $node, self::RENAME);
                    $this->nestingLevel--;

                    return;
                }

                $this->addFileOperation($paths, $node, self::RENAME);

                $this->nestingLevel--;

                return;
            case self::WRITE:
                $this->logger->debug("Write ".$path, ['app' =>  Application::APP_ID]);
                // reset PROPFIND_COUNT
                $this->resetProfindCount();

                try {
                    $userRoot = $this->rootFolder->getUserFolder($this->userId);
                    $node = $userRoot->get($path);
                } catch (\OCP\Files\NotFoundException $exception) {
                    $this->logger->error("File Not Found ".$path, ['app' =>  Application::APP_ID]);
                    return;
                }

                // not a file no need to analyze
                if (!($node instanceof File)) {
                    $this->addFolderOperation($paths, $node, self::WRITE);
                    $this->nestingLevel--;

                    return;
                }

                $this->addFileOperation($paths, $node, self::WRITE);

                $this->nestingLevel--;

                return;
            case self::READ:
                $this->nestingLevel--;

                return;
            case self::DELETE:
                $this->logger->debug("Delete ".$path, ['app' =>  Application::APP_ID]);
                // reset PROPFIND_COUNT
                $this->resetProfindCount();

                try {
                    $userRoot = $this->rootFolder->getUserFolder($this->userId);
                    $node = $userRoot->get($path);
                } catch (\OCP\Files\NotFoundException $exception) {
                    $this->logger->error("File Not Found ".$path, ['app' =>  Application::APP_ID]);
                    return;
                }

                // not a file no need to analyze
                if (!($node instanceof File)) {
                    $this->addFolderOperation($paths, $node, self::DELETE);
                    $this->nestingLevel--;

                    return;
                }

                $this->addFileOperation($paths, $node, self::DELETE);

                $this->nestingLevel--;

                return;
            case self::CREATE:
                $this->logger->debug("Create ".$path, ['app' =>  Application::APP_ID]);
                // reset PROPFIND_COUNT
                $this->resetProfindCount();

                try {
                    $userRoot = $this->rootFolder->getUserFolder($this->userId);
                    $node = $userRoot->get($path);
                } catch (\OCP\Files\NotFoundException $exception) {
                    $this->logger->error("File Not Found ".$path, ['app' =>  Application::APP_ID]);
                    return;
                }
                if (!($node instanceof File)) {

                    $fileOperation = new FileOperation();
                    $fileOperation->setUserId($this->userId);
                    $fileOperation->setPath(str_replace('files', '', pathinfo($path)['dirname']));
                    $fileOperation->setOriginalName(pathinfo($path)['basename']);
                    $fileOperation->setType('folder');
                    $fileOperation->setMimeType('httpd/unix-directory');
                    $fileOperation->setSize(0);
                    $fileOperation->setTimestamp(time());
                    $fileOperation->setCorrupted(false);
                    $fileOperation->setCommand(self::CREATE);
                    $sequenceId = $this->config->getUserValue($this->userId, Application::APP_ID, 'sequence_id', 0);
                    $fileOperation->setSequence($sequenceId);

                    // entropy analysis
                    $fileOperation->setEntropy(0.0);
                    $fileOperation->setStandardDeviation(0.0);
                    $fileOperation->setFileClass(EntropyResult::NORMAL);

                    // file extension analysis
                    $fileOperation->setFileExtensionClass(FileExtensionResult::NOT_SUSPICIOUS);

                    $this->mapper->insert($fileOperation);
                    $this->nestingLevel--;
                } else {
                    $this->addFileOperation($paths, $node, self::CREATE);

                    $this->nestingLevel--;
                }

                return;
            default:
                $this->nestingLevel--;

                return;
        }
    }

    /**
     * Check if we are in the LoginController and if so, ignore the firewall.
     *
     * @return bool
     */
    protected function isCreatingSkeletonFiles()
    {
        $exception = new \Exception();
        $trace = $exception->getTrace();
        foreach ($trace as $step) {
            if (isset($step['class'], $step['function']) &&
                $step['class'] === 'OC\Core\Controller\LoginController' &&
                $step['function'] === 'tryLogin') {
                return true;
            }
        }

        return false;
    }

    /**
     * Reset PROPFIND_COUNT.
     */
    protected function resetProfindCount()
    {
        $userKeys = $this->config->getUserKeys($this->userId, Application::APP_ID);
        foreach ($userKeys as $key) {
            if (strpos($key, 'propfind_count') !== false) {
                $this->config->deleteUserValue($this->userId, Application::APP_ID, $key);
            }
        }
    }

    /**
     * Return file size of a path.
     *
     * @param string $path
     *
     * @return int
     */
    private function getFileSize($path)
    {
        if (strpos($path, 'files_trashbin') !== false) {
            $node = $this->rootFolder->get($path);

            if (!($node instanceof File)) {
                throw new NotFoundException();
            }

            return $node->getSize();
        } else {
            $userRoot = $this->rootFolder->getUserFolder($this->userId)->getParent();
            $node = $userRoot->get($path);

            if (!($node instanceof File)) {
                throw new NotFoundException();
            }

            return $node->getSize();
        }
    }

    /**
     * Check if file is a uploaded file.
     *
     * @param IStorage $storage
     * @param string   $path
     *
     * @return bool
     */
    private function isUploadedFile(IStorage $storage, $path)
    {
        $fullPath = $path;
        if (property_exists($storage, 'mountPoint')) {
            /* @var StorageWrapper $storage */
            try {
                $fullPath = $storage->mountPoint.$path;
            } catch (\Exception $ex) {
                return true;
            }
        }

        // ignore transfer files
        if (strpos($fullPath, 'ocTransferId') > 0) {
            return false;
        }

        if (substr_count($fullPath, '/') < 3) {
            return false;
        }

        // '', admin, 'files', 'path/to/file.txt'
        $segment = explode('/', $fullPath, 4);

        return isset($segment[2]) && in_array($segment[2], [
            'files',
            'thumbnails',
            'files_versions',
        ], true);
    }

    /**
     * Add a folder to the operations.
     *
     * @param array $paths
     * @param INode $node
     * @param int   $operation
     */
    private function addFolderOperation($paths, $node, $operation)
    {
        $this->logger->debug("Add folder operation.", ['app' =>  Application::APP_ID]);
        if (is_null($paths)) {
            $this->logger->debug("Add folder operation: Paths are empty.", ['app' =>  Application::APP_ID]);
            return;
        }
        $fileOperation = new FileOperation();
        $fileOperation->setUserId($this->userId);
        $fileOperation->setPath(str_replace('files', '', pathinfo($node->getInternalPath())['dirname']));
        $fileOperation->setOriginalName($node->getName());
        if ($operation === self::RENAME) {
            $fileOperation->setNewName(pathinfo($paths[1])['basename']);
        }
        $fileOperation->setType('folder');
        $fileOperation->setMimeType($node->getMimeType());
        $fileOperation->setSize(0);
        $fileOperation->setTimestamp(time());
        $fileOperation->setCorrupted(false);
        $fileOperation->setCommand($operation);
        $sequenceId = $this->config->getUserValue($this->userId, Application::APP_ID, 'sequence_id', 0);
        $fileOperation->setSequence($sequenceId);

        // entropy analysis
        $fileOperation->setEntropy(0.0);
        $fileOperation->setStandardDeviation(0.0);
        $fileOperation->setFileClass(EntropyResult::NORMAL);

        // file extension analysis
        $fileOperation->setFileExtensionClass(FileExtensionResult::NOT_SUSPICIOUS);

        $this->mapper->insert($fileOperation);
    }

    /**
     * Add a file to the operations.
     *
     * @param array $paths
     * @param INode $node
     * @param int   $operation
     */
    private function addFileOperation($paths, $node, $operation)
    {
        $this->logger->debug("Add file operation.", ['app' =>  Application::APP_ID]);
        if (is_null($paths)) {
            $this->logger->debug("Add file operation: Paths are empty.", ['app' =>  Application::APP_ID]);
            return;
        }
        $fileOperation = new FileOperation();
        $fileOperation->setUserId($this->userId);
        $fileOperation->setPath(str_replace('files', '', pathinfo($node->getInternalPath())['dirname']));
        $fileOperation->setOriginalName($node->getName());
        if ($operation === self::RENAME) {
            $fileOperation->setNewName(pathinfo($paths[1])['basename']);
        }
        $fileOperation->setType('file');
        $fileOperation->setMimeType($node->getMimeType());
        $fileOperation->setSize($node->getSize());
        $fileOperation->setTimestamp(time());
        $fileOperation->setCommand($operation);
        $sequenceId = $this->config->getUserValue($this->userId, Application::APP_ID, 'sequence_id', 0);
        $fileOperation->setSequence($sequenceId);

        // file extension analysis
        $fileExtensionResult = $this->fileExtensionAnalyzer->analyze($node->getInternalPath());
        $fileOperation->setFileExtensionClass($fileExtensionResult->getFileExtensionClass());

        $fileCorruptionResult = $this->fileCorruptionAnalyzer->analyze($node);
        $isCorrupted = $fileCorruptionResult->isCorrupted();
        $fileOperation->setCorrupted($isCorrupted);
        if ($isCorrupted) {
            $fileOperation->setFileExtensionClass(FileExtensionResult::SUSPICIOUS);
        }

        // entropy analysis
        $entropyResult = $this->entropyAnalyzer->analyze($node);
        $fileOperation->setEntropy($entropyResult->getEntropy());
        $fileOperation->setStandardDeviation($entropyResult->getStandardDeviation());
        $fileOperation->setFileClass($entropyResult->getFileClass());

        $entity = $this->mapper->insert($fileOperation);
    }
}
