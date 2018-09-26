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
/* global Utils */
(function() {

    /**
     * @class OCA.RansomwareDetection.FileList
     */
    var FileList = function($el, options) {
        this.initialize($el, options);
    };

    /**
     * @memberof OCA.RansomwareDetection
     */
    FileList.prototype = {
        id: 'ransomware_detection',
        appName: t('ransomware_detection', 'Ransomware Recovery'),
        $el: null,
        $container: null,
        $wrapper: null,
        $table: null,
        $fileList: null,
        initialized: false,
        count: 1,
        url: '',
        debug: 0,

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
            this.url = '/ocs/v2.php/apps/' + this.id + '/api/v1/list';
            this.deleteUrl = '/ocs/v2.php/apps/' + this.id + '/api/v1/delete-sequence';
            this.recoveryUrl = '/ocs/v2.php/apps/' + this.id + '/api/v1/recover';
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
                $.getJSON(self.getColorModeUrl, function(schema) {
                    if (schema.colorMode == 1) {
                        Utils.colors = {red: 'color-blind-red', yellow: 'color-blind-yellow', green: 'color-blind-green'};
                        Utils.colorsText = {red: 'color-blind-red-text', yellow: 'color-blind-yellow-text', green: 'color-blind-green-text'};
                    }
                    $.getJSON(self.url, function(data) {
                        $('#section-loading').remove();
                        if (self.debug === 1) {
                            self.$el.append(self._createAppHeader());
                        }
                        if (data.length === 0) {
                            self.$el.append(self._createNoSequenceFound());
                        }
                        $.each(data, function(i, sequence) {
                            self.$section[sequence.id] = self._createSection(sequence.id);
                            self.$section[sequence.id].append(self._createHeader(sequence.id, sequence.suspicionScore, OC.Util.formatDate(parseInt(sequence.sequence[0].timestamp, 10) * 1000)));
                            self.$table[sequence.id] = Utils._createTableSkeleton(sequence.id, sequence.suspicionScore);
                            self.files[sequence.id] = [];
                            $.each(sequence.sequence, function(i, file) {
                                self.files[sequence.id][file.id] = file;
                                self.$fileList[sequence.id] = self.$table[sequence.id].find('tbody.file-list');
                                self.$fileList[sequence.id].append(Utils._createRow(file));
                            });
                            self.$section[sequence.id].append(self.$table[sequence.id]);
                            self.$el.append(self.$section[sequence.id]);
                        });

                        self.updateSelectionSummary();
                    });
                });
            });
            this.$el.on('change', 'td.selection>.selectCheckBox', _.bind(this._onClickFileCheckbox, this));
            this.$el.on('click', '.select-all', _.bind(this._onClickSelectAll, this));
            this.$el.on('click', '.sequence-delete', _.bind(this._onClickDeleteSequence, this));
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
         * Event handler for deleting a sequence
         */
        _onClickDeleteSequence: function(e) {
            var self = this;

            var sequence = $(e.target).parent().data('sequence');
            OC.dialogs.confirm(t('ransomware_detection', 'Are your sure you want to delete the sequence?'), t('ransomware_detection', 'Confirmation'), function (e) {
                if (e === true) {
                    $.getJSON(self.deleteUrl + "/" + sequence, function(data) {
                        self.$section[sequence].remove();
                        delete self.$section[sequence];
                        if (Object.keys(self.$section).length === 0) {
                            self.$el.append(self._createNoSequenceFound());
                        }
                    });
                }
            });
        },

        /**
         * Event handler to recover files
         */
        _onClickRecover: function(e) {
            var self = this;
            var sequence = $(e.target).parent().data('sequence');
            var numberOfFiles = Object.keys(this.files[sequence]).length;
            var $tr = $(e.target).closest('tr');

            OC.dialogs.confirm(t('ransomware_detection', 'Are your sure you want to recover the selected files?'), t('ransomware_detection', 'Confirmation'), function (e) {
                if (e === true) {
                    $.each(self._selectedFiles, function(index, value) {
                        $.ajax({
                            url: self.recoveryUrl,
                            type: 'POST',
                            contentType: 'application/json',
                            data: JSON.stringify({id: parseInt(index)})
                        }).done(function(response) {
                            self.$el.find("tr[data-id='" + response['id'] + "']").remove();
                            numberOfFiles = numberOfFiles - 1;
                            delete self._selectedFiles[index];
                            if (numberOfFiles === 0) {
                                self.$section[sequence].remove();
                                delete self.$section[sequence];
                                if (Object.keys(self.$section).length === 0) {
                                    self.$el.append(self._createNoSequenceFound());
                                }
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
         * Creates the App header.
         */
        _createAppHeader: function() {
            header = $('<div class="section"><div class="pull-right"><span><a class="action" href="/ocs/v2.php/apps/ransomware_detection/api/v1/export"><span class="icon icon-download"></span>' + t('ransomware_detection', 'Export data') + '</a></span></div></div>');
            return header;
        },

        /**
         * Creates the no sequence found text.
         */
        _createNoSequenceFound: function() {
            var text = $('<div class="section"><h2>' + t('ransomware_detection', 'No sequences found.') + '</h2></div>');
            return text;
        },

        /**
         * Creates the section.
         */
        _createSection: function(sequence) {
            var section = $('<div class="section group" data-sequence="' + sequence + '"></div>');
            return section;
        },

        /**
         * Creates the header.
         */
        _createHeader: function(sequence, suspicionScore, timestamp) {
            var self = this;
            var label = $('<div class="row" data-sequence="' + sequence + '"><h2 class="sequence-header pull-left">' + timestamp + '</h2>');
            if (self.debug == 1) {
                label = $('<div class="row" data-sequence="' + sequence + '"><h2 class="sequence-header pull-left">' + timestamp + '</h2><a class="sequence-action sequence-delete pull-right" data-sequence="' + sequence + '"><span class="icon icon-delete"></span><span>' + t('ransomware_detection', 'Delete') + '</span></div></a>');
            }
            $sequenceHeader = label.find('h2.sequence-header');
            this.count++;
            return label;
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

    OCA.RansomwareDetection.FileList = FileList;
})();

$(document).ready(function() {

});
