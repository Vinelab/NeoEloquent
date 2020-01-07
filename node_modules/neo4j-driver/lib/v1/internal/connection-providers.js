"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.SingleConnectionProvider = exports.LoadBalancer = exports.DirectConnectionProvider = void 0;

var _possibleConstructorReturn2 = _interopRequireDefault(require("@babel/runtime/helpers/possibleConstructorReturn"));

var _getPrototypeOf2 = _interopRequireDefault(require("@babel/runtime/helpers/getPrototypeOf"));

var _inherits2 = _interopRequireDefault(require("@babel/runtime/helpers/inherits"));

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _error = require("../error");

var _driver = require("../driver");

var _session = _interopRequireDefault(require("../session"));

var _routingTable = _interopRequireDefault(require("./routing-table"));

var _rediscovery = _interopRequireDefault(require("./rediscovery"));

var _routingUtil = _interopRequireDefault(require("./routing-util"));

var _node = require("./node");

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
var UNAUTHORIZED_ERROR_CODE = 'Neo.ClientError.Security.Unauthorized';

var ConnectionProvider =
/*#__PURE__*/
function () {
  function ConnectionProvider() {
    (0, _classCallCheck2["default"])(this, ConnectionProvider);
  }

  (0, _createClass2["default"])(ConnectionProvider, [{
    key: "acquireConnection",
    value: function acquireConnection(mode) {
      throw new Error('Abstract function');
    }
  }, {
    key: "_withAdditionalOnErrorCallback",
    value: function _withAdditionalOnErrorCallback(connectionPromise, driverOnErrorCallback) {
      // install error handler from the driver on the connection promise; this callback is installed separately
      // so that it does not handle errors, instead it is just an additional error reporting facility.
      connectionPromise["catch"](function (error) {
        driverOnErrorCallback(error);
      }); // return the original connection promise

      return connectionPromise;
    }
  }]);
  return ConnectionProvider;
}();

var DirectConnectionProvider =
/*#__PURE__*/
function (_ConnectionProvider) {
  (0, _inherits2["default"])(DirectConnectionProvider, _ConnectionProvider);

  function DirectConnectionProvider(address, connectionPool, driverOnErrorCallback) {
    var _this;

    (0, _classCallCheck2["default"])(this, DirectConnectionProvider);
    _this = (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(DirectConnectionProvider).call(this));
    _this._address = address;
    _this._connectionPool = connectionPool;
    _this._driverOnErrorCallback = driverOnErrorCallback;
    return _this;
  }

  (0, _createClass2["default"])(DirectConnectionProvider, [{
    key: "acquireConnection",
    value: function acquireConnection(mode) {
      var connectionPromise = this._connectionPool.acquire(this._address);

      return this._withAdditionalOnErrorCallback(connectionPromise, this._driverOnErrorCallback);
    }
  }]);
  return DirectConnectionProvider;
}(ConnectionProvider);

exports.DirectConnectionProvider = DirectConnectionProvider;

