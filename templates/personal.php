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
script('ransomware_detection', 'personal');
?>
<div id="ransomware_detection_color_mode" class="section">
    <h2><?php p($l->t('Color Mode')); ?></h2>
    <p class="settings-hint hidden-when-empty">Current color mode for coding sequences.</p>

    <div class="ransomware_detection_color_scheme">
        <h3>
            <?php p($l->t('Current color mode')); ?>
        </h3>
        <select id="color-scheme" name="color-scheme" data-placeholder="<?php p($l->t('Color mode'));?>">
            <option value="<?php p($_['colorActive']['code']); ?>">
				<?php p($_['colorActive']['name']); ?>
			</option>
            <option value="<?php p($_['color']['code']); ?>">
				<?php p($_['color']['name']); ?>
			</option>
        </select>
    </div>
</div>
