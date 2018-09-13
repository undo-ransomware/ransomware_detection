/**
 * @copyright Copyright (c) 2017 Matthias Held <matthias.held@uni-konstanz.de>
 *
 * @author Matthias Held <matthias.held@uni-konstanz.de>
 *
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

$(document).ready(function() {
    var timeouts = {
        'minimum_sequence_length': undefined,
        'expire_days': undefined
    };
    // Save number of ransomware files function
    var saveTextInput = function(fieldId, $field) {
        OCP.AppConfig.setValue('ransomware_detection', fieldId, $field.val());
    };

    $('#expire_days').on('change input paste keyup', function(e) {
        var $field = $(e.currentTarget);

        if (!_.isUndefined(timeouts['expire_days'])) {
           clearTimeout(timeouts['expire_days']);
       }

        timeouts['expire_days'] = setTimeout(_.bind(saveTextInput, this, 'expire_days', $field), 1500);
    });

    $('#minimum_sequence_length').on('change input paste keyup', function(e) {
        var $field = $(e.currentTarget);

        if (!_.isUndefined(timeouts['minimum_sequence_length'])) {
           clearTimeout(timeouts['minimum_sequence_length']);
       }

        timeouts['minimum_sequence_length'] = setTimeout(_.bind(saveTextInput, this, 'minimum_sequence_length', $field), 1500);
    });

    $('#sequence-suspicion-level').on('change', function(e) {
        var $field = $(e.currentTarget);

        $('#sequence-suspicion-level').attr('class', 'suspicion-level-' + $field.val());

        OCP.AppConfig.setValue('ransomware_detection', 'suspicion_level', $field.val());
    });
});
