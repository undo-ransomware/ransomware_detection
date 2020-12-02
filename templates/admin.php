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
style('ransomware_detection', 'style');
script('ransomware_detection', 'admin');
?>
<div id="ransomware_detection_service" class="section">
    <h2 class="inlineblock"><?php p($l->t('Recovery')); ?></h2>
    <div class="ransomware_detection_cronjob">
        <h3>
            <?php p($l->t('Number of days until a sequence will be deleted')); ?>
        </h3>
        <input id="expire_days" type="text" name="expire_days" value="<?php p($_['expire_days']); ?>"/>
    </div>
</div>
