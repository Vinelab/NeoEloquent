"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _node = require("./node");

var _chunking = require("./chunking");

var _error = require("./../error");

var _channelConfig = _interopRequireDefault(require("./channel-config"));

var _serverVersion = require("./server-version");

var _protocolHandshaker = _interopRequireDefault(require("./protocol-handshaker"));

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
// Signature bytes for each response message type
var SUCCESS = 0x70; // 0111 0000 // SUCCESS <metadata>

var RECORD = 0x71; // 0111 0001 // RECORD <value>

var IGNORED = 0x7e; // 0111 1110 // IGNORED <metadata>

var FAILURE = 0x7f; // 0111 1111 // FAILURE <metadata>

function NO_OP() {}

var NO_OP_OBSERVER = {
  onNext: NO_OP,
  onCompleted: NO_OP,
  onError: NO_OP
};
var idGenerator = 0;

var Connection =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {Channel} channel - channel with a 'write' function and a 'onmessage' callback property.
   * @param {ConnectionErrorHandler} errorHandler the error handler.
   * @param {ServerAddress} address - the server address to connect to.
   * @param {Logger} log - the configured logger.
   * @param {boolean} disableLosslessIntegers if this connection should convert all received integers to native JS numbers.
   */
  function Connection(channel, errorHandler, address, log) {
    var disableLosslessIntegers = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : false;
    (0, _classCallCheck2["default"])(this, Connection);
    this.id = idGenerator++;
    this.address = address;
    this.server = {
      address: address.asHostPort()
    };
    this.creationTimestamp = Date.now();
    this._errorHandler = errorHandler;
    this._disableLosslessIntegers = disableLosslessIntegers;
    this._pendingObservers = [];
    this._currentObserver = undefined;
    this._ch = channel;
    this._dechunker = new _chunking.Dechunker();
    this._chunker = new _chunking.Chunker(channel);
    this._log = log; // connection from the database, returned in response for HELLO message and might not be available

    this._dbConnectionId = null; // bolt protocol is initially not initialized

    this._protocol = null; // error extracted from a FAILURE message

    this._currentFailure = null; // Set to true on fatal errors, to get this out of connection pool.

    this._isBroken = false;

    if (this._log.isDebugEnabled()) {
      this._log.debug("".concat(this, " created towards ").concat(address));
    }
  }
  /**
   * Crete new connection to the provided address. Returned connection is not connected.
   * @param {ServerAddress} address - the Bolt endpoint to connect to.
   * @param {object} config - this driver configuration.
   * @param {ConnectionErrorHandler} errorHandler - the error handler for connection errors.
   * @param {Logger} log - configured logger.
   * @return {Connection} - new connection.
   */


  (0, _createClass2["default"])(Connection, [{
    key: "connect",

    /**
     * Connect to the target address, negotiate Bolt protocol and send initialization message.
     * @param {string} userAgent the user agent for this driver.
     * @param {object} authToken the object containing auth information.
     * @return {Promise<Connection>} promise resolved with the current connection if connection is successful. Rejected promise otherwise.
     */
    value: function connect(userAgent, authToken) {
      var _this = this;

      return this._negotiateProtocol().then(function () {
        return _this._initialize(userAgent, authToken);
      });
    }
    /**
     * Execute Bolt protocol handshake to initialize the protocol version.
     * @return {Promise<Connection>} promise resolved with the current connection if handshake is successful. Rejected promise otherwise.
     */

  }, {
    key: "_negotiateProtocol",
    value: function _negotiateProtocol() {
      var _this2 = this;

      var protocolHandshaker = new _protocolHandshaker["default"](this, this._ch, this._chunker, this._disableLosslessIntegers, this._log);
      return new Promise(function (resolve, reject) {
        var handshakeErrorHandler = function handshakeErrorHandler(error) {
          _this2._handleFatalError(error);

          reject(error);
        };

        _this2._ch.onerror = handshakeErrorHandler.bind(_this2);

        if (_this2._ch._error) {
          // channel is already broken
          handshakeErrorHandler(_this2._ch._error);
        }

        _this2._ch.onmessage = function (buffer) {
          try {
            // read the response buffer and initialize the protocol
            _this2._protocol = protocolHandshaker.createNegotiatedProtocol(buffer); // reset the error handler to just handle errors and forget about the handshake promise

            _this2._ch.onerror = _this2._handleFatalError.bind(_this2); // Ok, protocol running. Simply forward all messages to the dechunker

            _this2._ch.onmessage = function (buf) {
              return _this2._dechunker.write(buf);
            }; // setup dechunker to dechunk messages and forward them to the message handler


            _this2._dechunker.onmessage = function (buf) {
              _this2._handleMessage(_this2._protocol.unpacker().unpack(buf));
            }; // forward all pending bytes to the dechunker


            if (buffer.hasRemaining()) {
              _this2._dechunker.write(buffer.readSlice(buffer.remaining()));
            }

            resolve(_this2);
          } catch (e) {
            _this2._handleFatalError(e);

            reject(e);
          }
        };

        protocolHandshaker.writeHandshakeRequest();
      });
    }
    /**
     * Perform protocol-specific initialization which includes authentication.
     * @param {string} userAgent the user agent for this driver.
     * @param {object} authToken the object containing auth information.
     * @return {Promise<Connection>} promise resolved with the current connection if initialization is successful. Rejected promise otherwise.
     */

  }, {
    key: "_initialize",
    value: function _initialize(userAgent, authToken) {
      var _this3 = this;

      return new Promise(function (resolve, reject) {
        var observer = new InitializationObserver(_this3, resolve, reject);

        _this3._protocol.initialize(userAgent, authToken, observer);
      });
    }
    /**
     * Get the Bolt protocol for the connection.
     * @return {BoltProtocol} the protocol.
     */

  }, {
    key: "protocol",
    value: function protocol() {
      return this._protocol;
    }
    /**
     * Write a message to the network channel.
     * @param {RequestMessage} message the message to write.
     * @param {StreamObserver} observer the response observer.
     * @param {boolean} flush `true` if flush should happen after the message is written to the buffer.
     */

  }, {
    key: "write",
    value: function write(message, observer, flush) {
      var _this4 = this;

      var queued = this._queueObserver(observer);

      if (queued) {
        if (this._log.isDebugEnabled()) {
          this._log.debug("".concat(this, " C: ").concat(message));
        }

        this._protocol.packer().packStruct(message.signature, message.fields.map(function (field) {
          return _this4._packable(field);
        }), function (err) {
          return _this4._handleFatalError(err);
        });

        this._chunker.messageBoundary();

        if (flush) {
          this._chunker.flush();
        }
      }
    }
    /**
     * "Fatal" means the connection is dead. Only call this if something
     * happens that cannot be recovered from. This will lead to all subscribers
     * failing, and the connection getting ejected from the session pool.
     *
     * @param error an error object, forwarded to all current and future subscribers
     */

  }, {
    key: "_handleFatalError",
    value: function _handleFatalError(error) {
      this._isBroken = true;
      this._error = this._errorHandler.handleAndTransformError(error, this.address);

      if (this._log.isErrorEnabled()) {
        this._log.error("".concat(this, " experienced a fatal error ").concat(JSON.stringify(this._error)));
      }

      if (this._currentObserver && this._currentObserver.onError) {
        this._currentObserver.onError(this._error);
      }

      while (this._pendingObservers.length > 0) {
        var observer = this._pendingObservers.shift();

        if (observer && observer.onError) {
          observer.onError(this._error);
        }
      }
    }
  }, {
    key: "_handleMessage",
    value: function _handleMessage(msg) {
      if (this._isBroken) {
        // ignore all incoming messages when this connection is broken. all previously pending observers failed
        // with the fatal error. all future observers will fail with same fatal error.
        return;
      }

      var payload = msg.fields[0];

      switch (msg.signature) {
        case RECORD:
          if (this._log.isDebugEnabled()) {
            this._log.debug("".concat(this, " S: RECORD ").concat(JSON.stringify(msg)));
          }

          this._currentObserver.onNext(payload);

          break;

        case SUCCESS:
          if (this._log.isDebugEnabled()) {
            this._log.debug("".concat(this, " S: SUCCESS ").concat(JSON.stringify(msg)));
          }

          try {
            var metadata = this._protocol.transformMetadata(payload);

            this._currentObserver.onCompleted(metadata);
          } finally {
            this._updateCurrentObserver();
          }

          break;

        case FAILURE:
          if (this._log.isDebugEnabled()) {
            this._log.debug("".concat(this, " S: FAILURE ").concat(JSON.stringify(msg)));
          }

          try {
            var error = (0, _error.newError)(payload.message, payload.code);
            this._currentFailure = this._errorHandler.handleAndTransformError(error, this.address);

            this._currentObserver.onError(this._currentFailure);
          } finally {
            this._updateCurrentObserver(); // Things are now broken. Pending observers will get FAILURE messages routed until we are done handling this failure.


            this._resetOnFailure();
          }

          break;

        case IGNORED:
          if (this._log.isDebugEnabled()) {
            this._log.debug("".concat(this, " S: IGNORED ").concat(JSON.stringify(msg)));
          }

          try {
            if (this._currentFailure && this._currentObserver.onError) {
              this._currentObserver.onError(this._currentFailure);
            } else if (this._currentObserver.onError) {
              this._currentObserver.onError((0, _error.newError)('Ignored either because of an error or RESET'));
            }
          } finally {
            this._updateCurrentObserver();
          }

          break;

        default:
          this._handleFatalError((0, _error.newError)('Unknown Bolt protocol message: ' + msg));

      }
    }
    /**
     * Send a RESET-message to the database. Message is immediately flushed to the network.
     * @return {Promise<void>} promise resolved when SUCCESS-message response arrives, or failed when other response messages arrives.
     */

  }, {
    key: "resetAndFlush",
    value: function resetAndFlush() {
      var _this5 = this;

      return new Promise(function (resolve, reject) {
        _this5._protocol.reset({
          onNext: function onNext(record) {
            var neo4jError = _this5._handleProtocolError('Received RECORD as a response for RESET: ' + JSON.stringify(record));

            reject(neo4jError);
          },
          onError: function onError(error) {
            if (_this5._isBroken) {
              // handling a fatal error, no need to raise a protocol violation
              reject(error);
            } else {
              var neo4jError = _this5._handleProtocolError('Received FAILURE as a response for RESET: ' + error);

              reject(neo4jError);
            }
          },
          onCompleted: function onCompleted() {
            resolve();
          }
        });
      });
    }
  }, {
    key: "_resetOnFailure",
    value: function _resetOnFailure() {
      var _this6 = this;

      this._protocol.reset({
        onNext: function onNext(record) {
          _this6._handleProtocolError('Received RECORD as a response for RESET: ' + JSON.stringify(record));
        },
        // clear the current failure when response for RESET is received
        onError: function onError() {
          _this6._currentFailure = null;
        },
        onCompleted: function onCompleted() {
          _this6._currentFailure = null;
        }
      });
    }
  }, {
    key: "_queueObserver",
    value: function _queueObserver(observer) {
      if (this._isBroken) {
        if (observer && observer.onError) {
          observer.onError(this._error);
        }

        return false;
      }

      observer = observer || NO_OP_OBSERVER;
      observer.onCompleted = observer.onCompleted || NO_OP;
      observer.onError = observer.onError || NO_OP;
      observer.onNext = observer.onNext || NO_OP;

      if (this._currentObserver === undefined) {
        this._currentObserver = observer;
      } else {
        this._pendingObservers.push(observer);
      }

      return true;
    }
    /*
     * Pop next pending observer form the list of observers and make it current observer.
     * @protected
     */

  }, {
    key: "_updateCurrentObserver",
    value: function _updateCurrentObserver() {
      this._currentObserver = this._pendingObservers.shift();
    }
    /** Check if this connection is in working condition */

  }, {
    key: "isOpen",
    value: function isOpen() {
      return !this._isBroken && this._ch._open;
    }
    /**
     * Call close on the channel.
     * @param {function} cb - Function to call on close.
     */

  }, {
    key: "close",
    value: function close() {
      var _this7 = this;

      var cb = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : function () {
        return null;
      };

      if (this._log.isDebugEnabled()) {
        this._log.debug("".concat(this, " closing"));
      }

      if (this._protocol && this.isOpen()) {
        // protocol has been initialized and this connection is healthy
        // notify the database about the upcoming close of the connection
        this._protocol.prepareToClose(NO_OP_OBSERVER);
      }

      this._ch.close(function () {
        if (_this7._log.isDebugEnabled()) {
          _this7._log.debug("".concat(_this7, " closed"));
        }

        cb();
      });
    }
  }, {
    key: "toString",
    value: function toString() {
      var dbConnectionId = this._dbConnectionId || '';
      return "Connection [".concat(this.id, "][").concat(dbConnectionId, "]");
    }
  }, {
    key: "_packable",
    value: function _packable(value) {
      var _this8 = this;

      return this._protocol.packer().packable(value, function (err) {
        return _this8._handleFatalError(err);
      });
    }
  }, {
    key: "_handleProtocolError",
    value: function _handleProtocolError(message) {
      this._currentFailure = null;

      this._updateCurrentObserver();

      var error = (0, _error.newError)(message, _error.PROTOCOL_ERROR);

      this._handleFatalError(error);

      return error;
    }
  }], [{
    key: "create",
    value: function create(address, config, errorHandler, log) {
      var channelConfig = new _channelConfig["default"](address, config, errorHandler.errorCode());
      return new Connection(new _node.Channel(channelConfig), errorHandler, address, log, config.disableLosslessIntegers);
    }
  }]);
  return Connection;
}();

