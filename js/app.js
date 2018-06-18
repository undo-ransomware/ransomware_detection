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

/* global OC */
(function() {

    if (!OCA.RansomwareDetection) {
        /**
         * Namespace for the ransomware detection app
         * @namespace OCA.RansomwareDetection
         */
        OCA.RansomwareDetection = {};
    }

    /**
     * @namespace OCA.RansomwareDetection.App
     */
    OCA.RansomwareDetection.App = {
        /**
         * File list for the "Ransomware detection" section
         *
         * @member {OCA.RansomwareDetection.FileList}
         */
        fileList: null,

        /**
         * Initializes the ransomware detection app
         */
        initialize: function() {
            this.fileList = new OCA.RansomwareDetection.FileList(
                $('#app-content-ransomware-detection'), {}
            );
            window.FileList = this.fileList;

            OC.Plugins.attach('OCA.RansomwareDetection.App', this);
        },

        /**
         * Destroy the app
         */
        destroy: function() {
            this.fileList.destroy();
            this.fileList = null;
        }
    }
})();

$(document).ready(function() {
    _.defer(function() {
        OCA.RansomwareDetection.App.initialize();
    });
});
