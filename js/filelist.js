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
        appName: t('ransomware_detection', 'Ransomware Detection'),
        $el: null,
        $container: null,
        $wrapper: null,
        $table: null,
        $fileList: null,
        initialized: false,
        count: 1,
        url: '',
        debug: 0,
        colors: {red: 'red', orange: 'orange', yellow: 'yellow', green: 'green'},
        colorsText: {red: 'red-text', orange: 'orange-text', yellow: 'yellow-text', green: 'green-text'},

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
                if (debug.debug_mode == 1) {
                    console.log('Debug mode active.');
                    self.debug = 1;
                }
                $.getJSON(self.getColorModeUrl, function(schema) {
                    if (schema.color_mode == 1) {
                        console.log('Color blind mode active.');
                        self.colors = {red: 'color-blind-red', orange: 'color-blind-orange', yellow: 'color-blind-yellow', green: 'color-blind-green'};
                        self.colorsText = {red: 'color-blind-red-text', orange: 'color-blind-orange', yellow: 'color-blind-yellow-text', green: 'color-blind-green-text'};
                    }
                    $.getJSON(self.url, function(data) {
                        console.log("Create app header.");
                        $('#section-loading').remove();
                        if (self.debug === 1) {
                            self.$el.append(self._createAppHeader());
                        }
                        if (data.length === 0) {
                            console.log("No sequence found.");
                            self.$el.append(self._createNoSequenceFound());
                        }
                        $.each(data, function(i, sequence) {
                            console.log("New sequence.");
                            console.log("Create new section.");
                            self.$section[sequence.id] = self._createSection(sequence.id);
                            console.log("Create new label.");
                            self.$section[sequence.id].append(self._createHeader(sequence.id, sequence.suspicionScore, OC.Util.formatDate(parseInt(sequence.sequence[0].timestamp, 10) * 1000)));
                            console.log("Create table skeleton.");
                            self.$table[sequence.id] = self._createTableSkeleton(sequence.id, sequence.suspicionScore);
                            self.files[sequence.id] = [];
                            $.each(sequence.sequence, function(i, file) {
                                self.files[sequence.id][file.id] = file;
                                self.$fileList[sequence.id] = self.$table[sequence.id].find('tbody.file-list');
                                if (file.type === 'file') {
                                    self.$fileList[sequence.id].append(self._createFileRow(file));
                                } else {
                                    self.$fileList[sequence.id].append(self._createFolderRow(file));
                                }
                            });
                            console.log("Add new table.");
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
            console.log("Sequence: " + $(e.target).data('sequence'));
			this.$fileList[$(e.target).data('sequence')].find('td.selection>.selectCheckBox').prop('checked', checked)
				.closest('tr').toggleClass('selected', checked);
			this._selectedFiles = {};
			if (checked) {
                console.log("Target is checked.");
                Object.keys(this.files[$(e.target).data('sequence')]).forEach(function(key) {
                    console.log("Add " + key + " to selected files.");
                    var fileData = self.files[$(e.target).data('sequence')][key];
                    console.log(fileData);
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
                    console.log("Delete sequence: " + sequence + ".");
                    $.getJSON(self.deleteUrl + "/" + sequence, function(data) {
                        self.$section[sequence].remove();
                        delete self.$section[sequence];
                        if (Object.keys(self.$section).length === 0) {
                            console.log("No sequence found.");
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

            console.log("Recover files from sequence " + sequence + " with " + numberOfFiles + " files.");

            OC.dialogs.confirm(t('ransomware_detection', 'Are your sure you want to recover the selected files?'), t('ransomware_detection', 'Confirmation'), function (e) {
                if (e === true) {
                    $.each(self._selectedFiles, function(index, value) {
                        console.log(JSON.stringify({id: parseInt(index)}));
                        $.ajax({
                            url: self.recoveryUrl,
                            type: 'POST',
                            contentType: 'application/json',
                            data: JSON.stringify({id: parseInt(index)})
                        }).done(function(response) {
                            console.log("Recovery was a success.");
                            console.log(response['id']);
                            self.$el.find("tr[data-id='" + response['id'] + "']").remove();
                            numberOfFiles = numberOfFiles - 1;
                            delete self._selectedFiles[index];
                            if (numberOfFiles === 0) {
                                self.$section[sequence].remove();
                                delete self.$section[sequence];
                                if (Object.keys(self.$section).length === 0) {
                                    console.log("No sequence found.");
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
            console.log('File selected.');
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
         * Creates a new table skeleton.
         */
        _createTableSkeleton: function(sequence, suspicionScore) {
            var color = this.colors.green;
            if (suspicionScore >= 6) {
                color = this.colors.red;
            } else if (suspicionScore >= 5) {
                color = this.colors.orange;
            } else if (suspicionScore >= 3) {
                color = this.colors.yellow;
            }
            var table =
                $('<div class="row">' +
                    '<div class="sequence-color"><div class="color-box ' + color + '"></div></div>' +
                    '<div class="sequence-table"><table class="ransomware-files" data-sequence="' + sequence + '"><thead>' +
                    '<th><input type="checkbox" data-sequence="' + sequence + '" id="select_all_files_' + sequence + '" class="select-all checkbox"/>' +
                    '<label for="select_all_files_' + sequence + '"><span class="hidden-visually">' + t('ransomware_detection', 'Select all') + '</span></label></th>' +
                    '<th><a class="column-title name">' + t('ransomware_detection', 'Name') + '</a></th>' +
                    '<th><a class="column-title hide-selected"><p>' + t('ransomware_detection', 'Operation') + '</p></a></th>' +
                    '<th><a class="column-title hide-selected"><p>' + t('ransomware_detection', 'Size') + '</p></a></th>' +
                    '<th><a class="column-title hide-selected"><p>' + t('ransomware_detection', 'File class') + '</p></a></th>' +
                    '<th><a class="column-title hide-selected"><p>' + t('ransomware_detection', 'File name class') + '</p></a></th>' +
                    '<th class="controls"><a class="column-title detected">' + t('ransomware_detection', 'Time') + '</a><span class="column-title selected-actions"><a class="recover-selected" data-sequence="' + sequence + '"><span class="icon icon-history"></span><span>' + t('ransomware_detection', 'Recover') + '</span></a></span></th> ' +
                    '</thead><tbody class="file-list"></tbody><tfoot></tfoot></table></div>' +
                '</div>');
            return table;
        },

        /**
         * Creates a new row in the table.
         */
        _createFileRow: function(fileData) {
            var self = this;
            var td, tr = $('<tr data-id="' + fileData.id + '" data-sequence="' + fileData.sequence + '"></tr>'),
                mtime = parseInt(fileData.timestamp, 10) * 1000,
                basename, extension, simpleSize, sizeColor;

            if (isNaN(mtime)) {
    			mtime = new Date().getTime();
    		}

            // size
            if (typeof(fileData.size) !== 'undefined' && fileData.size >= 0) {
				simpleSize = humanFileSize(parseInt(fileData.size, 10), true);
				sizeColor = Math.round(160-Math.pow((fileData.size/(1024*1024)),2));
			} else {
				simpleSize = t('ransomware_detection', 'Pending');
			}

            td = $('<td class="selection"></td>');
            td.append(
				'<input id="select-' + this.id + '-' + fileData.id +
				'" type="checkbox" class="selectCheckBox checkbox"/><label for="select-' + this.id + '-' + fileData.id + '">' +
				'<span class="hidden-visually">' + t('ransomware_detection', 'Select') + '</span>' +
				'</label>'
			);
            tr.append(td);

            var nameWrapper = $('<div class="name-wrapper"></div>');
            nameWrapper.append('<div class="thumbnail-wrapper"><div class="thumbnail" style="background-image:url(' + OC.MimeType.getIconUrl(fileData.type) + ');"></div></div>');

            // file name
            filename = fileData.originalName;
            if (fileData.command === 2) {
                // file was renamed use new name
                filename = fileData.newName
            }

            if (filename !== null) {
                if (filename.indexOf('.') === 0) {
    				basename = '';
    				extension = name;
                } else {
                    basename = filename.substr(0, filename.lastIndexOf('.'));
    				extension = filename.substr(filename.lastIndexOf('.'));
                }

                var nameSpan = $('<span></span>').addClass('name-text');
    			var innernameSpan = $('<span></span>').addClass('inner-name-text').text(basename);

                nameSpan.append(innernameSpan);

                if (extension) {
    				nameSpan.append($('<span></span>').addClass('extension').text(extension));
    			}
            } else {
               nameSpan = $('<span></span>').addClass('name-text');
               innernameSpan = $('<span></span>').addClass('inner-name-text').text(t('ransomware_detection', 'Not found.'));

               nameSpan.append(innernameSpan);
           }

           nameWrapper.append(nameSpan);

            td = $('<td class="file-name"></td>');
            td.append(nameWrapper);
            tr.append(td);

            if (fileData.command === 1) {
                // delete
                td = $('<td></td>').append($('<p></p>').attr({"title": "DELETE"}).tooltip({placement: 'top'}).prepend('<span class="fas fa-trash-alt fa-fw"></span>'));
            } else if (fileData.command === 2) {
                // rename
                    td = $('<td></td>').append($('<p></p>').attr({"title": "RENAME"}).tooltip({placement: 'top'}).prepend('<span class="fas fa-font fa-fw"></span>'));
            } else if (fileData.command === 3) {
                // write
                td = $('<td></td>').append($('<p></p>').attr({"title": "WRITE"}).tooltip({placement: 'top'}).prepend('<span class="fas fa-pencil-alt fa-fw"></span>'));
            } else if (fileData.command === 4) {
                // read
                td = $('<td></td>').append($('<p></p>').attr({"title": "READ"}).tooltip({placement: 'top'}).prepend('<span class="fas fa-book fa-fw"></span>'));
            } else if (fileData.command === 5) {
                // create
                td = $('<td></td>').append($('<p></p>').attr({"title": "CREATE"}).tooltip({placement: 'top'}).prepend('<span class="fas fa-pencil-alt fa-fw"></span>'));
            } else {
                // error
                td = $('<td></td>').append($('<p></p>').attr({"title": "ERROR"}).tooltip({placement: 'top'}).prepend('<span class="fas fa-times fa-fw"></span>'));
            }
            tr.append(td);

            // size
            if (typeof(fileData.size) !== 'undefined' && fileData.size >= 0) {
                simpleSize = humanFileSize(parseInt(fileData.size, 10), true);
                sizeColor = Math.round(120-Math.pow((fileData.size/(1024*1024)),2));
            } else {
                simpleSize = t('ransomware_detection', 'Pending');
            }

            td = $('<td></td>').append($('<p></p>').attr({
				"class": "filesize"
			}).text(simpleSize));
            tr.append(td);

            if (fileData.fileClass === 1) {
                // encrypted
                td = $('<td></td>').append($('<p></p>').attr({"title": "ENCRYPTED"}).tooltip({placement: 'top'}).prepend('<span class="fas fa-lock fa-fw"></span>'));
            } else if (fileData.fileClass === 2) {
                // compressed
                    td = $('<td></td>').append($('<p></p>').attr({"title": "COMPRESSED"}).tooltip({placement: 'top'}).prepend('<span class="fas fa-file-archive fa-fw"></span>'));
            } else if (fileData.fileClass === 3) {
                // normal
                td = $('<td></td>').append($('<p></p>').attr({"title": "NORMAL"}).tooltip({placement: 'top'}).prepend('<span class="fas fa-file fa-fw"></span>'));
            } else {
                // error
                td = $('<td></td>').append($('<p></p>').attr({"title": "ERROR"}).tooltip({placement: 'top'}).prepend('<span class="fas fa-times fa-fw"></span>'));
            }
            tr.append(td);

            if (fileData.fileExtensionClass === 0) {
                // normal
                td = $('<td></td>').append($('<p></p>').attr({"title": "NOT SUSPICIOUS"}).tooltip({placement: 'top'}).prepend('<span class="fas fa-check-circle fa-fw"></span>'));
            } else if (fileData.fileExtensionClass === 1) {
                // suspicious
                td = $('<td></td>').append($('<p></p>').attr({"title": "SUSPICIOUS"}).tooltip({placement: 'top'}).prepend('<span class="fas fa-exclamation-triangle fa-fw"></span>'));
            } else {
                // error
                td = $('<td></td>').append($('<p></p>').attr({"title": "ERROR"}).tooltip({placement: 'top'}).prepend('<span class="fas fa-times fa-fw"></span>'));
            }
            tr.append(td);

            // date column (1000 milliseconds to seconds, 60 seconds, 60 minutes, 24 hours)
			// difference in days multiplied by 5 - brightest shade for files older than 32 days (160/5)
			var modifiedColor = Math.round(((new Date()).getTime() - mtime )/1000/60/60/24*5 );
			// ensure that the brightest color is still readable
			if (modifiedColor >= '160') {
				modifiedColor = 160;
			}
            var formatted;
			var text;
			if (mtime > 0) {
				formatted = OC.Util.formatDate(mtime);
				text = OC.Util.relativeModifiedDate(mtime);
			} else {
				formatted = t('ransomware_detection', 'Unable to determine date');
				text = '?';
			}

            td = $('<td></td>').attr({ "class": "date" });
            td.append($('<span></span>').attr({
				"class": "modified live-relative-timestamp",
				"title": formatted,
				"data-timestamp": mtime,
				"style": 'color:rgb('+modifiedColor+','+modifiedColor+','+modifiedColor+')'
			}).text(text)
			  .tooltip({placement: 'top'})
			);
            tr.append(td);

            // Color row according to suspicion level
            if (fileData.suspicionClass === 3) {
                tr.attr({ 'class': self.colors.red});
            } else if (fileData.suspicionClass === 2) {
                tr.attr({ 'class': self.colors.yellow});
            } else if (fileData.suspicionClass === 1) {
                tr.attr({ 'class': self.colors.green});
            }

            return tr;
        },

        /**
         * Creates a new row in the table.
         */
        _createFolderRow: function(fileData) {
            var self = this;
            var td, tr = $('<tr data-id="' + fileData.id + '" data-sequence="' + fileData.sequence + '"></tr>'),
                mtime = parseInt(fileData.timestamp, 10) * 1000,
                basename, extension, simpleSize, sizeColor;

            if (isNaN(mtime)) {
    			mtime = new Date().getTime();
    		}

            // size
            if (typeof(fileData.size) !== 'undefined' && fileData.size >= 0) {
				simpleSize = humanFileSize(parseInt(fileData.size, 10), true);
				sizeColor = Math.round(160-Math.pow((fileData.size/(1024*1024)),2));
			} else {
				simpleSize = t('ransomware_detection', 'Pending');
			}

            td = $('<td class="selection"></td>');
            td.append(
				'<input id="select-' + this.id + '-' + fileData.id +
				'" type="checkbox" class="selectCheckBox checkbox"/><label for="select-' + this.id + '-' + fileData.id + '">' +
				'<span class="hidden-visually">' + t('ransomware_detection', 'Select') + '</span>' +
				'</label>'
			);
            tr.append(td);

            var nameWrapper = $('<div class="name-wrapper"></div>');
            nameWrapper.append('<div class="thumbnail-wrapper"><div class="thumbnail" style="background-image:url(' + OC.MimeType.getIconUrl(fileData.type) + ');"></div></div>');

            // file name
            filename = fileData.originalName;
            if (fileData.command === 2) {
                filename = fileData.newName
            }

            if (filename !== null) {

                var nameSpan = $('<span></span>').addClass('name-text');
    			var innernameSpan = $('<span></span>').addClass('inner-name-text').text(filename);

                nameSpan.append(innernameSpan);
            } else {
               nameSpan = $('<span></span>').addClass('name-text');
               innernameSpan = $('<span></span>').addClass('inner-name-text').text(t('ransomware_detection', 'Not found.'));

               nameSpan.append(innernameSpan);
           }

           nameWrapper.append(nameSpan);

           td = $('<td class="file-name"></td>');
           td.append(nameWrapper);
           tr.append(td);

           if (fileData.command === 1) {
                // delete
                td = $('<td></td>').append($('<p></p>').attr({"title": "DELETE"}).tooltip({placement: 'top'}).prepend('<span class="fas fa-trash-alt fa-fw"></span>'));
            } else if (fileData.command === 2) {
                // rename
                    td = $('<td></td>').append($('<p></p>').attr({"title": "RENAME"}).tooltip({placement: 'top'}).prepend('<span class="fas fa-font fa-fw"></span>'));
            } else if (fileData.command === 3) {
                // write
                td = $('<td></td>').append($('<p></p>').attr({"title": "WRITE"}).tooltip({placement: 'top'}).prepend('<span class="fas fa-pencil-alt fa-fw"></span>'));
            } else if (fileData.command === 4) {
                // read
                td = $('<td></td>').append($('<p></p>').attr({"title": "READ"}).tooltip({placement: 'top'}).prepend('<span class="fas fa-book fa-fw"></span>'));
            } else if (fileData.command === 5) {
                // create
                td = $('<td></td>').append($('<p></p>').attr({"title": "CREATE"}).tooltip({placement: 'top'}).prepend('<span class="fas fa-plus-square fa-fw"></span>'));
            } else {
                // error
                td = $('<td></td>').append($('<p></p>').attr({"title": "ERROR"}).tooltip({placement: 'top'}).prepend('<span class="fas fa-times fa-fw"></span>'));
            }
            tr.append(td);

            // size
            if (typeof(fileData.size) !== 'undefined' && fileData.size >= 0) {
                simpleSize = humanFileSize(parseInt(fileData.size, 10), true);
                sizeColor = Math.round(120-Math.pow((fileData.size/(1024*1024)),2));
            } else {
                simpleSize = t('ransomware_detection', 'Pending');
            }

            td = $('<td></td>').append($('<p></p>').attr({
				"class": "filesize"
			}).text(simpleSize));
            tr.append(td);

            if (fileData.fileClass === 1) {
                // encrypted
                td = $('<td></td>').append($('<p></p>').attr({"title": "ENCRYPTED"}).tooltip({placement: 'top'}).prepend('<span class="fas fa-lock fa-fw"></span>'));
            } else if (fileData.fileClass === 2) {
                // compressed
                    td = $('<td></td>').append($('<p></p>').attr({"title": "COMPRESSED"}).tooltip({placement: 'top'}).prepend('<span class="fas fa-file-archive fa-fw"></span>'));
            } else if (fileData.fileClass === 3) {
                // normal
                td = $('<td></td>').append($('<p></p>').attr({"title": "NORMAL"}).tooltip({placement: 'top'}).prepend('<span class="fas fa-file fa-fw"></span>'));
            } else {
                // error
                td = $('<td></td>').append($('<p></p>').attr({"title": "ERROR"}).tooltip({placement: 'top'}).prepend('<span class="fas fa-times fa-fw"></span>'));
            }
            tr.append(td);

            if (fileData.fileExtensionClass === 0) {
                // not suspicious
                td = $('<td></td>').append($('<p></p>').attr({"title": "NOT_SUSPICIOUS"}).tooltip({placement: 'top'}).prepend('<span class="fas fa-check-circle fa-fw"></span>'));
            } else if (fileData.fileExtensionClass === 1) {
                // suspicious
                td = $('<td></td>').append($('<p></p>').attr({"title": "SUSPICIOUS"}).tooltip({placement: 'top'}).prepend('<span class="fas fa-exclamation-triangle fa-fw"></span>'));
            } else {
                // error
                td = $('<td></td>').append($('<p></p>').attr({"title": "ERROR"}).tooltip({placement: 'top'}).prepend('<span class="fas fa-times fa-fw"></span>'));
            }
            tr.append(td);

            // date column (1000 milliseconds to seconds, 60 seconds, 60 minutes, 24 hours)
			// difference in days multiplied by 5 - brightest shade for files older than 32 days (160/5)
			var modifiedColor = Math.round(((new Date()).getTime() - mtime )/1000/60/60/24*5 );
			// ensure that the brightest color is still readable
			if (modifiedColor >= '160') {
				modifiedColor = 160;
			}
            var formatted;
			var text;
			if (mtime > 0) {
				formatted = OC.Util.formatDate(mtime);
				text = OC.Util.relativeModifiedDate(mtime);
			} else {
				formatted = t('ransomware_detection', 'Unable to determine date');
				text = '?';
			}

            td = $('<td></td>').attr({ "class": "date" });
            td.append($('<span></span>').attr({
				"class": "modified live-relative-timestamp",
				"title": formatted,
				"data-timestamp": mtime,
				"style": 'color:rgb('+modifiedColor+','+modifiedColor+','+modifiedColor+')'
			}).text(text)
			  .tooltip({placement: 'top'})
			);
            tr.append(td);

            // Color row according to suspicion level
            if (fileData.suspicionClass === 3) {
                tr.attr({ 'class': self.colors.red});
            } else if (fileData.suspicionClass === 2) {
                tr.attr({ 'class': self.colors.yellow});
            } else if (fileData.suspicionClass === 1) {
                tr.attr({ 'class': self.colors.green});
            }

            return tr;
        },

        /**
         * Update UI based on the current selection
         */
        updateSelectionSummary: function(sequence) {
        	if (Object.keys(this._selectedFiles).length === 0) {
                console.log("No files selected.");
        		this.$el.find('.selected-actions').css('display', 'none');
                this.$el.find('.detected').css('display', 'block');
                this.$el.find('.name').text(t('ransomware_detection', 'Name')).removeClass('bold');
                this.$el.find('.hide-selected').css('color', '#999');
        	}
        	else {
                console.log(Object.keys(this._selectedFiles).length + " files selected.");
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