exports["default"] = Connection;

var InitializationObserver =
/*#__PURE__*/
function () {
  function InitializationObserver(connection, onSuccess, onError) {
    (0, _classCallCheck2["default"])(this, InitializationObserver);
    this._connection = connection;
    this._onSuccess = onSuccess;
    this._onError = onError;
  }

  (0, _createClass2["default"])(InitializationObserver, [{
    key: "onNext",
    value: function onNext(record) {
      this.onError((0, _error.newError)('Received RECORD when initializing ' + JSON.stringify(record)));
    }
  }, {
    key: "onError",
    value: function onError(error) {
      this._connection._updateCurrentObserver(); // make sure this exact observer will not be called again


      this._connection._handleFatalError(error); // initialization errors are fatal


      this._onError(error);
    }
  }, {
    key: "onCompleted",
    value: function onCompleted(metadata) {
      if (metadata) {
        // read server version from the response metadata, if it is available
        var serverVersion = metadata.server;

        if (!this._connection.server.version) {
          this._connection.server.version = serverVersion;

          var version = _serverVersion.ServerVersion.fromString(serverVersion);

          if (version.compareTo(_serverVersion.VERSION_3_2_0) < 0) {
            this._connection.protocol().packer().disableByteArrays();
          }
        } // read database connection id from the response metadata, if it is available


        var dbConnectionId = metadata.connection_id;

        if (!this._connection._dbConnectionId) {
          this._connection._dbConnectionId = dbConnectionId;
        }
      }

      this._onSuccess(this._connection);
    }
  }]);
  return InitializationObserver;
}();