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
script('ransomware_detection', 'app');
script('ransomware_detection', 'filelist');
script('ransomware_detection', 'vendor/font-awesome/fontawesome-all');
style('ransomware_detection', 'style');
?>
<div id="app-navigation">
    <ul>
        <li class="active">
            <a href="<?php p(\OC::$server->getURLGenerator()->linkToRoute('ransomware_detection.recover.index', [])); ?>">
                <img alt="" src="<?php print_unescaped(\OC::$server->getURLGenerator()->imagePath('core', 'actions/history.svg')); ?>">
                <span>Monitoring</span>
            </a>
        </li>
        <li>
            <a href="<?php p(\OC::$server->getURLGenerator()->linkToRoute('ransomware_detection.recover.scan', [])); ?>">
                <img alt="" src="<?php print_unescaped(\OC::$server->getURLGenerator()->imagePath('core', 'actions/search.svg')); ?>">
                <span>Scan files</span>
            </a>
        </li>
    </ul>
</div>
<div id="app-content">
    <div id="app-content-ransomware-detection-filelist">
        <!-- Tables -->
        <div class="section" id="section-loading">
    	       <div class="icon-loading-dark"></div>
    	</div>
    </div>
</div>
