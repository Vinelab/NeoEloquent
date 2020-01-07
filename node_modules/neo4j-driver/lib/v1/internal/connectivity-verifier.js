"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _connectionHolder = _interopRequireDefault(require("./connection-holder"));

var _driver = require("../driver");

var _streamObserver = _interopRequireDefault(require("./stream-observer"));

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

/**
 * Verifies connectivity using the given connection provider.
 */
var ConnectivityVerifier =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {ConnectionProvider} connectionProvider the provider to obtain connections from.
   * @param {function} successCallback a callback to invoke when verification succeeds.
   */
  function ConnectivityVerifier(connectionProvider, successCallback) {
    (0, _classCallCheck2["default"])(this, ConnectivityVerifier);
    this._connectionProvider = connectionProvider;
    this._successCallback = successCallback;
  }

  (0, _createClass2["default"])(ConnectivityVerifier, [{
    key: "verify",
    value: function verify() {
      var _this = this;

      acquireAndReleaseDummyConnection(this._connectionProvider).then(function (serverInfo) {
        if (_this._successCallback) {
          _this._successCallback(serverInfo);
        }
      })["catch"](function (ignoredError) {});
    }
  }]);
  return ConnectivityVerifier;
}();
/**
 * @private
 * @param {ConnectionProvider} connectionProvider the provider to obtain connections from.
 * @return {Promise<object>} promise resolved with server info or rejected with error.
 */


exports["default"] = ConnectivityVerifier;

function acquireAndReleaseDummyConnection(connectionProvider) {
  var connectionHolder = new _connectionHolder["default"](_driver.READ, connectionProvider);
  connectionHolder.initializeConnection();
  var dummyObserver = new _streamObserver["default"]();
  var connectionPromise = connectionHolder.getConnection(dummyObserver);
  return connectionPromise.then(function (connection) {
    // able to establish a connection
    return connectionHolder.close().then(function () {
      return connection.server;
    });
  })["catch"](function (error) {
    // failed to establish a connection
    return connectionHolder.close()["catch"](function (ignoredError) {// ignore connection release error
    }).then(function () {
      return Promise.reject(error);
    });
  });
}