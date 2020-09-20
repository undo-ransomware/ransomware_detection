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

use OCA\RansomwareDetection\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\Settings\ISettings;

class Admin implements ISettings
{
    /** @var IConfig */
    protected $config;

    /**
     * @param IConfig $config
     */
    public function __construct(
        IConfig $config
    ) {
        $this->config = $config;
    }

    /**
     * @return TemplateResponse returns the instance with all parameters set, ready to be rendered
     *
     * @since 9.1
     */
    public function getForm()
    {
        return new TemplateResponse(Application::APP_ID, 'admin', [
            'expire_days' => $this->config->getAppValue(Application::APP_ID, 'expire_days', 7),
        ], '');
    }

    /**
     * @return string the section ID, e.g. 'sharing'
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
        return 1;
    }
}
