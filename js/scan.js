/**
 * @copyright Copyright (c) 2018 Matthias Held <matthias.held@uni-konstanz.de>
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

    /**
     * @class OCA.RansomwareDetection.Scan
     */
    var Scan = function($el, options) {
        this.initialize($el, options);
    };

    /**
     * @memberof OCA.RansomwareDetection
     */
    Scan.prototype = {
        id: 'ransomware_detection',
        appName: t('ransomware_detection', 'Ransomware Detection'),
        $el: null,
        $section: null,
        $table: null,
        $fileList: null,
        debug: 0,
        filesToScan: {},
        sequencesToScan: {},

        /**
		 * Map of file id to file data
		 * @type Object.<int, Object>
		 */
		_selectedFiles: {},

        /**
		 * Map of files in the current folder.
		 * The entries are of file data.
		 *
		 * @type Object.<int, Object>
		 */
		files: {},

        /**
         * Initialize the file list and its components
         */
        initialize: function($el, options) {
            var self = this;
            options = options || {};
            if (this.initialized) {
                return;
            }
            this.$el = $el;
            if (options.id) {
				this.id = options.id;
			}

            this.filesUrl = '/ocs/v2.php/apps/' + this.id + '/api/v1/files-to-scan';
            this.recoveryUrl = '/ocs/v2.php/apps/' + this.id + '/api/v1/scan-recover';
            this.scanSequenceUrl = '/ocs/v2.php/apps/' + this.id + '/api/v1/scan-sequence';
            this.getColorModeUrl = '/ocs/v2.php/apps/' + this.id + '/api/v1/get-color-mode';
            this.getDebugModeUrl = '/ocs/v2.php/apps/' + this.id + '/api/v1/get-debug-mode';
            this.$container = options.scrollContainer || $(window);
            this.$section = {};
            this.$table = {};
            this.$fileList = {};

            $.getJSON(self.getDebugModeUrl, function(debug) {
                if (debug.debugMode == 1) {
                    self.debug = 1;
                }
                $.getJSON(self.filesUrl, function(data) {
                    $('#section-loading').remove();
                    console.log("Scanned " + data.number_of_files + " files in " + data.scan_duration + " seconds.");
                    self.$el.append(self._createScanHeader(data.sequences.length));
                    self.sequencesToScan = data.sequences;
                });
            });

            this.$el.on('click', '.start-scan', _.bind(this._onClickStartScan, this));
            this.$el.on('change', 'td.selection>.selectCheckBox', _.bind(this._onClickFileCheckbox, this));
            this.$el.on('click', '.select-all', _.bind(this._onClickSelectAll, this));
            this.$el.on('click', '.recover-selected', _.bind(this._onClickRecover, this));
        },

        /**
         * Destroy this instance
         */
        destroy: function() {
            OC.Plugins.detach('OCA.RansomwareDetection.FileList', this);
        },

        /**
		 * Event handler for when selecting/deselecting all files
		 */
		_onClickSelectAll: function(e) {
            var self = this;

			var checked = $(e.target).prop('checked');
			this.$fileList[$(e.target).data('sequence')].find('td.selection>.selectCheckBox').prop('checked', checked)
				.closest('tr').toggleClass('selected', checked);
			this._selectedFiles = {};
			if (checked) {
                Object.keys(this.files[$(e.target).data('sequence')]).forEach(function(key) {
                    var fileData = self.files[$(e.target).data('sequence')][key];
					self._selectedFiles[fileData.id] = fileData;
                });
			}
			this.updateSelectionSummary($(e.target).data('sequence'));
		},

        /**
		 * Event handler for when clicking on a file's checkbox
		 */
		_onClickFileCheckbox: function(e) {
            var $tr = $(e.target).closest('tr');
            var state = !$tr.hasClass('selected');
            var fileData = this.files[$tr.data('sequence')][$tr.data('id')];
            if (state) {
                $tr.addClass('selected');
                this._selectedFiles[fileData.id] = fileData;
            } else {
                $tr.removeClass('selected');
                delete this._selectedFiles[fileData.id];
            }
			this.updateSelectionSummary($tr.data('sequence'));
		},

        /**
         * Create the App header.
         */
        _createScanHeader: function(numberOfSequences) {
            if (this.debug == 1) {
                header = $('<div class="section"><div class="pull-right"><span><a class="action" href="/ocs/v2.php/apps/ransomware_detection/api/v1/export"><span class="icon icon-download"></span>' + t('ransomware_detection', 'Export data') + '</a></span></div></div>');
            } else {
                header = $('<div class="section scan-header"><a href="#" class="button start-scan primary" data-original-title="" title=""><span>Start scan</span></a><div class="pull-right"><span>Sequences scanned: </span><span id="scanned">0</span>/<span id="total-files">' + numberOfSequences + '</span></div>')
            }
            return header;
        },

        /**
         * Event handler to recover files
         */
        _onClickRecover: function(e) {
            var self = this;

            var sequence = $(e.target).parent().data('sequence');
            var numberOfFiles = Object.keys(self.files[sequence]).length;

            OC.dialogs.confirm(t('ransomware_detection', 'Are your sure you want to recover the selected files?'), t('ransomware_detection', 'Confirmation'), function (e) {
                if (e === true) {
                    $.each(self._selectedFiles, function(index, value) {
                        $.ajax({
                            url: self.recoveryUrl,
                            type: 'POST',
                            contentType: 'application/json',
                            data: JSON.stringify({id: parseInt(index), sequence: sequence, command: parseInt(value.command), path: value.path, name: value.originalName, timestamp: value.timestamp})
                        }).done(function(response) {
                            self.$el.find("tr[data-id='" + response['id'] + "'][data-sequence='" + response['sequence'] + "']").remove();
                            numberOfFiles = numberOfFiles - 1;
                            delete self._selectedFiles[index];
                            if (numberOfFiles === 0) {
                                self.$section[sequence].remove();
                                delete self.$section[sequence];
                            }
                            if (Object.keys(self._selectedFiles).length === 0) {
                                OC.dialogs.alert(t('ransomware_detection', 'All files successfully recovered.'), t('ransomware_detection', 'Success'));
                            }
                            self.updateSelectionSummary(sequence);
                        }).fail(function(response, code) {
                            console.log("Recovery failed.");
                        });
                    });
                }
            });
        },

        /**
         * On click listener for start scan.
         */
        _onClickStartScan: function(e) {
            var self = this;

            self.$el.find('#scan-results').parent().parent().remove();
            self.$el.find('#section-suspicious-files-text').remove();
            self.$el.find(".start-scan span").text("Scan running...");
            self.$el.find(".start-scan").addClass("disabled");
            self.$el.append(self._createNoSuspiciousFilesFound());


            if (self.sequencesToScan.length > 0) {
                var count = 0;
                $.getJSON(self.getColorModeUrl, function(schema) {
                    if (schema.colorMode == 1) {
                        Utils.colors = {red: 'color-blind-red', yellow: 'color-blind-yellow', green: 'color-blind-green'};
                        Utils.colorsText = {red: 'color-blind-red-text', yellow: 'color-blind-yellow-text', green: 'color-blind-green-text'};
                    }
                    $.each(self.sequencesToScan, function(index, sequence) {
                        $.ajax({
                            url: self.scanSequenceUrl,
                            type: 'POST',
                            contentType: 'application/json',
                            data: JSON.stringify({sequence: sequence})
                        }).done(function(response) {
                            count = count + 1;
                            $('#scanned').text(count);
                            if (response.status === "success") {
                                self.$section[index] = self._createSection(index);
                                self.$table[index] = Utils._createTableSkeleton(index, response.suspicionScore);
                                self.$fileList[index] = self.$table[index].find('tbody.file-list');
                                self.files[index] = [];
                                $.each(response.sequence, function(i, file) {
                                    file.id = i;
                                    self.files[index][file.id] = file;
                                    self.$fileList[index].append(Utils._createRow(file, index));
                                    self.$el.find('#section-suspicious-files-text').remove();
                                    self.$el.find('#scan-results').show();
                                });
                                self.$section[index].append(self.$table[index]);
                                self.$el.append(self.$section[index]);
                                self.updateSelectionSummary(index);
                            }
                        }).fail(function(response, code) {
                            console.log("Scan failed.");
                            count = count + 1;
                            $('#scanned').text(count);
                        }).always(function() {
                            if (count >= self.sequencesToScan.length) {
                                self.$el.find(".start-scan span").text("Scan finished");
                            }
                        });
                    });
                });
            }
        },

        /**
         * Creates the section.
         */
        _createSection: function() {
            var section = $('<div class="section group" id="section-results"></div>');
            return section;
        },

        /**
         * All files recovered text.
         */
        _createAllFilesRecovered: function() {
            var text = $('<div class="section"><h2>' + t('ransomware_detection', 'All files successfully recovered.') + '</h2></div>');
            return text;
        },

        /**
         * No suspicious files found text.
         */
        _createNoSuspiciousFilesFound: function() {
            var text = $('<div class="section" id="section-suspicious-files-text"><h2>' + t('ransomware_detection', 'No suspicious files found.') + '</h2></div>');
            return text;
        },

        /**
         * Update UI based on the current selection
         */
        updateSelectionSummary: function(sequence) {
        	if (Object.keys(this._selectedFiles).length === 0) {
        		this.$el.find('.selected-actions').css('display', 'none');
                this.$el.find('.detected').css('display', 'block');
                this.$el.find('.name').text(t('ransomware_detection', 'Name')).removeClass('bold');
                this.$el.find('.hide-selected').css('color', '#999');
        	}
        	else {
        		this.$table[sequence].find('.selected-actions').css('display', 'block');
                this.$table[sequence].find('.detected').css('display', 'none');
                if (Object.keys(this._selectedFiles).length > 1) {
                    this.$table[sequence].find('.name').text(t('ransomware_detection', '{files} files', {files: Object.keys(this._selectedFiles).length})).addClass('bold');
                } else {
                    this.$table[sequence].find('.name').text(t('ransomware_detection', '{files} file', {files: Object.keys(this._selectedFiles).length})).addClass('bold');
                }
                this.$table[sequence].find('.hide-selected').css('color', '#fff');
            }
        }
    };

    OCA.RansomwareDetection.Scan = Scan;
})();

$(document).ready(function() {});
