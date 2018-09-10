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

namespace OCA\RansomwareDetection\Settings;

use OCP\Settings\ISettings;
use OCA\RansomwareDetection\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\IConfig;
use OCP\IL10N;

class Personal implements ISettings
{
    /** @var IConfig */
    protected $config;

    /** @var ITimeFactory */
    protected $time;

    /** @var IL10N */
    protected $l10n;

    /** @var string */
    protected $userId;

    /**
     * @param IConfig      $config
     * @param ITimeFactory $time
     * @param IL10N        $l10n
     * @param string       $userId
     */
    public function __construct(
        IConfig $config,
        ITimeFactory $time,
        IL10N $l10n,
        $userId
    ) {
        $this->config = $config;
        $this->time = $time;
        $this->l10n = $l10n;
        $this->userId = $userId;
    }

    /**
     * @return TemplateResponse returns the instance with all parameters set, ready to be rendered
     *
     * @since 9.1
     */
    public function getForm()
    {
        $colorMode = $this->config->getUserValue($this->userId, Application::APP_ID, 'color_mode', 0);

        if ($colorMode === 0) {
            $colorActive = ['code' => 0, 'name' => 'Normal'];
            $color = ['code' => 1, 'name' => 'Color blind'];
        } else {
            $colorActive = ['code' => 1, 'name' => 'Color blind'];
            $color = ['code' => 0, 'name' => 'Normal'];
        }

        return new TemplateResponse(Application::APP_ID, 'personal', [
            'colorActive' => $colorActive,
            'color' => $color,
        ], '');
    }

    /**
     * @return string the section ID, e.g. 'ransomware_detection'
     *
     * @since 9.1
     */
    public function getSection()
    {
        return Application::APP_ID;
    }

    /**
     * @return int whether the form should be rather on the top or bottom of
     *             the admin section. The forms are arranged in ascending order of the
     *             priority values. It is required to return a value between 0 and 100.
     *
     * E.g.: 70
     *
     * @since 9.1
     */
    public function getPriority()
    {
        return 40;
    }
}
