"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _util = require("./util");

var _urlUtil = _interopRequireDefault(require("./url-util"));

/**
 * Copyright (c) 2002-2019 "Neo4j,"
 * Neo4j Sweden AB [http://neo4j.com]
 *
 * This file is part of Neo4j.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
var ServerAddress =
/*#__PURE__*/
function () {
  function ServerAddress(host, resolved, port, hostPort) {
    (0, _classCallCheck2["default"])(this, ServerAddress);
    this._host = (0, _util.assertString)(host, 'host');
    this._resolved = resolved ? (0, _util.assertString)(resolved, 'resolved') : null;
    this._port = (0, _util.assertNumber)(port, 'port');
    this._hostPort = hostPort;
    this._stringValue = resolved ? "".concat(hostPort, "(").concat(resolved, ")") : "".concat(hostPort);
  }

  (0, _createClass2["default"])(ServerAddress, [{
    key: "host",
    value: function host() {
      return this._host;
    }
  }, {
    key: "resolvedHost",
    value: function resolvedHost() {
      return this._resolved ? this._resolved : this._host;
    }
  }, {
    key: "port",
    value: function port() {
      return this._port;
    }
  }, {
    key: "resolveWith",
    value: function resolveWith(resolved) {
      return new ServerAddress(this._host, resolved, this._port, this._hostPort);
    }
  }, {
    key: "asHostPort",
    value: function asHostPort() {
      return this._hostPort;
    }
  }, {
    key: "asKey",
    value: function asKey() {
      return this._hostPort;
    }
  }, {
    key: "toString",
    value: function toString() {
      return this._stringValue;
    }
  }], [{
    key: "fromUrl",
    value: function fromUrl(url) {
      var urlParsed = _urlUtil["default"].parseDatabaseUrl(url);

      return new ServerAddress(urlParsed.host, null, urlParsed.port, urlParsed.hostAndPort);
    }
  }]);
  return ServerAddress;
}();

exports["default"] = ServerAddress;