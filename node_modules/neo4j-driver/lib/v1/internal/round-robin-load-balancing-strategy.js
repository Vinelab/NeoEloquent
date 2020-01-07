"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = exports.ROUND_ROBIN_STRATEGY_NAME = void 0;

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
var ROUND_ROBIN_STRATEGY_NAME = 'round_robin';
exports.ROUND_ROBIN_STRATEGY_NAME = ROUND_ROBIN_STRATEGY_NAME;

var RoundRobinLoadBalancingStrategy =
/*#__PURE__*/
function (_LoadBalancingStrateg) {
  (0, _inherits2["default"])(RoundRobinLoadBalancingStrategy, _LoadBalancingStrateg);

  function RoundRobinLoadBalancingStrategy() {
    var _this;

    (0, _classCallCheck2["default"])(this, RoundRobinLoadBalancingStrategy);
    _this = (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(RoundRobinLoadBalancingStrategy).call(this));
    _this._readersIndex = new _roundRobinArrayIndex["default"]();
    _this._writersIndex = new _roundRobinArrayIndex["default"]();
    return _this;
  }
  /**
   * @inheritDoc
   */


  (0, _createClass2["default"])(RoundRobinLoadBalancingStrategy, [{
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
      }

      var index = roundRobinIndex.next(length);
      return addresses[index];
    }
  }]);
  return RoundRobinLoadBalancingStrategy;
}(_loadBalancingStrategy["default"]);

exports["default"] = RoundRobinLoadBalancingStrategy;