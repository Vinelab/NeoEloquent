"use strict";

var _interopRequireWildcard = require("@babel/runtime/helpers/interopRequireWildcard");

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

var _driver = require("./driver");

var _error = require("./error");

var _connectionProviders = require("./internal/connection-providers");

var _leastConnectedLoadBalancingStrategy = _interopRequireWildcard(require("./internal/least-connected-load-balancing-strategy"));

var _roundRobinLoadBalancingStrategy = _interopRequireWildcard(require("./internal/round-robin-load-balancing-strategy"));

var _connectionErrorHandler = _interopRequireDefault(require("./internal/connection-error-handler"));

var _configuredCustomResolver = _interopRequireDefault(require("./internal/resolver/configured-custom-resolver"));

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
 * A driver that supports routing in a causal cluster.
 * @private
 */
var RoutingDriver =
/*#__PURE__*/
function (_Driver) {
  (0, _inherits2["default"])(RoutingDriver, _Driver);

  function RoutingDriver(address, routingContext, userAgent) {
    var _this;

    var token = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : {};
    var config = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : {};
    (0, _classCallCheck2["default"])(this, RoutingDriver);
    _this = (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(RoutingDriver).call(this, address, userAgent, token, validateConfig(config)));
    _this._routingContext = routingContext;
    return _this;
  }

  (0, _createClass2["default"])(RoutingDriver, [{
    key: "_afterConstruction",
    value: function _afterConstruction() {
      this._log.info("Routing driver ".concat(this._id, " created for server address ").concat(this._address));
    }
  }, {
    key: "_createConnectionProvider",
    value: function _createConnectionProvider(address, connectionPool, driverOnErrorCallback) {
      var loadBalancingStrategy = RoutingDriver._createLoadBalancingStrategy(this._config, connectionPool);

      var resolver = createHostNameResolver(this._config);
      return new _connectionProviders.LoadBalancer(address, this._routingContext, connectionPool, loadBalancingStrategy, resolver, driverOnErrorCallback, this._log);
    }
  }, {
    key: "_createConnectionErrorHandler",
    value: function _createConnectionErrorHandler() {
      var _this2 = this;

      // connection errors mean SERVICE_UNAVAILABLE for direct driver but for routing driver they should only
      // result in SESSION_EXPIRED because there might still exist other servers capable of serving the request
      return new _connectionErrorHandler["default"](_error.SESSION_EXPIRED, function (error, address) {
        return _this2._handleUnavailability(error, address);
      }, function (error, address) {
        return _this2._handleWriteFailure(error, address);
      });
    }
  }, {
    key: "_handleUnavailability",
    value: function _handleUnavailability(error, address) {
      this._log.warn("Routing driver ".concat(this._id, " will forget ").concat(address, " because of an error ").concat(error.code, " '").concat(error.message, "'"));

      this._connectionProvider.forget(address);

      return error;
    }
  }, {
    key: "_handleWriteFailure",
    value: function _handleWriteFailure(error, address) {
      this._log.warn("Routing driver ".concat(this._id, " will forget writer ").concat(address, " because of an error ").concat(error.code, " '").concat(error.message, "'"));

      this._connectionProvider.forgetWriter(address);

      return (0, _error.newError)('No longer possible to write to server at ' + address, _error.SESSION_EXPIRED);
    }
    /**
     * Create new load balancing strategy based on the config.
     * @param {object} config the user provided config.
     * @param {Pool} connectionPool the connection pool for this driver.
     * @return {LoadBalancingStrategy} new strategy.
     * @private
     */

  }], [{
    key: "_createLoadBalancingStrategy",
    value: function _createLoadBalancingStrategy(config, connectionPool) {
      var configuredValue = config.loadBalancingStrategy;

      if (!configuredValue || configuredValue === _leastConnectedLoadBalancingStrategy.LEAST_CONNECTED_STRATEGY_NAME) {
        return new _leastConnectedLoadBalancingStrategy["default"](connectionPool);
      } else if (configuredValue === _roundRobinLoadBalancingStrategy.ROUND_ROBIN_STRATEGY_NAME) {
        return new _roundRobinLoadBalancingStrategy["default"]();
      } else {
        throw (0, _error.newError)('Unknown load balancing strategy: ' + configuredValue);
      }
    }
  }]);
  return RoutingDriver;
}(_driver.Driver);
/**
 * @private
 * @returns {ConfiguredCustomResolver} new custom resolver that wraps the passed-in resolver function.
 *              If resolved function is not specified, it defaults to an identity resolver.
 */


function createHostNameResolver(config) {
  return new _configuredCustomResolver["default"](config.resolver);
}
/**
 * @private
 * @returns {object} the given config.
 */


function validateConfig(config) {
  if (config.trust === 'TRUST_ON_FIRST_USE') {
    throw (0, _error.newError)('The chosen trust mode is not compatible with a routing driver');
  }

  var resolver = config.resolver;

  if (resolver && typeof resolver !== 'function') {
    throw new TypeError("Configured resolver should be a function. Got: ".concat(resolver));
  }

  return config;
}

var _default = RoutingDriver;
exports["default"] = _default;