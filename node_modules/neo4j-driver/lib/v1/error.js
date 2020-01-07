"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.newError = newError;
exports.PROTOCOL_ERROR = exports.SESSION_EXPIRED = exports.SERVICE_UNAVAILABLE = exports.Neo4jError = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _possibleConstructorReturn2 = _interopRequireDefault(require("@babel/runtime/helpers/possibleConstructorReturn"));

var _getPrototypeOf2 = _interopRequireDefault(require("@babel/runtime/helpers/getPrototypeOf"));

var _inherits2 = _interopRequireDefault(require("@babel/runtime/helpers/inherits"));

var _wrapNativeSuper2 = _interopRequireDefault(require("@babel/runtime/helpers/wrapNativeSuper"));

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
// A common place for constructing error objects, to keep them
// uniform across the driver surface.

/**
 * Error code representing complete loss of service. Used by {@link Neo4jError#code}.
 * @type {string}
 */
var SERVICE_UNAVAILABLE = 'ServiceUnavailable';
/**
 * Error code representing transient loss of service. Used by {@link Neo4jError#code}.
 * @type {string}
 */

exports.SERVICE_UNAVAILABLE = SERVICE_UNAVAILABLE;
var SESSION_EXPIRED = 'SessionExpired';
/**
 * Error code representing serialization/deserialization issue in the Bolt protocol. Used by {@link Neo4jError#code}.
 * @type {string}
 */

exports.SESSION_EXPIRED = SESSION_EXPIRED;
var PROTOCOL_ERROR = 'ProtocolError';
exports.PROTOCOL_ERROR = PROTOCOL_ERROR;

function newError(message) {
  var code = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'N/A';
  // TODO: Idea is that we can check the code here and throw sub-classes
  // of Neo4jError as appropriate
  return new Neo4jError(message, code);
}
/**
 * Class for all errors thrown/returned by the driver.
 */


var Neo4jError =
/*#__PURE__*/
function (_Error) {
  (0, _inherits2["default"])(Neo4jError, _Error);

  /**
   * @constructor
   * @param {string} message - The error message.
   * @param {string} code - Optional error code. Will be populated when error originates in the database.
   */
  function Neo4jError(message) {
    var _this;

    var code = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'N/A';
    (0, _classCallCheck2["default"])(this, Neo4jError);
    _this = (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(Neo4jError).call(this, message));
    _this.message = message;
    _this.code = code;
    _this.name = 'Neo4jError';
    return _this;
  }

  return Neo4jError;
}((0, _wrapNativeSuper2["default"])(Error));

exports.Neo4jError = Neo4jError;