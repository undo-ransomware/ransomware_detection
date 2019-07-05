<?php

/**
 * @copyright Copyright (c) 2019 Matthias Held <matthias.held@uni-konstanz.de>
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

namespace OCA\RansomwareDetection\Connector;

use GuzzleHttp\Client;

class RequestTemplate implements IRequestTemplate {
    public static function get($url) {
        $client = new Client();
        return $client->request('GET', $url);   
    }

    public static function post($url, $queryData) {
        $client = new Client();
        return $client->post($url, [
            'json' => $queryData
        ]);
    }

    public static function put($url, $queryData) {
        $client = new Client();
        return $client->put($url, [
            'json' => $queryData
        ]);
    }

    public static function delete($url) {
        $client = new Client();
        return $client->delete($url);
    }
}