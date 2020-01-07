"use strict";

var _interopRequireWildcard = require("@babel/runtime/helpers/interopRequireWildcard");

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = exports.WRITE = exports.READ = exports.Driver = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _session = _interopRequireDefault(require("./session"));

var _pool = _interopRequireDefault(require("./internal/pool"));

var _connection = _interopRequireDefault(require("./internal/connection"));

var _error = require("./error");

var _connectionProviders = require("./internal/connection-providers");

var _bookmark = _interopRequireDefault(require("./internal/bookmark"));

var _connectivityVerifier = _interopRequireDefault(require("./internal/connectivity-verifier"));

var _poolConfig = _interopRequireWildcard(require("./internal/pool-config"));

var _logger = _interopRequireDefault(require("./internal/logger"));

var _connectionErrorHandler = _interopRequireDefault(require("./internal/connection-error-handler"));

var _constants = require("./internal/constants");

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
var DEFAULT_MAX_CONNECTION_LIFETIME = 60 * 60 * 1000; // 1 hour

/**
 * Constant that represents read session access mode.
 * Should be used like this: `driver.session(neo4j.session.READ)`.
 * @type {string}
 */

var READ = _constants.ACCESS_MODE_READ;
/**
 * Constant that represents write session access mode.
 * Should be used like this: `driver.session(neo4j.session.WRITE)`.
 * @type {string}
 */

exports.READ = READ;
var WRITE = _constants.ACCESS_MODE_WRITE;
exports.WRITE = WRITE;
var idGenerator = 0;
/**
 * A driver maintains one or more {@link Session}s with a remote
 * Neo4j instance. Through the {@link Session}s you can send statements
 * and retrieve results from the database.
 *
 * Drivers are reasonably expensive to create - you should strive to keep one
 * driver instance around per Neo4j Instance you connect to.
 *
 * @access public
 */

