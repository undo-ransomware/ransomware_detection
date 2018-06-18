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

namespace OCA\RansomwareDetection;

use OCA\RansomwareDetection\Analyzer\EntropyResult;

class FileSignatureList
{
    /**
     * Signature definition.
     *
     * @var array
     */
    private static $signatures = [
        ['byteSequence' => '00001A00051004', 'offset' => '0', 'extension' => ['123'], 'mimeType' => [['extension' => '123', 'mime' => 'application/vnd.lotus-1-2-3']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '4D5A', 'offset' => '0', 'extension' => ['cpl', 'exe', 'dll'], 'mimeType' => [['extension' => 'cpl', 'mime' => 'application/cpl+xml'], ['extension' => 'exe', 'mime' => 'application/octet-stream'], ['extension' => 'dll', 'mime' => 'application/octet-stream']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => 'DCDC', 'offset' => '0', 'extension' => ['cpl'], 'mimeType' => [['extension' => 'cpl', 'mime' => 'application/cpl+xml']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '504B03040A000200', 'offset' => '0', 'extension' => ['epub'], 'mimeType' => [['extension' => 'epub', 'mime' => 'application/epub+zip']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '0001000000', 'offset' => '0', 'extension' => ['ttf'], 'mimeType' => [['extension' => 'ttf', 'mime' => 'application/font-sfnt']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '1F8B08', 'offset' => '0', 'extension' => ['gz', 'tgz'], 'mimeType' => [['extension' => 'gz', 'mime' => 'application/gzip'], ['extension' => 'tgz', 'mime' => 'application/gzip']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '28546869732066696C65206D75737420626520636F6E76657274656420776974682042696E48657820', 'offset' => '0', 'extension' => ['hqx'], 'mimeType' => [['extension' => 'hqx', 'mime' => 'application/mac-binhex40']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '0D444F43', 'offset' => '0', 'extension' => ['doc'], 'mimeType' => [['extension' => 'doc', 'mime' => 'application/msword']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => 'CF11E0A1B11AE100', 'offset' => '0', 'extension' => ['doc'], 'mimeType' => [['extension' => 'doc', 'mime' => 'application/msword']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => 'D0CF11E0A1B11AE1', 'offset' => '0', 'extension' => ['doc', 'apr', 'xls', 'xla', 'ppt', 'pps', 'dot'], 'mimeType' => [['extension' => 'doc', 'mime' => 'application/msword'], ['extension' => 'apr', 'mime' => 'application/vnd.lotus-approach'], ['extension' => 'xls', 'mime' => 'application/vnd.ms-excel'], ['extension' => 'xla', 'mime' => 'application/vnd.ms-excel'], ['extension' => 'ppt', 'mime' => 'application/vnd.ms-powerpoint'], ['extension' => 'pps', 'mime' => 'application/vnd.ms-powerpoint'], ['extension' => 'dot', 'mime' => 'text/vnd.graphviz']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => 'DBA52D00', 'offset' => '0', 'extension' => ['doc'], 'mimeType' => [['extension' => 'doc', 'mime' => 'application/msword']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => 'ECA5C100', 'offset' => '512', 'extension' => ['doc'], 'mimeType' => [['extension' => 'doc', 'mime' => 'application/msword']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '060E2B34020501010D0102010102', 'offset' => '0', 'extension' => ['mxf'], 'mimeType' => [['extension' => 'mxf', 'mime' => 'application/mxf']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '3C435472616E7354696D656C696E653E', 'offset' => '0', 'extension' => ['mxf'], 'mimeType' => [['extension' => 'mxf', 'mime' => 'application/mxf']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '2D6C68', 'offset' => '2', 'extension' => ['lha', 'lzh'], 'mimeType' => [['extension' => 'lha', 'mime' => 'application/octet-stream'], ['extension' => 'lzh', 'mime' => 'application/octet-stream']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => 'CAFEBABE', 'offset' => '0', 'extension' => ['class'], 'mimeType' => [['extension' => 'class', 'mime' => 'application/octet-stream']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '000100005374616E64617264204A6574204442', 'offset' => '0', 'extension' => ['img'], 'mimeType' => [['extension' => 'img', 'mime' => 'application/octet-stream']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '504943540008', 'offset' => '0', 'extension' => ['img'], 'mimeType' => [['extension' => 'img', 'mime' => 'application/octet-stream']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '514649FB', 'offset' => '0', 'extension' => ['img'], 'mimeType' => [['extension' => 'img', 'mime' => 'application/octet-stream']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '53434D49', 'offset' => '0', 'extension' => ['img'], 'mimeType' => [['extension' => 'img', 'mime' => 'application/octet-stream']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '7E742C015070024D52010000000800000001000031000000310000004301FF0001000800010000007e742c01', 'offset' => '0', 'extension' => ['img'], 'mimeType' => [['extension' => 'img', 'mime' => 'application/octet-stream']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => 'EB3C902A', 'offset' => '0', 'extension' => ['img'], 'mimeType' => [['extension' => 'img', 'mime' => 'application/octet-stream']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '4344303031', 'offset' => '0', 'extension' => ['iso'], 'mimeType' => [['extension' => 'iso', 'mime' => 'application/octet-stream']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '4F67675300020000000000000000', 'offset' => '0', 'extension' => ['ogx', 'oga', 'ogg', 'ogv'], 'mimeType' => [['extension' => 'ogx', 'mime' => 'application/ogg'], ['extension' => 'oga', 'mime' => 'audio/ogg'], ['extension' => 'ogg', 'mime' => 'audio/ogg'], ['extension' => 'ogv', 'mime' => 'video/ogg']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '504B0304', 'offset' => '0', 'extension' => ['oxps', 'kmz', 'kwd', 'xps', 'odp', 'odt', 'ott', 'zip', 'sxc', 'sxd', 'sxi', 'sxw', 'jar', 'xpi'], 'mimeType' => [['extension' => 'oxps', 'mime' => 'application/oxps'], ['extension' => 'kmz', 'mime' => 'application/vnd.google-earth.kmz'], ['extension' => 'kwd', 'mime' => 'application/vnd.kde.kword'], ['extension' => 'xps', 'mime' => 'application/vnd.ms-xpsdocument'], ['extension' => 'odp', 'mime' => 'application/vnd.oasis.opendocument.presentation'], ['extension' => 'odt', 'mime' => 'application/vnd.oasis.opendocument.text'], ['extension' => 'ott', 'mime' => 'application/vnd.oasis.opendocument.text-template'], ['extension' => 'zip', 'mime' => 'application/zip'], ['extension' => 'sxc', 'mime' => 'application/vnd.sun.xml.calc'], ['extension' => 'sxd', 'mime' => 'application/vnd.sun.xml.draw'], ['extension' => 'sxi', 'mime' => 'application/vnd.sun.xml.impress'], ['extension' => 'sxw', 'mime' => 'application/vnd.sun.xml.writer'], ['extension' => 'jar', 'mime' => 'application/x-java-archive'], ['extension' => 'xpi', 'mime' => 'application/x-xpinstall']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '25504446', 'offset' => '0', 'extension' => ['pdf', 'ai', 'fdf'], 'mimeType' => [['extension' => 'pdf', 'mime' => 'application/pdf'], ['extension' => 'ai', 'mime' => 'application/postscript'], ['extension' => 'fdf', 'mime' => 'application/vnd.fdf']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '64000000', 'offset' => '0', 'extension' => ['p10'], 'mimeType' => [['extension' => 'p10', 'mime' => 'application/pkcs10']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '5B706C61796C6973745D', 'offset' => '0', 'extension' => ['pls'], 'mimeType' => [['extension' => 'pls', 'mime' => 'application/pls+xml']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '252150532D41646F62652D332E3020455053462D332030', 'offset' => '0', 'extension' => ['eps'], 'mimeType' => [['extension' => 'eps', 'mime' => 'application/postscript']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => 'C5D0D3C6', 'offset' => '0', 'extension' => ['eps'], 'mimeType' => [['extension' => 'eps', 'mime' => 'application/postscript']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '7B5C72746631', 'offset' => '0', 'extension' => ['rtf'], 'mimeType' => [['extension' => 'rtf', 'mime' => 'application/rtf']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '47', 'offset' => '0', 'extension' => ['tsa', 'tsv', 'ts'], 'mimeType' => [['extension' => 'tsa', 'mime' => 'application/tamp-sequence-adjust'], ['extension' => 'tsv', 'mime' => 'text/tab-separated-values'], ['extension' => 'ts', 'mime' => 'text/vnd.trolltech.linguist']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '2F2F203C212D2D203C6D64623A6D6F726B3A7A', 'offset' => '0', 'extension' => ['msf'], 'mimeType' => [['extension' => 'msf', 'mime' => 'application/vnd.epson.msf']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '3C4D616B657246696C6520', 'offset' => '0', 'extension' => ['fm', 'mif'], 'mimeType' => [['extension' => 'fm', 'mime' => 'application/vnd.framemaker'], ['extension' => 'mif', 'mime' => 'application/vnd.mif']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '0020AF30', 'offset' => '0', 'extension' => ['tpl'], 'mimeType' => [['extension' => 'tpl', 'mime' => 'application/vnd.groove-tool-template']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '6D7346696C7465724C697374', 'offset' => '0', 'extension' => ['tpl'], 'mimeType' => [['extension' => 'tpl', 'mime' => 'application/vnd.groove-tool-template']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '00001A000210040000000000', 'offset' => '0', 'extension' => ['wk4'], 'mimeType' => [['extension' => 'wk4', 'mime' => 'application/vnd.lotus-1-2-3']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '00001A000010040000000000', 'offset' => '0', 'extension' => ['wk3'], 'mimeType' => [['extension' => 'wk3', 'mime' => 'application/vnd.lotus-1-2-3']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '0000020006040600080000000000', 'offset' => '0', 'extension' => ['wk1'], 'mimeType' => [['extension' => 'wk1', 'mime' => 'application/vnd.lotus-1-2-3']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '1A0000040000', 'offset' => '0', 'extension' => ['nsf'], 'mimeType' => [['extension' => 'nsf', 'mime' => 'application/vnd.lotus-notes']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '4E45534D1A01', 'offset' => '0', 'extension' => ['nsf'], 'mimeType' => [['extension' => 'nsf', 'mime' => 'application/vnd.lotus-notes']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '1A0000', 'offset' => '0', 'extension' => ['ntf'], 'mimeType' => [['extension' => 'ntf', 'mime' => 'application/vnd.lotus-notes']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '30314F52444E414E43452053555256455920202020202020', 'offset' => '0', 'extension' => ['ntf'], 'mimeType' => [['extension' => 'ntf', 'mime' => 'application/vnd.lotus-notes']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '4E49544630', 'offset' => '0', 'extension' => ['ntf'], 'mimeType' => [['extension' => 'ntf', 'mime' => 'application/vnd.lotus-notes']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '414F4C564D313030', 'offset' => '0', 'extension' => ['org'], 'mimeType' => [['extension' => 'org', 'mime' => 'application/vnd.lotus-organizer']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '576F726450726F', 'offset' => '0', 'extension' => ['lwp'], 'mimeType' => [['extension' => 'lwp', 'mime' => 'application/vnd.lotus-wordpro']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '5B50686F6E655D', 'offset' => '0', 'extension' => ['sam'], 'mimeType' => [['extension' => 'sam', 'mime' => 'application/vnd.lotus-wordpro']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '56657273696F6E20', 'offset' => '0', 'extension' => ['mif'], 'mimeType' => [['extension' => 'mif', 'mime' => 'application/vnd.mif']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '3C3F786D6C2076657273696F6E3D22312E30223F3E', 'offset' => '0', 'extension' => ['xul'], 'mimeType' => [['extension' => 'xul', 'mime' => 'application/vnd.mozilla.xul+xml']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '3026B2758E66CF11A6D900AA0062CE6C', 'offset' => '0', 'extension' => ['asf', 'wma', 'wmv'], 'mimeType' => [['extension' => 'asf', 'mime' => 'application/vnd.ms-asf'], ['extension' => 'wma', 'mime' => 'audio/x-ms-wma'], ['extension' => 'wmv', 'mime' => 'video/x-ms-wmv']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '49536328', 'offset' => '0', 'extension' => ['cab', 'hdr'], 'mimeType' => [['extension' => 'cab', 'mime' => 'application/vnd.ms-cab-compressed'], ['extension' => 'hdr', 'mime' => 'image/vnd.radiance']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '4D534346', 'offset' => '0', 'extension' => ['cab'], 'mimeType' => [['extension' => 'cab', 'mime' => 'application/vnd.ms-cab-compressed']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '0908100000060500', 'offset' => '512', 'extension' => ['xls', 'pcx'], 'mimeType' => [['extension' => 'xls', 'mime' => 'application/vnd.ms-excel'], ['extension' => 'pcx', 'mime' => 'image/vnd.zbrush.pcx']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => 'FDFFFFFF04', 'offset' => '512', 'extension' => ['xls', 'ppt'], 'mimeType' => [['extension' => 'xls', 'mime' => 'application/vnd.ms-excel'], ['extension' => 'ppt', 'mime' => 'application/vnd.ms-powerpoint']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => 'FDFFFFFF20000000', 'offset' => '512', 'extension' => ['xls'], 'mimeType' => [['extension' => 'xls', 'mime' => 'application/vnd.ms-excel']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '49545346', 'offset' => '0', 'extension' => ['chm'], 'mimeType' => [['extension' => 'chm', 'mime' => 'application/vnd.ms-htmlhelp']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '006E1EF0', 'offset' => '512', 'extension' => ['ppt'], 'mimeType' => [['extension' => 'ppt', 'mime' => 'application/vnd.ms-powerpoint']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '0F00E803', 'offset' => '512', 'extension' => ['ppt'], 'mimeType' => [['extension' => 'ppt', 'mime' => 'application/vnd.ms-powerpoint']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => 'A0461DF0', 'offset' => '512', 'extension' => ['ppt'], 'mimeType' => [['extension' => 'ppt', 'mime' => 'application/vnd.ms-powerpoint']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '0E574B53', 'offset' => '0', 'extension' => ['wks'], 'mimeType' => [['extension' => 'wks', 'mime' => 'application/vnd.ms-works']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => 'FF000200040405540200', 'offset' => '0', 'extension' => ['wks'], 'mimeType' => [['extension' => 'wks', 'mime' => 'application/vnd.ms-works']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '4D6963726F736F66742057696E646F7773204D6564696120506C61796572202D2D20', 'offset' => '84', 'extension' => ['wpl'], 'mimeType' => [['extension' => 'wpl', 'mime' => 'application/vnd.ms-wpl']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '5B56657273696F6E', 'offset' => '2', 'extension' => ['cif'], 'mimeType' => [['extension' => 'cif', 'mime' => 'application/vnd.multiad.creator.cif']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '504B030414000600', 'offset' => '0', 'extension' => ['pptx', 'xlsx', 'docx'], 'mimeType' => [['extension' => 'pptx', 'mime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation'], ['extension' => 'xlsx', 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'], ['extension' => 'docx', 'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '424F4F4B4D4F4249', 'offset' => '0', 'extension' => ['prc'], 'mimeType' => [['extension' => 'prc', 'mime' => 'application/vnd.palm']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '74424D504B6E5772', 'offset' => '60', 'extension' => ['prc'], 'mimeType' => [['extension' => 'prc', 'mime' => 'application/vnd.palm']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '000000000000000000000000000000000000000000000000', 'offset' => '11', 'extension' => ['pdb'], 'mimeType' => [['extension' => 'pdb', 'mime' => 'application/vnd.palm']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '4D2D5720506F636B6574204469637469', 'offset' => '0', 'extension' => ['pdb'], 'mimeType' => [['extension' => 'pdb', 'mime' => 'application/vnd.palm']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '4D6963726F736F667420432F432B2B20', 'offset' => '0', 'extension' => ['pdb'], 'mimeType' => [['extension' => 'pdb', 'mime' => 'application/vnd.palm']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '736D5F', 'offset' => '0', 'extension' => ['pdb'], 'mimeType' => [['extension' => 'pdb', 'mime' => 'application/vnd.palm']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '737A657A', 'offset' => '0', 'extension' => ['pdb'], 'mimeType' => [['extension' => 'pdb', 'mime' => 'application/vnd.palm']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => 'ACED0005737200126267626C69747A2E', 'offset' => '0', 'extension' => ['pdb'], 'mimeType' => [['extension' => 'pdb', 'mime' => 'application/vnd.palm']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '00004D4D585052', 'offset' => '0', 'extension' => ['qxd'], 'mimeType' => [['extension' => 'qxd', 'mime' => 'application/vnd.Quark.QuarkXPress']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '526172211A0700', 'offset' => '0', 'extension' => ['rar'], 'mimeType' => [['extension' => 'rar', 'mime' => 'application/vnd.rar']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '526172211A070100', 'offset' => '0', 'extension' => ['rar'], 'mimeType' => [['extension' => 'rar', 'mime' => 'application/vnd.rar']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '4D4D4D440000', 'offset' => '0', 'extension' => ['mmf'], 'mimeType' => [['extension' => 'mmf', 'mime' => 'application/vnd.smaf']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '52545353', 'offset' => '0', 'extension' => ['cap'], 'mimeType' => [['extension' => 'cap', 'mime' => 'application/vnd.tcpdump.pcap']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '58435000', 'offset' => '0', 'extension' => ['cap'], 'mimeType' => [['extension' => 'cap', 'mime' => 'application/vnd.tcpdump.pcap']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '4D444D5093A7', 'offset' => '0', 'extension' => ['dmp'], 'mimeType' => [['extension' => 'dmp', 'mime' => 'application/vnd.tcpdump.pcap']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '5041474544553634', 'offset' => '0', 'extension' => ['dmp'], 'mimeType' => [['extension' => 'dmp', 'mime' => 'application/vnd.tcpdump.pcap']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '5041474544554D50', 'offset' => '0', 'extension' => ['dmp'], 'mimeType' => [['extension' => 'dmp', 'mime' => 'application/vnd.tcpdump.pcap']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => 'FF575043', 'offset' => '0', 'extension' => ['wpd'], 'mimeType' => [['extension' => 'wpd', 'mime' => 'application/vnd.wordperfect']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '78617221', 'offset' => '0', 'extension' => ['xar'], 'mimeType' => [['extension' => 'xar', 'mime' => 'application/vnd.xara']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '5350464900', 'offset' => '0', 'extension' => ['spf'], 'mimeType' => [['extension' => 'spf', 'mime' => 'application/vnd.yamaha.smaf-phrase']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '0764743264647464', 'offset' => '0', 'extension' => ['dtd'], 'mimeType' => [['extension' => 'dtd', 'mime' => 'application/xml-dtd']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '504B030414000100630000000000', 'offset' => '0', 'extension' => ['zip'], 'mimeType' => [['extension' => 'zip', 'mime' => 'application/zip']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '504B0708', 'offset' => '0', 'extension' => ['zip'], 'mimeType' => [['extension' => 'zip', 'mime' => 'application/zip']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '504B4C495445', 'offset' => '30', 'extension' => ['zip'], 'mimeType' => [['extension' => 'zip', 'mime' => 'application/zip']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '504B537058', 'offset' => '526', 'extension' => ['zip'], 'mimeType' => [['extension' => 'zip', 'mime' => 'application/zip']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '152', 'offset' => '29', 'extension' => ['zip'], 'mimeType' => [['extension' => 'zip', 'mime' => 'application/zip']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '2321414D52', 'offset' => '0', 'extension' => ['amr'], 'mimeType' => [['extension' => 'amr', 'mime' => 'audio/AMR']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '2E736E64', 'offset' => '0', 'extension' => ['au'], 'mimeType' => [['extension' => 'au', 'mime' => 'audio/basic']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '646E732E', 'offset' => '0', 'extension' => ['au'], 'mimeType' => [['extension' => 'au', 'mime' => 'audio/basic']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '00000020667479704D344120', 'offset' => '0', 'extension' => ['m4a'], 'mimeType' => [['extension' => 'm4a', 'mime' => 'audio/mp4']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '667479704D344120', 'offset' => '4', 'extension' => ['m4a'], 'mimeType' => [['extension' => 'm4a', 'mime' => 'audio/mp4']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '494433', 'offset' => '0', 'extension' => ['mp3'], 'mimeType' => [['extension' => 'mp3', 'mime' => 'audio/mpeg']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => 'FFD8', 'offset' => '0', 'extension' => ['mp3', 'jpg', 'jpeg', 'jpe', 'jfif', 'mpeg', 'mpg'], 'mimeType' => [['extension' => 'mp3', 'mime' => 'audio/mpeg'], ['extension' => 'jpg', 'mime' => 'image/jpeg'], ['extension' => 'jpeg', 'mime' => 'image/jpeg'], ['extension' => 'jpe', 'mime' => 'image/jpeg'], ['extension' => 'jfif', 'mime' => 'image/jpeg'], ['extension' => 'mpeg', 'mime' => 'video/mpeg'], ['extension' => 'mpg', 'mime' => 'video/mpeg']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '52494646', 'offset' => '0', 'extension' => ['qcp', 'wav', 'webp', 'avi'], 'mimeType' => [['extension' => 'qcp', 'mime' => 'audio/qcelp'], ['extension' => 'wav', 'mime' => 'audio/x-wav'], ['extension' => 'webp', 'mime' => 'image/webp'], ['extension' => 'avi', 'mime' => 'video/x-msvideo']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '49443303000000', 'offset' => '0', 'extension' => ['koz'], 'mimeType' => [['extension' => 'koz', 'mime' => 'audio/vnd.audikoz']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '424D', 'offset' => '0', 'extension' => ['bmp', 'dib'], 'mimeType' => [['extension' => 'bmp', 'mime' => 'image/bmp'], ['extension' => 'dib', 'mime' => 'image/bmp']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '01000000', 'offset' => '0', 'extension' => ['emf'], 'mimeType' => [['extension' => 'emf', 'mime' => 'image/emf']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '53494D504C4520203D202020202020202020202020202020202020202054', 'offset' => '0', 'extension' => ['fits'], 'mimeType' => [['extension' => 'fits', 'mime' => 'image/fits']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '474946383961', 'offset' => '0', 'extension' => ['gif'], 'mimeType' => [['extension' => 'gif', 'mime' => 'image/gif']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '0000000C6A5020200D0A', 'offset' => '0', 'extension' => ['jp2'], 'mimeType' => [['extension' => 'jp2', 'mime' => 'image/jp2']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '89504E470D0A1A0A', 'offset' => '0', 'extension' => ['png'], 'mimeType' => [['extension' => 'png', 'mime' => 'image/png']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '492049', 'offset' => '0', 'extension' => ['tiff', 'tif'], 'mimeType' => [['extension' => 'tiff', 'mime' => 'image/tiff'], ['extension' => 'tif', 'mime' => 'image/tiff']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '49492A00', 'offset' => '0', 'extension' => ['tiff', 'tif'], 'mimeType' => [['extension' => 'tiff', 'mime' => 'image/tiff'], ['extension' => 'tif', 'mime' => 'image/tiff']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '4D4D002A', 'offset' => '0', 'extension' => ['tiff', 'tif'], 'mimeType' => [['extension' => 'tiff', 'mime' => 'image/tiff'], ['extension' => 'tif', 'mime' => 'image/tiff']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '4D4D002B', 'offset' => '0', 'extension' => ['tiff', 'tif'], 'mimeType' => [['extension' => 'tiff', 'mime' => 'image/tiff'], ['extension' => 'tif', 'mime' => 'image/tiff']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '38425053', 'offset' => '0', 'extension' => ['psd'], 'mimeType' => [['extension' => 'psd', 'mime' => 'image/vnd.adobe.photoshop']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '41433130', 'offset' => '0', 'extension' => ['dwg'], 'mimeType' => [['extension' => 'dwg', 'mime' => 'image/vnd.dwg']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '00000100', 'offset' => '0', 'extension' => ['ico', 'mpeg', 'mpg', 'spl'], 'mimeType' => [['extension' => 'ico', 'mime' => 'image/vnd.microsoft.icon'], ['extension' => 'mpeg', 'mime' => 'video/mpeg'], ['extension' => 'mpg', 'mime' => 'video/mpeg'], ['extension' => 'spl', 'mime' => 'application/x-futuresplash']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '4550', 'offset' => '0', 'extension' => ['mdi'], 'mimeType' => [['extension' => 'mdi', 'mime' => 'image/vnd.ms-modi']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '233F52414449414E43450A', 'offset' => '0', 'extension' => ['hdr'], 'mimeType' => [['extension' => 'hdr', 'mime' => 'image/vnd.radiance']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '010009000003', 'offset' => '0', 'extension' => ['wmf'], 'mimeType' => [['extension' => 'wmf', 'mime' => 'image/wmf']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => 'D7CDC69A', 'offset' => '0', 'extension' => ['wmf'], 'mimeType' => [['extension' => 'wmf', 'mime' => 'image/wmf']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '46726F6D3A20', 'offset' => '0', 'extension' => ['eml'], 'mimeType' => [['extension' => 'eml', 'mime' => 'message/rfc822']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '52657475726E2D506174683A20', 'offset' => '0', 'extension' => ['eml'], 'mimeType' => [['extension' => 'eml', 'mime' => 'message/rfc822']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '582D', 'offset' => '0', 'extension' => ['eml'], 'mimeType' => [['extension' => 'eml', 'mime' => 'message/rfc822']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '4A47040E', 'offset' => '0', 'extension' => ['art'], 'mimeType' => [['extension' => 'art', 'mime' => 'message/rfc822']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '3C3F786D6C2076657273696F6E3D', 'offset' => '0', 'extension' => ['manifest'], 'mimeType' => [['extension' => 'manifest', 'mime' => 'text/cache-manifest']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '2A2A2A2020496E7374616C6C6174696F6E205374617274656420', 'offset' => '0', 'extension' => ['log'], 'mimeType' => [['extension' => 'log', 'mime' => 'text/plain']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '424547494E3A56434152440D0A', 'offset' => '0', 'extension' => ['vcf'], 'mimeType' => [['extension' => 'vcf', 'mime' => 'text/vcard']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '444D5321', 'offset' => '0', 'extension' => ['dms'], 'mimeType' => [['extension' => 'dms', 'mime' => 'text/vnd.DMClientScript']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '0000001466747970336770', 'offset' => '0', 'extension' => ['3gp', '3g2'], 'mimeType' => [['extension' => '3gp', 'mime' => 'video/3gpp'], ['extension' => '3g2', 'mime' => 'video/3gpp2']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '0000002066747970336770', 'offset' => '0', 'extension' => ['3gp', '3g2'], 'mimeType' => [['extension' => '3gp', 'mime' => 'video/3gpp'], ['extension' => '3g2', 'mime' => 'video/3gpp2']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '000000146674797069736F6D', 'offset' => '0', 'extension' => ['mp4'], 'mimeType' => [['extension' => 'mp4', 'mime' => 'video/mp4']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '000000186674797033677035', 'offset' => '0', 'extension' => ['mp4'], 'mimeType' => [['extension' => 'mp4', 'mime' => 'video/mp4']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '0000001C667479704D534E56012900464D534E566D703432', 'offset' => '0', 'extension' => ['mp4'], 'mimeType' => [['extension' => 'mp4', 'mime' => 'video/mp4']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '6674797033677035', 'offset' => '4', 'extension' => ['mp4'], 'mimeType' => [['extension' => 'mp4', 'mime' => 'video/mp4']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '667479704D534E56', 'offset' => '4', 'extension' => ['mp4'], 'mimeType' => [['extension' => 'mp4', 'mime' => 'video/mp4']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '6674797069736F6D', 'offset' => '4', 'extension' => ['mp4'], 'mimeType' => [['extension' => 'mp4', 'mime' => 'video/mp4']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '00000018667479706D703432', 'offset' => '0', 'extension' => ['m4v'], 'mimeType' => [['extension' => 'm4v', 'mime' => 'video/mp4']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '00000020667479704D345620', 'offset' => '0', 'extension' => ['m4v', 'flv'], 'mimeType' => [['extension' => 'm4v', 'mime' => 'video/mp4'], ['extension' => 'flv', 'mime' => 'video/x-flv']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '667479706D703432', 'offset' => '4', 'extension' => ['m4v'], 'mimeType' => [['extension' => 'm4v', 'mime' => 'video/mp4']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '000001BA', 'offset' => '0', 'extension' => ['mpg'], 'mimeType' => [['extension' => 'mpg', 'mime' => 'video/mpeg']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '00', 'offset' => '0', 'extension' => ['mov'], 'mimeType' => [['extension' => 'mov', 'mime' => 'video/quicktime']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '000000146674797071742020', 'offset' => '0', 'extension' => ['mov'], 'mimeType' => [['extension' => 'mov', 'mime' => 'video/quicktime']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '6674797071742020', 'offset' => '4', 'extension' => ['mov'], 'mimeType' => [['extension' => 'mov', 'mime' => 'video/quicktime']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '6D6F6F76', 'offset' => '4', 'extension' => ['mov'], 'mimeType' => [['extension' => 'mov', 'mime' => 'video/quicktime']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '4350543746494C45', 'offset' => '0', 'extension' => ['cpt'], 'mimeType' => [['extension' => 'cpt', 'mime' => 'application/mac-compactpro']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '43505446494C45', 'offset' => '0', 'extension' => ['cpt'], 'mimeType' => [['extension' => 'cpt', 'mime' => 'application/mac-compactpro']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '425A68', 'offset' => '0', 'extension' => ['bz2'], 'mimeType' => [['extension' => 'bz2', 'mime' => 'application/x-bzip2']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '454E5452595643440200000102001858', 'offset' => '0', 'extension' => ['vcd'], 'mimeType' => [['extension' => 'vcd', 'mime' => 'application/x-cdlink']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '6375736800000002000000', 'offset' => '0', 'extension' => ['csh'], 'mimeType' => [['extension' => 'csh', 'mime' => 'application/x-csh']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '4A4152435300', 'offset' => '0', 'extension' => ['jar'], 'mimeType' => [['extension' => 'jar', 'mime' => 'application/x-java-archive']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '504B0304140008000800', 'offset' => '0', 'extension' => ['jar'], 'mimeType' => [['extension' => 'jar', 'mime' => 'application/x-java-archive']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '5F27A889', 'offset' => '0', 'extension' => ['jar'], 'mimeType' => [['extension' => 'jar', 'mime' => 'application/x-java-archive']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => 'EDABEEDB', 'offset' => '0', 'extension' => ['rpm'], 'mimeType' => [['extension' => 'rpm', 'mime' => 'application/x-rpm']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '435753', 'offset' => '0', 'extension' => ['swf'], 'mimeType' => [['extension' => 'swf', 'mime' => 'application/x-shockwave-flash']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '465753', 'offset' => '0', 'extension' => ['swf'], 'mimeType' => [['extension' => 'swf', 'mime' => 'application/x-shockwave-flash']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '5A5753', 'offset' => '0', 'extension' => ['swf'], 'mimeType' => [['extension' => 'swf', 'mime' => 'application/x-shockwave-flash']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '5349542100', 'offset' => '0', 'extension' => ['sit'], 'mimeType' => [['extension' => 'sit', 'mime' => 'application/x-stuffit']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '5374756666497420286329313939372D', 'offset' => '0', 'extension' => ['sit'], 'mimeType' => [['extension' => 'sit', 'mime' => 'application/x-stuffit']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '7573746172', 'offset' => '257', 'extension' => ['tar'], 'mimeType' => [['extension' => 'tar', 'mime' => 'application/x-tar']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => 'FD377A585A00', 'offset' => '0', 'extension' => ['xz'], 'mimeType' => [['extension' => 'xz', 'mime' => 'application/x-xz']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '4D546864', 'offset' => '0', 'extension' => ['mid', 'midi'], 'mimeType' => [['extension' => 'mid', 'mime' => 'audio/midi'], ['extension' => 'midi', 'mime' => 'audio/midi']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '464F524D00', 'offset' => '0', 'extension' => ['aiff'], 'mimeType' => [['extension' => 'aiff', 'mime' => 'audio/x-aiff']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '664C614300000022', 'offset' => '0', 'extension' => ['flac'], 'mimeType' => [['extension' => 'flac', 'mime' => 'audio/x-flac']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '727473703A2F2F', 'offset' => '0', 'extension' => ['ram'], 'mimeType' => [['extension' => 'ram', 'mime' => 'audio/x-pn-realaudio']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '2E524D46', 'offset' => '0', 'extension' => ['rm'], 'mimeType' => [['extension' => 'rm', 'mime' => 'audio/x-pn-realaudio']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '2E524D460000001200', 'offset' => '0', 'extension' => ['ra'], 'mimeType' => [['extension' => 'ra', 'mime' => 'audio/x-realaudio']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '2E7261FD00', 'offset' => '0', 'extension' => ['ra'], 'mimeType' => [['extension' => 'ra', 'mime' => 'audio/x-realaudio']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '50350A', 'offset' => '0', 'extension' => ['pgm'], 'mimeType' => [['extension' => 'pgm', 'mime' => 'image/x-portable-graymap']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '01DA01010003', 'offset' => '0', 'extension' => ['rgb'], 'mimeType' => [['extension' => 'rgb', 'mime' => 'image/x-rgb']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '1A45DFA3', 'offset' => '0', 'extension' => ['webm', 'mkv'], 'mimeType' => [['extension' => 'webm', 'mime' => 'video/webm'], ['extension' => 'mkv', 'mime' => 'video/x-matroska']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '464C5601', 'offset' => '0', 'extension' => ['flv'], 'mimeType' => [['extension' => 'flv', 'mime' => 'video/x-flv']], 'file_class' => EntropyResult::COMPRESSED],
        ['byteSequence' => '3C', 'offset' => '0', 'extension' => ['asx'], 'mimeType' => [['extension' => 'asx', 'mime' => 'video/x-ms-asf']], 'file_class' => EntropyResult::COMPRESSED],
    ];

    /**
     * @var array
     */
    public static function getSignatures()
    {
        return self::$signatures;
    }
}
