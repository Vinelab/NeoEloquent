"use strict";

var _interopRequireWildcard = require("@babel/runtime/helpers/interopRequireWildcard");

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var util = _interopRequireWildcard(require("./util"));

var _integer = require("../integer");

var _error = require("../error");

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
 * Internal holder of the transaction configuration.
 * It performs input validation and value conversion for further serialization by the Bolt protocol layer.
 * Users of the driver provide transaction configuration as regular objects `{timeout: 10, metadata: {key: 'value'}}`.
 * Driver converts such objects to {@link TxConfig} immediately and uses converted values everywhere.
 */
var TxConfig =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {object} config the raw configuration object.
   */
  function TxConfig(config) {
    (0, _classCallCheck2["default"])(this, TxConfig);
    assertValidConfig(config);
    this.timeout = extractTimeout(config);
    this.metadata = extractMetadata(config);
  }
  /**
   * Get an empty config object.
   * @return {TxConfig} an empty config.
   */


  (0, _createClass2["default"])(TxConfig, [{
    key: "isEmpty",

    /**
     * Check if this config object is empty. I.e. has no configuration values specified.
     * @return {boolean} `true` if this object is empty, `false` otherwise.
     */
    value: function isEmpty() {
      return Object.values(this).every(function (value) {
        return value == null;
      });
    }
  }], [{
    key: "empty",
    value: function empty() {
      return EMPTY_CONFIG;
    }
  }]);
  return TxConfig;
}();

exports["default"] = TxConfig;
var EMPTY_CONFIG = new TxConfig({});
/**
 * @return {Integer|null}
 */

function extractTimeout(config) {
  if (util.isObject(config) && (config.timeout || config.timeout === 0)) {
    util.assertNumberOrInteger(config.timeout, 'Transaction timeout');
    var timeout = (0, _integer["int"])(config.timeout);

    if (timeout.isZero()) {
      throw (0, _error.newError)('Transaction timeout should not be zero');
    }

    if (timeout.isNegative()) {
      throw (0, _error.newError)('Transaction timeout should not be negative');
    }

    return timeout;
  }

  return null;
}
/**
 * @return {object|null}
 */


function extractMetadata(config) {
  if (util.isObject(config) && config.metadata) {
    var metadata = config.metadata;
    util.assertObject(metadata);

    if (Object.keys(metadata).length !== 0) {
      // not an empty object
      return metadata;
    }
  }

  return null;
}

function assertValidConfig(config) {
  if (config) {
    util.assertObject(config, 'Transaction config');
  }
}