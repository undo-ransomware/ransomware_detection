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

namespace OCA\RansomwareDetection\Service;

use OCA\RansomwareDetection\AppInfo\Application;
use OCA\RansomwareDetection\RequestTemplate;
use OCA\RansomwareDetection\Db\FileOperationMapper;
use OCA\RansomwareDetection\Model\Status;
use OCP\IConfig;
use OCP\ILogger;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class FileOperationService
{
    /** @var FileOperationMapper */
    protected $mapper;

    /** @var string */
    protected $userId;

    /** @var IConfig */
    protected $config;

    /** @var ILogger */
    protected $logger;

    /**
     * @param FileOperationMapper $mapper
     * @param IConfig             $config
     * @param ILogger             $logger
     * @param string              $userId
     */
    public function __construct(
        FileOperationMapper $mapper,
        IConfig $config,
        ILogger $logger,
        $userId
    ) {
        $this->mapper = $mapper;
        $this->logger = $logger;
        $this->config = $config;
        $this->userId = $userId;
    }

    /**
     * Find one by the id.
     *
     * @throws \OCP\AppFramework\Db\DoesNotExistException            if not found
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
     *
     * @param int $id
     *
     * @return Entity
     */
    public function find($id)
    {
        return $this->mapper->find($id, $this->userId);
    }

    /**
     * Find one by the file name.
     *
     * @throws \OCP\AppFramework\Db\DoesNotExistException            if not found
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
     *
     * @param string $name
     *
     * @return Entity
     */
    public function findOneByFileName($name)
    {
        return $this->mapper->findOneByFileName($name, $this->userId);
    }

    /**
     * Find one with the highest id.
     *
     * @throws \OCP\AppFramework\Db\DoesNotExistException            if not found
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
     *
     * @return Entity
     */
    public function findOneWithHighestId()
    {
        return $this->mapper->findOneWithHighestId($this->userId);
    }

    /**
     * Find all.
     *
     * @param array $params
     * @param int   $limit
     * @param int   $offset
     *
     * @return array
     */
    public function findAll(array $params = [], $limit = null, $offset = null)
    {
        array_push($params, $this->userId);

        $fileOperations = $this->mapper->findAll($params, $limit, $offset);

        // check if all file operations are analysed if not get results from detection service
        foreach($fileOperations as $fileOperation) {
            if ($fileOperation->getStatus() === Status::PENDING) {
                try {
                    $serviceUri = $this->config->getAppValue(Application::APP_ID, 'service_uri', 'http://localhost:5000');
                    try {
                        $result = RequestTemplate::get($serviceUri . "/file-operation/" . $fileOperation->getId());
                    } catch (ClientException $ex) {
                        if ($ex->getResponse()->getStatusCode() === 404) {
                            // if the detection service doesn't know the file analyze it again.
                            try {
                                RequestTemplate::post($serviceUri . "/file-operation", $fileOperation);
                            } catch(ClientException $ex) {
                                // already exists
                            } catch (ServerException $ex) {
                                $this->logger->error("The detection service is not working correctly.");
                            }
                        } else {
                            // update local file operation and save it to the database
                            $fileOperation->setStatus(json_decode($result)['status']);
                        }
                    } catch (ServerException $ex) {
                        $this->logger->error("The detection service is not working correctly.");
                    }
                } catch (ConnectException $ex) {
                    //TODO: Notify the use by the Notifier
                    $this->logger->error("No connection to the detection service.");
                }
            }
        }

        return $fileOperations;
    }

    /**
     * Delete one by id.
     *
     * @param int $id
     */
    public function deleteById($id)
    {
        $this->mapper->deleteById($id, $this->userId);
    }

    /**
     * Delete all entries before $timestamp.
     *
     * @param int $timestamp
     */
    public function deleteFileOperationsBefore($timestamp)
    {
        $this->mapper->deleteFileOperationsBefore($timestamp);
    }
}
