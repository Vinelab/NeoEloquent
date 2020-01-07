"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.DEFAULT_ACQUISITION_TIMEOUT = exports.DEFAULT_MAX_SIZE = exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

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
var DEFAULT_MAX_SIZE = 100;
exports.DEFAULT_MAX_SIZE = DEFAULT_MAX_SIZE;
var DEFAULT_ACQUISITION_TIMEOUT = 60 * 1000; // 60 seconds

exports.DEFAULT_ACQUISITION_TIMEOUT = DEFAULT_ACQUISITION_TIMEOUT;

var PoolConfig =
/*#__PURE__*/
function () {
  function PoolConfig(maxSize, acquisitionTimeout) {
    (0, _classCallCheck2["default"])(this, PoolConfig);
    this.maxSize = valueOrDefault(maxSize, DEFAULT_MAX_SIZE);
    this.acquisitionTimeout = valueOrDefault(acquisitionTimeout, DEFAULT_ACQUISITION_TIMEOUT);
  }

  (0, _createClass2["default"])(PoolConfig, null, [{
    key: "defaultConfig",
    value: function defaultConfig() {
      return new PoolConfig(DEFAULT_MAX_SIZE, DEFAULT_ACQUISITION_TIMEOUT);
    }
  }, {
    key: "fromDriverConfig",
    value: function fromDriverConfig(config) {
      var maxIdleSizeConfigured = isConfigured(config.connectionPoolSize);
      var maxSizeConfigured = isConfigured(config.maxConnectionPoolSize);
      var maxSize;

      if (maxSizeConfigured) {
        // correct size setting is set - use it's value
        maxSize = config.maxConnectionPoolSize;
      } else if (maxIdleSizeConfigured) {
        // deprecated size setting is set - use it's value
        console.warn('WARNING: neo4j-driver setting "connectionPoolSize" is deprecated, please use "maxConnectionPoolSize" instead');
        maxSize = config.connectionPoolSize;
      } else {
        maxSize = DEFAULT_MAX_SIZE;
      }

      var acquisitionTimeoutConfigured = isConfigured(config.connectionAcquisitionTimeout);
      var acquisitionTimeout = acquisitionTimeoutConfigured ? config.connectionAcquisitionTimeout : DEFAULT_ACQUISITION_TIMEOUT;
      return new PoolConfig(maxSize, acquisitionTimeout);
    }
  }]);
  return PoolConfig;
}();

exports["default"] = PoolConfig;

function valueOrDefault(value, defaultValue) {
  return value === 0 || value ? value : defaultValue;
}

function isConfigured(value) {
  return value === 0 || value;
}