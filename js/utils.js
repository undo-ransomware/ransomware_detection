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

/** global: OC */
 (function() {

     /**
      * @class OCA.RansomwareDetection.FileList
      */
     var Utils = function() {
     };

     /**
      * @memberof OCA.RansomwareDetection
      */
     Utils.prototype = {
         colors: {red: 'red', yellow: 'yellow', green: 'green'},
         colorsText: {red: 'red-text', yellow: 'yellow-text', green: 'green-text'},

         /**
          * Creates a new row in the table.
          */
         _createRow: function(fileData) {
             var self = this;
             var td, tr = $('<tr data-id="' + fileData.id + '" data-sequence="' + fileData.sequence + '"></tr>'),
                 mtime = parseInt(fileData.timestamp, 10) * 1000,
                 basename, extension, simpleSize, filename;

             if (isNaN(mtime)) {
     			mtime = new Date().getTime();
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

             var nameSpan, innernameSpan;

             if (filename !== null) {
                 if (fileData.type === 'file') {
                     if (filename.indexOf('.') === 0) {
         				 basename = '';
         				 extension = filename;
                     } else {
                         basename = filename.substr(0, filename.lastIndexOf('.'));
         				 extension = filename.substr(filename.lastIndexOf('.'));
                     }

                     nameSpan = $('<span></span>').addClass('name-text');
         			 innernameSpan = $('<span></span>').addClass('inner-name-text').text(basename);

                     nameSpan.append(innernameSpan);

                     if (extension) {
         				nameSpan.append($('<span></span>').addClass('extension').text(extension));
         			}
                } else {
                    nameSpan = $('<span></span>').addClass('name-text');
                    innernameSpan = $('<span></span>').addClass('inner-name-text').text(filename);

                    nameSpan.append(innernameSpan);
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
                 td = $('<td></td>').append($('<p></p>').attr({"title": "CREATE"}).tooltip({placement: 'top'}).prepend('<span class="fas fa-plus-circle fa-fw"></span>'));
             } else {
                 // error
                 td = $('<td></td>').append($('<p></p>').attr({"title": "ERROR"}).tooltip({placement: 'top'}).prepend('<span class="fas fa-times fa-fw"></span>'));
             }
             tr.append(td);

             // size
             if (typeof(fileData.size) !== 'undefined' && fileData.size >= 0) {
                 simpleSize = filesize(parseInt(fileData.size, 10), true);
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
          * Creates a new table skeleton.
          */
         _createTableSkeleton: function(sequence, suspicionScore) {
             var color = this.colors.green;
             if (suspicionScore > 4) {
                 color = this.colors.red;
             } else if (suspicionScore > 2) {
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
     };

     OCA.RansomwareDetection.Utils = Utils;
 })();

 $(document).ready(function() {

 });