var LoadBalancer =
/*#__PURE__*/
function (_ConnectionProvider2) {
  (0, _inherits2["default"])(LoadBalancer, _ConnectionProvider2);

  function LoadBalancer(address, routingContext, connectionPool, loadBalancingStrategy, hostNameResolver, driverOnErrorCallback, log) {
    var _this2;

    (0, _classCallCheck2["default"])(this, LoadBalancer);
    _this2 = (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(LoadBalancer).call(this));
    _this2._seedRouter = address;
    _this2._routingTable = new _routingTable["default"]();
    _this2._rediscovery = new _rediscovery["default"](new _routingUtil["default"](routingContext));
    _this2._connectionPool = connectionPool;
    _this2._driverOnErrorCallback = driverOnErrorCallback;
    _this2._loadBalancingStrategy = loadBalancingStrategy;
    _this2._hostNameResolver = hostNameResolver;
    _this2._dnsResolver = new _node.HostNameResolver();
    _this2._log = log;
    _this2._useSeedRouter = true;
    return _this2;
  }

  (0, _createClass2["default"])(LoadBalancer, [{
    key: "acquireConnection",
    value: function acquireConnection(accessMode) {
      var _this3 = this;

      var connectionPromise = this._freshRoutingTable(accessMode).then(function (routingTable) {
        if (accessMode === _driver.READ) {
          var address = _this3._loadBalancingStrategy.selectReader(routingTable.readers);

          return _this3._acquireConnectionToServer(address, 'read');
        } else if (accessMode === _driver.WRITE) {
          var _address = _this3._loadBalancingStrategy.selectWriter(routingTable.writers);

          return _this3._acquireConnectionToServer(_address, 'write');
        } else {
          throw (0, _error.newError)('Illegal mode ' + accessMode);
        }
      });

      return this._withAdditionalOnErrorCallback(connectionPromise, this._driverOnErrorCallback);
    }
  }, {
    key: "forget",
    value: function forget(address) {
      this._routingTable.forget(address);

      this._connectionPool.purge(address);
    }
  }, {
    key: "forgetWriter",
    value: function forgetWriter(address) {
      this._routingTable.forgetWriter(address);
    }
  }, {
    key: "_acquireConnectionToServer",
    value: function _acquireConnectionToServer(address, serverName) {
      if (!address) {
        return Promise.reject((0, _error.newError)("Failed to obtain connection towards ".concat(serverName, " server. Known routing table is: ").concat(this._routingTable), _error.SESSION_EXPIRED));
      }

      return this._connectionPool.acquire(address);
    }
  }, {
    key: "_freshRoutingTable",
    value: function _freshRoutingTable(accessMode) {
      var currentRoutingTable = this._routingTable;

      if (!currentRoutingTable.isStaleFor(accessMode)) {
        return Promise.resolve(currentRoutingTable);
      }

      this._log.info("Routing table is stale for ".concat(accessMode, ": ").concat(currentRoutingTable));

      return this._refreshRoutingTable(currentRoutingTable);
    }
  }, {
    key: "_refreshRoutingTable",
    value: function _refreshRoutingTable(currentRoutingTable) {
      var knownRouters = currentRoutingTable.routers;

      if (this._useSeedRouter) {
        return this._fetchRoutingTableFromSeedRouterFallbackToKnownRouters(knownRouters, currentRoutingTable);
      }

      return this._fetchRoutingTableFromKnownRoutersFallbackToSeedRouter(knownRouters, currentRoutingTable);
    }
  }, {
    key: "_fetchRoutingTableFromSeedRouterFallbackToKnownRouters",
    value: function _fetchRoutingTableFromSeedRouterFallbackToKnownRouters(knownRouters, currentRoutingTable) {
      var _this4 = this;

      // we start with seed router, no routers were probed before
      var seenRouters = [];
      return this._fetchRoutingTableUsingSeedRouter(seenRouters, this._seedRouter).then(function (newRoutingTable) {
        if (newRoutingTable) {
          _this4._useSeedRouter = false;
          return newRoutingTable;
        } // seed router did not return a valid routing table - try to use other known routers


        return _this4._fetchRoutingTableUsingKnownRouters(knownRouters, currentRoutingTable);
      }).then(function (newRoutingTable) {
        _this4._applyRoutingTableIfPossible(newRoutingTable);

        return newRoutingTable;
      });
    }
  }, {
    key: "_fetchRoutingTableFromKnownRoutersFallbackToSeedRouter",
    value: function _fetchRoutingTableFromKnownRoutersFallbackToSeedRouter(knownRouters, currentRoutingTable) {
      var _this5 = this;

      return this._fetchRoutingTableUsingKnownRouters(knownRouters, currentRoutingTable).then(function (newRoutingTable) {
        if (newRoutingTable) {
          return newRoutingTable;
        } // none of the known routers returned a valid routing table - try to use seed router address for rediscovery


        return _this5._fetchRoutingTableUsingSeedRouter(knownRouters, _this5._seedRouter);
      }).then(function (newRoutingTable) {
        _this5._applyRoutingTableIfPossible(newRoutingTable);

        return newRoutingTable;
      });
    }
  }, {
    key: "_fetchRoutingTableUsingKnownRouters",
    value: function _fetchRoutingTableUsingKnownRouters(knownRouters, currentRoutingTable) {
      return this._fetchRoutingTable(knownRouters, currentRoutingTable).then(function (newRoutingTable) {
        if (newRoutingTable) {
          // one of the known routers returned a valid routing table - use it
          return newRoutingTable;
        } // returned routing table was undefined, this means a connection error happened and the last known
        // router did not return a valid routing table, so we need to forget it


        var lastRouterIndex = knownRouters.length - 1;

        LoadBalancer._forgetRouter(currentRoutingTable, knownRouters, lastRouterIndex);

        return null;
      });
    }
  }, {
    key: "_fetchRoutingTableUsingSeedRouter",
    value: function _fetchRoutingTableUsingSeedRouter(seenRouters, seedRouter) {
      var _this6 = this;

      var resolvedAddresses = this._resolveSeedRouter(seedRouter);

      return resolvedAddresses.then(function (resolvedRouterAddresses) {
        // filter out all addresses that we've already tried
        var newAddresses = resolvedRouterAddresses.filter(function (address) {
          return seenRouters.indexOf(address) < 0;
        });
        return _this6._fetchRoutingTable(newAddresses, null);
      });
    }
  }, {
    key: "_resolveSeedRouter",
    value: function _resolveSeedRouter(seedRouter) {
      var _this7 = this;

      var customResolution = this._hostNameResolver.resolve(seedRouter);

      var dnsResolutions = customResolution.then(function (resolvedAddresses) {
        return Promise.all(resolvedAddresses.map(function (address) {
          return _this7._dnsResolver.resolve(address);
        }));
      });
      return dnsResolutions.then(function (results) {
        return [].concat.apply([], results);
      });
    }
  }, {
    key: "_fetchRoutingTable",
    value: function _fetchRoutingTable(routerAddresses, routingTable) {
      var _this8 = this;

      return routerAddresses.reduce(function (refreshedTablePromise, currentRouter, currentIndex) {
        return refreshedTablePromise.then(function (newRoutingTable) {
          if (newRoutingTable) {
            // valid routing table was fetched - just return it, try next router otherwise
            return newRoutingTable;
          } else {
            // returned routing table was undefined, this means a connection error happened and we need to forget the
            // previous router and try the next one
            var previousRouterIndex = currentIndex - 1;

            LoadBalancer._forgetRouter(routingTable, routerAddresses, previousRouterIndex);
          } // try next router


          return _this8._createSessionForRediscovery(currentRouter).then(function (session) {
            if (session) {
              return _this8._rediscovery.lookupRoutingTableOnRouter(session, currentRouter)["catch"](function (error) {
                _this8._log.warn("unable to fetch routing table because of an error ".concat(error));

                return null;
              });
            } else {
              // unable to acquire connection and create session towards the current router
              // return null to signal that the next router should be tried
              return null;
            }
          });
        });
      }, Promise.resolve(null));
    }
  }, {
    key: "_createSessionForRediscovery",
    value: function _createSessionForRediscovery(routerAddress) {
      return this._connectionPool.acquire(routerAddress).then(function (connection) {
        var connectionProvider = new SingleConnectionProvider(connection);
        return new _session["default"](_driver.READ, connectionProvider);
      })["catch"](function (error) {
        // unable to acquire connection towards the given router
        if (error && error.code === UNAUTHORIZED_ERROR_CODE) {
          // auth error is a sign of a configuration issue, rediscovery should not proceed
          throw error;
        }

        return null;
      });
    }
  }, {
    key: "_applyRoutingTableIfPossible",
    value: function _applyRoutingTableIfPossible(newRoutingTable) {
      if (!newRoutingTable) {
        // none of routing servers returned valid routing table, throw exception
        throw (0, _error.newError)("Could not perform discovery. No routing servers available. Known routing table: ".concat(this._routingTable), _error.SERVICE_UNAVAILABLE);
      }

      if (newRoutingTable.writers.length === 0) {
        // use seed router next time. this is important when cluster is partitioned. it tries to make sure driver
        // does not always get routing table without writers because it talks exclusively to a minority partition
        this._useSeedRouter = true;
      }

      this._updateRoutingTable(newRoutingTable);
    }
  }, {
    key: "_updateRoutingTable",
    value: function _updateRoutingTable(newRoutingTable) {
      // close old connections to servers not present in the new routing table
      this._connectionPool.keepAll(newRoutingTable.allServers()); // make this driver instance aware of the new table


      this._routingTable = newRoutingTable;

      this._log.info("Updated routing table ".concat(newRoutingTable));
    }
  }], [{
    key: "_forgetRouter",
    value: function _forgetRouter(routingTable, routersArray, routerIndex) {
      var address = routersArray[routerIndex];

      if (routingTable && address) {
        routingTable.forgetRouter(address);
      }
    }
  }]);
  return LoadBalancer;
}(ConnectionProvider);

exports.LoadBalancer = LoadBalancer;

var SingleConnectionProvider =
/*#__PURE__*/
function (_ConnectionProvider3) {
  (0, _inherits2["default"])(SingleConnectionProvider, _ConnectionProvider3);

  function SingleConnectionProvider(connection) {
    var _this9;

    (0, _classCallCheck2["default"])(this, SingleConnectionProvider);
    _this9 = (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(SingleConnectionProvider).call(this));
    _this9._connection = connection;
    return _this9;
  }

  (0, _createClass2["default"])(SingleConnectionProvider, [{
    key: "acquireConnection",
    value: function acquireConnection(mode) {
      var connection = this._connection;
      this._connection = null;
      return Promise.resolve(connection);
    }
  }]);
  return SingleConnectionProvider;
}(ConnectionProvider);

exports.SingleConnectionProvider = SingleConnectionProvider;