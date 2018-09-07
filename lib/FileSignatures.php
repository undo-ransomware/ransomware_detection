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

class FileSignatures
{
    /**
     * Signature definition.
     *
     * @var array
     */
    private static $signatures = [
        ['mimeType' => 'application/pdf', 'extensions' => ['pdf'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/25504446/']], 'trailing' => ['offset' => 0, 'bytes' => ['/0a2525454f46/', '/0a2525454f460a/', '/0d0a2525454f460d0a/', '/0d2525454f460d/']]]],
        ['mimeType' => 'image/jpeg', 'extensions' => ['jpg', 'jpeg', 'jfif', 'jpe'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/ffd8ffe000104a46494600/', '/ffd8ffdb/', '/ffd8ffe1[0-9a-f]{4}457869660000/']], 'trailing' => ['offset' => 0, 'bytes' => ['/ffd9/']]]],
        ['mimeType' => 'image/jpg', 'extensions' => ['jp2'], 'signature' => ['trailing' => ['offset' => 0, 'bytes' => ['/0000000c6a5020200d0a/']]]],
        ['mimeType' => '', 'extensions' => ['mp4'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/000000146674797069736f6d/', '/000000186674797033677035/']]]],
        ['mimeType' => '', 'extensions' => ['mov'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/000000146674797071742020/']]]],
        ['mimeType' => '', 'extensions' => ['m4v'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/00000018667479706d703432/']]]],
        ['mimeType' => '', 'extensions' => ['mp4'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/0000001c667479704d534e56012900464d534e566d703432/']]]],
        ['mimeType' => '', 'extensions' => ['m4a'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/00000020667479704d344120/']]]],
        ['mimeType' => '', 'extensions' => ['txt'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/0000feff/']]]],
        ['mimeType' => '', 'extensions' => ['ttf'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/0001000000/']]]],
        ['mimeType' => '', 'extensions' => ['ppt'], 'signature' => ['starting' => ['offset' => 512, 'bytes' => ['/006E1EF0/', '/0F00E803/']]]],
        ['mimeType' => '', 'extensions' => ['drw'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/07/']]]],
        ['mimeType' => '', 'extensions' => ['xls'], 'signature' => ['starting' => ['offset' => 512, 'bytes' => ['/0908100000060500/']]]],
        ['mimeType' => '', 'extensions' => ['doc'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/0d444f43/']]]],
        ['mimeType' => '', 'extensions' => ['webm'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/1a45dfa3/']]]],
        ['mimeType' => '', 'extensions' => ['mkv'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/45dfa3934282886d6174726f736b61/']]]],
        ['mimeType' => '', 'extensions' => ['gz', 'tgz', 'vlt'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/1f8b08/']]]],
        ['mimeType' => '', 'extensions' => ['tar'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/1f9d/', '/1fA0/']]]],
        ['mimeType' => '', 'extensions' => ['eps'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/252150532d41646f62652d332e3020455053462d332030/']]]],
        ['mimeType' => '', 'extensions' => ['pdf'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/38425053/']]]],
        ['mimeType' => '', 'extensions' => ['xul'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/3c3f786d6c2076657273696f6e3d22312e30223f3e/']]]],
        ['mimeType' => '', 'extensions' => ['dwg'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/41433130/']]]],
        ['mimeType' => '', 'extensions' => ['vcf'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/424547494E3A56434152440D0A/']]]],
        ['mimeType' => '', 'extensions' => ['bz2', 'tar.bz2', 'tbz2', 'tb2'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/425a68/']]]],
        ['mimeType' => '', 'extensions' => ['iso'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/4344303031/']]]],
        ['mimeType' => '', 'extensions' => ['swf'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/435753/', '/465753/']]]],
        ['mimeType' => '', 'extensions' => ['gif'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/474946383761/', '/474946383961/']], 'trailing' => ['offset' => 0, 'bytes' => ['/003b/']]]],
        ['mimeType' => '', 'extensions' => ['tif', 'tiff'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/492049/', '/49492a00/', '/4d4d002a/', '/4d4d002b/']]]],
        ['mimeType' => '', 'extensions' => ['mp3'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/494433/']]]],
        ['mimeType' => '', 'extensions' => ['com', 'dll', 'drv', 'exe', 'pif', 'qts', 'qtx', 'sys', 'acm', 'ax', 'cpl', 'fon', 'ocx', 'olb', 'scr', 'vbx'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/4d5a/']]]],
        ['mimeType' => '', 'extensions' => ['zip', 'jar', 'kmz', 'kwd', 'odt', 'odp', 'ott', 'sxc', 'sxd', 'sxi', 'sxw', 'sxc', 'wmz', 'xpi', 'xps', 'xpt'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/504b030414000100630000000000/']]]],
        ['mimeType' => '', 'extensions' => ['epub'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/504b03040a000200/']]]],
        ['mimeType' => '', 'extensions' => ['docx', 'pptx', 'xlsx'], 'signature' => ['starting' => ['offset' => 0, 'bytes' => ['/504b030414000600/']], 'trailing' => ['offset' => 18, 'bytes' => ['/504b0506/']]]],
    ];

    /**
     * @var array
     */
    public static function getSignatures()
    {
        return self::$signatures;
    }
}
