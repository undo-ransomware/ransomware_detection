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
<div id="ransomware_detection_recovery" class="section">
    <h2 class="inlineblock"><?php p($l->t('Recovery')); ?></h2>
    <div class="ransomware_detection_cronjob">
        <h3>
            <?php p($l->t('Number of days until a sequence will be deleted')); ?>
        </h3>
        <input id="expire_days" type="text" name="expire_days" value="<?php p($_['expireDays']); ?>"/>
    </div>
    <div class="ransomware_detection_sequence">
        <h3>
            <?php p($l->t('Minimum number of file operations contained by a sequence, that it will be shown in recovery')); ?>
        </h3>
        <input id="minimum_sequence_length" type="text" name="minimum_sequence_length" value="<?php p($_['minimumSequenceLength']); ?>"/>
    </div>
</div>
<div id="ransomware_detection_notification" class="section">
    <h2 class="inlineblock"><?php p($l->t('Notification')); ?></h2>
    <div class="ransomware_detection_notification">
        <h3>
            <?php p($l->t('Suspicion threshold for notifications')); ?>
        </h3>
        <select id="sequence-suspicion-level" name="sequence-suspicion-level" class="suspicion-level-<?php p($_['activeSuspicionLevel']['code']); ?>" data-placeholder="<?php p($l->t('Sequence suspicion level'));?>">
            <option class="suspicion-level-<?php p($_['activeSuspicionLevel']['code']); ?>" value="<?php p($_['activeSuspicionLevel']['code']); ?>">
				<?php p($_['activeSuspicionLevel']['name']); ?>
			</option>
            <?php foreach ($_['suspicionLevels'] as $suspicionLevel): ?>
				<option class="suspicion-level-<?php p($suspicionLevel['code']); ?>" value="<?php p($suspicionLevel['code']); ?>">
					<?php p($suspicionLevel['name']);?>
				</option>
			<?php endforeach; ?>
        </select>
    </div>
</div>