var Driver =
/*#__PURE__*/
function () {
  /**
   * You should not be calling this directly, instead use {@link driver}.
   * @constructor
   * @param {ServerAddress} address
   * @param {string} userAgent
   * @param {object} authToken
   * @param {object} config
   * @protected
   */
  function Driver(address, userAgent) {
    var authToken = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
    var config = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : {};
    (0, _classCallCheck2["default"])(this, Driver);
    sanitizeConfig(config);
    this._id = idGenerator++;
    this._address = address;
    this._userAgent = userAgent;
    this._openConnections = {};
    this._authToken = authToken;
    this._config = config;
    this._log = _logger["default"].create(config);
    this._pool = new _pool["default"]({
      create: this._createConnection.bind(this),
      destroy: this._destroyConnection.bind(this),
      validate: this._validateConnection.bind(this),
      installIdleObserver: this._installIdleObserverOnConnection.bind(this),
      removeIdleObserver: this._removeIdleObserverOnConnection.bind(this),
      config: _poolConfig["default"].fromDriverConfig(config),
      log: this._log
    });
    /**
     * Reference to the connection provider. Initialized lazily by {@link _getOrCreateConnectionProvider}.
     * @type {ConnectionProvider}
     * @protected
     */

    this._connectionProvider = null;
    this._onCompleted = null;

    this._afterConstruction();
  }
  /**
   * @protected
   */


  (0, _createClass2["default"])(Driver, [{
    key: "_afterConstruction",
    value: function _afterConstruction() {
      this._log.info("Direct driver ".concat(this._id, " created for server address ").concat(this._address));
    }
    /**
     * Get the installed connectivity verification callback.
     * @return {null|function}
     * @deprecated driver can be used directly once instantiated, use of this callback is not required.
     */

  }, {
    key: "_createConnection",

    /**
     * Create a new connection and initialize it.
     * @return {Promise<Connection>} promise resolved with a new connection or rejected when failed to connect.
     * @access private
     */
    value: function _createConnection(address, release) {
      var _this = this;

      var connection = _connection["default"].create(address, this._config, this._createConnectionErrorHandler(), this._log);

      connection._release = function () {
        return release(address, connection);
      };

      this._openConnections[connection.id] = connection;
      return connection.connect(this._userAgent, this._authToken)["catch"](function (error) {
        if (_this.onError) {
          // notify Driver.onError callback about connection initialization errors
          _this.onError(error);
        } // let's destroy this connection


        _this._destroyConnection(connection); // propagate the error because connection failed to connect / initialize


        throw error;
      });
    }
    /**
     * Check that a connection is usable
     * @return {boolean} true if the connection is open
     * @access private
     **/

  }, {
    key: "_validateConnection",
    value: function _validateConnection(conn) {
      if (!conn.isOpen()) {
        return false;
      }

      var maxConnectionLifetime = this._config.maxConnectionLifetime;
      var lifetime = Date.now() - conn.creationTimestamp;
      return lifetime <= maxConnectionLifetime;
    }
  }, {
    key: "_installIdleObserverOnConnection",
    value: function _installIdleObserverOnConnection(conn, observer) {
      conn._queueObserver(observer);
    }
  }, {
    key: "_removeIdleObserverOnConnection",
    value: function _removeIdleObserverOnConnection(conn) {
      conn._updateCurrentObserver();
    }
    /**
     * Dispose of a connection.
     * @return {Connection} the connection to dispose.
     * @access private
     */

  }, {
    key: "_destroyConnection",
    value: function _destroyConnection(conn) {
      delete this._openConnections[conn.id];
      conn.close();
    }
    /**
     * Acquire a session to communicate with the database. The session will
     * borrow connections from the underlying connection pool as required and
     * should be considered lightweight and disposable.
     *
     * This comes with some responsibility - make sure you always call
     * {@link close} when you are done using a session, and likewise,
     * make sure you don't close your session before you are done using it. Once
     * it is closed, the underlying connection will be released to the connection
     * pool and made available for others to use.
     *
     * @param {string} [mode=WRITE] the access mode of this session, allowed values are {@link READ} and {@link WRITE}.
     * @param {string|string[]} [bookmarkOrBookmarks=null] the initial reference or references to some previous
     * transactions. Value is optional and absence indicates that that the bookmarks do not exist or are unknown.
     * @return {Session} new session.
     */

  }, {
    key: "session",
    value: function session(mode, bookmarkOrBookmarks) {
      var sessionMode = Driver._validateSessionMode(mode);

      var connectionProvider = this._getOrCreateConnectionProvider();

      var bookmark = bookmarkOrBookmarks ? new _bookmark["default"](bookmarkOrBookmarks) : _bookmark["default"].empty();
      return new _session["default"](sessionMode, connectionProvider, bookmark, this._config);
    }
  }, {
    key: "_createConnectionProvider",
    // Extension point
    value: function _createConnectionProvider(address, connectionPool, driverOnErrorCallback) {
      return new _connectionProviders.DirectConnectionProvider(address, connectionPool, driverOnErrorCallback);
    } // Extension point

  }, {
    key: "_createConnectionErrorHandler",
    value: function _createConnectionErrorHandler() {
      return new _connectionErrorHandler["default"](_error.SERVICE_UNAVAILABLE);
    }
  }, {
    key: "_getOrCreateConnectionProvider",
    value: function _getOrCreateConnectionProvider() {
      if (!this._connectionProvider) {
        var driverOnErrorCallback = this._driverOnErrorCallback.bind(this);

        this._connectionProvider = this._createConnectionProvider(this._address, this._pool, driverOnErrorCallback);
      }

      return this._connectionProvider;
    }
  }, {
    key: "_driverOnErrorCallback",
    value: function _driverOnErrorCallback(error) {
      var userDefinedOnErrorCallback = this.onError;

      if (userDefinedOnErrorCallback && error.code === _error.SERVICE_UNAVAILABLE) {
        userDefinedOnErrorCallback(error);
      } else {// we don't need to tell the driver about this error
      }
    }
    /**
     * Close all open sessions and other associated resources. You should
     * make sure to use this when you are done with this driver instance.
     * @return undefined
     */

  }, {
    key: "close",
    value: function close() {
      this._log.info("Driver ".concat(this._id, " closing"));

      try {
        // purge all idle connections in the connection pool
        this._pool.purgeAll();
      } finally {
        // then close all connections driver has ever created
        // it is needed to close connections that are active right now and are acquired from the pool
        for (var connectionId in this._openConnections) {
          if (this._openConnections.hasOwnProperty(connectionId)) {
            this._openConnections[connectionId].close();
          }
        }
      }
    }
  }, {
    key: "onCompleted",
    get: function get() {
      return this._onCompleted;
    }
    /**
     * Install a connectivity verification callback.
     * @param {null|function} callback the new function to be notified about successful connection.
     * @deprecated driver can be used directly once instantiated, use of this callback is not required.
     */
    ,
    set: function set(callback) {
      this._onCompleted = callback;

      if (this._onCompleted) {
        var connectionProvider = this._getOrCreateConnectionProvider();

        var connectivityVerifier = new _connectivityVerifier["default"](connectionProvider, this._onCompleted);
        connectivityVerifier.verify();
      }
    }
  }], [{
    key: "_validateSessionMode",
    value: function _validateSessionMode(rawMode) {
      var mode = rawMode || WRITE;

      if (mode !== _constants.ACCESS_MODE_READ && mode !== _constants.ACCESS_MODE_WRITE) {
        throw (0, _error.newError)('Illegal session mode ' + mode);
      }

      return mode;
    }
  }]);
  return Driver;
}();
/**
 * @private
 */


exports.Driver = Driver;

function sanitizeConfig(config) {
  config.maxConnectionLifetime = sanitizeIntValue(config.maxConnectionLifetime, DEFAULT_MAX_CONNECTION_LIFETIME);
  config.maxConnectionPoolSize = sanitizeIntValue(config.maxConnectionPoolSize, _poolConfig.DEFAULT_MAX_SIZE);
  config.connectionAcquisitionTimeout = sanitizeIntValue(config.connectionAcquisitionTimeout, _poolConfig.DEFAULT_ACQUISITION_TIMEOUT);
}

function sanitizeIntValue(rawValue, defaultWhenAbsent) {
  var sanitizedValue = parseInt(rawValue, 10);

  if (sanitizedValue > 0 || sanitizedValue === 0) {
    return sanitizedValue;
  } else if (sanitizedValue < 0) {
    return Number.MAX_SAFE_INTEGER;
  } else {
    return defaultWhenAbsent;
  }
}

var _default = Driver;
exports["default"] = _default;