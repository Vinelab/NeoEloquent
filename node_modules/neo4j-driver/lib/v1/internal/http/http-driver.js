"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _possibleConstructorReturn2 = _interopRequireDefault(require("@babel/runtime/helpers/possibleConstructorReturn"));

var _getPrototypeOf2 = _interopRequireDefault(require("@babel/runtime/helpers/getPrototypeOf"));

var _inherits2 = _interopRequireDefault(require("@babel/runtime/helpers/inherits"));

var _driver = _interopRequireDefault(require("../../driver"));

var _httpSession = _interopRequireDefault(require("./http-session"));

var _httpSessionTracker = _interopRequireDefault(require("./http-session-tracker"));

var _serverAddress = _interopRequireDefault(require("../server-address"));

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
var HttpDriver =
/*#__PURE__*/
function (_Driver) {
  (0, _inherits2["default"])(HttpDriver, _Driver);

  function HttpDriver(url, userAgent, token, config) {
    var _this;

    (0, _classCallCheck2["default"])(this, HttpDriver);
    _this = (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(HttpDriver).call(this, _serverAddress["default"].fromUrl(url.hostAndPort), userAgent, token, config));
    _this._url = url;
    _this._sessionTracker = new _httpSessionTracker["default"]();
    return _this;
  }

  (0, _createClass2["default"])(HttpDriver, [{
    key: "session",
    value: function session() {
      return new _httpSession["default"](this._url, this._authToken, this._config, this._sessionTracker);
    }
  }, {
    key: "close",
    value: function close() {
      return this._sessionTracker.close();
    }
  }]);
  return HttpDriver;
}(_driver["default"]);

exports["default"] = HttpDriver;