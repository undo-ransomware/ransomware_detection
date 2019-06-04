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

namespace OCA\RansomwareDetection;

class RequestTemplate implements IRequestTemplate {
    public function get($url) {
        $request = new HttpRequest();
        $request->setUrl($url);
        $request->setMethod(HttpRequest::METH_GET);

        return $request->send();
    }

    public function post($url, $queryData) {
        $request = new HttpRequest();
        $request->setUrl($url);
        $request->setMethod(HttpRequest::METH_POST);

        $request->setQueryData($queryData);

        return $request->send();
    }

    public function put($url, $queryData) {
        $request = new HttpRequest();
        $request->setUrl($url);
        $request->setMethod(HttpRequest::METH_PUT);

        $request->setQueryData($queryData);

        return $request->send();
    }

    public function delete($url) {
        $request = new HttpRequest();
        $request->setUrl($url);
        $request->setMethod(HttpRequest::METH_DELETE);

        return $request->send();
    }
}