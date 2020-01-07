"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = exports.LEAST_CONNECTED_STRATEGY_NAME = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _possibleConstructorReturn2 = _interopRequireDefault(require("@babel/runtime/helpers/possibleConstructorReturn"));

var _getPrototypeOf2 = _interopRequireDefault(require("@babel/runtime/helpers/getPrototypeOf"));

var _inherits2 = _interopRequireDefault(require("@babel/runtime/helpers/inherits"));

var _roundRobinArrayIndex = _interopRequireDefault(require("./round-robin-array-index"));

var _loadBalancingStrategy = _interopRequireDefault(require("./load-balancing-strategy"));

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
var LEAST_CONNECTED_STRATEGY_NAME = 'least_connected';
exports.LEAST_CONNECTED_STRATEGY_NAME = LEAST_CONNECTED_STRATEGY_NAME;

var LeastConnectedLoadBalancingStrategy =
/*#__PURE__*/
function (_LoadBalancingStrateg) {
  (0, _inherits2["default"])(LeastConnectedLoadBalancingStrategy, _LoadBalancingStrateg);

  /**
   * @constructor
   * @param {Pool} connectionPool the connection pool of this driver.
   */
  function LeastConnectedLoadBalancingStrategy(connectionPool) {
    var _this;

    (0, _classCallCheck2["default"])(this, LeastConnectedLoadBalancingStrategy);
    _this = (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(LeastConnectedLoadBalancingStrategy).call(this));
    _this._readersIndex = new _roundRobinArrayIndex["default"]();
    _this._writersIndex = new _roundRobinArrayIndex["default"]();
    _this._connectionPool = connectionPool;
    return _this;
  }
  /**
   * @inheritDoc
   */


  (0, _createClass2["default"])(LeastConnectedLoadBalancingStrategy, [{
    key: "selectReader",
    value: function selectReader(knownReaders) {
      return this._select(knownReaders, this._readersIndex);
    }
    /**
     * @inheritDoc
     */

  }, {
    key: "selectWriter",
    value: function selectWriter(knownWriters) {
      return this._select(knownWriters, this._writersIndex);
    }
  }, {
    key: "_select",
    value: function _select(addresses, roundRobinIndex) {
      var length = addresses.length;

      if (length === 0) {
        return null;
      } // choose start index for iteration in round-robin fashion


      var startIndex = roundRobinIndex.next(length);
      var index = startIndex;
      var leastConnectedAddress = null;
      var leastActiveConnections = Number.MAX_SAFE_INTEGER; // iterate over the array to find least connected address

      do {
        var address = addresses[index];

        var activeConnections = this._connectionPool.activeResourceCount(address);

        if (activeConnections < leastActiveConnections) {
          leastConnectedAddress = address;
          leastActiveConnections = activeConnections;
        } // loop over to the start of the array when end is reached


        if (index === length - 1) {
          index = 0;
        } else {
          index++;
        }
      } while (index !== startIndex);

      return leastConnectedAddress;
    }
  }]);
  return LeastConnectedLoadBalancingStrategy;
}(_loadBalancingStrategy["default"]);

exports["default"] = LeastConnectedLoadBalancingStrategy;