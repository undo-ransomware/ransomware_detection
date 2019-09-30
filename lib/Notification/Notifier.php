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

namespace OCA\RansomwareDetection\Notification;

use OCA\RansomwareDetection\AppInfo\Application;
use OCP\IURLGenerator;
use OCP\IUserManager;
use OCP\L10N\IFactory;
use OCP\Notification\IManager;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;
use OCP\IConfig;

class Notifier implements INotifier
{
    /** @var IConfig */
    private $config;

    /** @var IFactory */
    protected $l10nFactory;

    /** @var IUserManager */
    protected $userManager;

    /** @var IManager */
    protected $notificationManager;

    /** @var IURLGenerator */
    protected $urlGenerator;

    /**
     * @param IConfig       $config
     * @param IFactory      $l10nFactory
     * @param IUserManager  $userManager
     * @param IManager      $notificationManager
     * @param IURLGenerator $urlGenerator
     */
    public function __construct(
        IConfig $config,
        IFactory $l10nFactory,
        IUserManager $userManager,
        IManager $notificationManager,
        IURLGenerator $urlGenerator
    ) {
        $this->config = $config;
        $this->l10nFactory = $l10nFactory;
        $this->userManager = $userManager;
        $this->notificationManager = $notificationManager;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param INotification $notification
     * @param string        $languageCode
     *
     * @return INotification
     */
    public function prepare(INotification $notification, string $languageCode): INotification
    {
        if ($notification->getApp() !== Application::APP_ID) {
            // Not my app => throw
            throw new \InvalidArgumentException('Unknown app');
        }

        //Read the language from the notification
        $l = $this->l10nFactory->get(Application::APP_ID, $languageCode);

        switch ($notification->getSubject()) {
            case 'ransomware_attack_detected':
                $message = 'Detected a sequence of suspicious file operations.';
                $notification->setParsedSubject($l->t('Detected suspicious file operations.', $notification->getSubjectParameters()));
                $notification->setParsedMessage($l->t($message, $notification->getMessageParameters()));
                $notification->setIcon($this->urlGenerator->imagePath('ransomware_detection', 'app-dark.svg'));

                return $notification;
            default:
                throw new \InvalidArgumentException('Unknown subject');
        }
    }

    /**
     * Identifier of the notifier, only use [a-z0-9_]
     *
     * @return string
     * @since 17.0.0
     */
    public function getID(): string {
        return Application::APP_ID;
    }
    
    /**
     * Human readable name describing the notifier
     *
     * @return string
     * @since 17.0.0
     */
    public function getName(): string {
        return $this->l10nFactory->get(Application::APP_ID)->t('Ransomware recovery');
    }
}
