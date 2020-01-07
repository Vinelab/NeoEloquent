"use strict";

var _interopRequireWildcard = require("@babel/runtime/helpers/interopRequireWildcard");

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _requestMessage = _interopRequireDefault(require("./request-message"));

var v1 = _interopRequireWildcard(require("./packstream-v1"));

var _error = require("../error");

var _bookmark = _interopRequireDefault(require("./bookmark"));

var _txConfig = _interopRequireDefault(require("./tx-config"));

var _constants = require("./constants");

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
var BoltProtocol =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {Connection} connection the connection.
   * @param {Chunker} chunker the chunker.
   * @param {boolean} disableLosslessIntegers if this connection should convert all received integers to native JS numbers.
   */
  function BoltProtocol(connection, chunker, disableLosslessIntegers) {
    (0, _classCallCheck2["default"])(this, BoltProtocol);
    this._connection = connection;
    this._packer = this._createPacker(chunker);
    this._unpacker = this._createUnpacker(disableLosslessIntegers);
  }
  /**
   * Get the packer.
   * @return {Packer} the protocol's packer.
   */


  (0, _createClass2["default"])(BoltProtocol, [{
    key: "packer",
    value: function packer() {
      return this._packer;
    }
    /**
     * Get the unpacker.
     * @return {Unpacker} the protocol's unpacker.
     */

  }, {
    key: "unpacker",
    value: function unpacker() {
      return this._unpacker;
    }
    /**
     * Transform metadata received in SUCCESS message before it is passed to the handler.
     * @param {object} metadata the received metadata.
     * @return {object} transformed metadata.
     */

  }, {
    key: "transformMetadata",
    value: function transformMetadata(metadata) {
      return metadata;
    }
    /**
     * Perform initialization and authentication of the underlying connection.
     * @param {string} clientName the client name.
     * @param {object} authToken the authentication token.
     * @param {StreamObserver} observer the response observer.
     */

  }, {
    key: "initialize",
    value: function initialize(clientName, authToken, observer) {
      var message = _requestMessage["default"].init(clientName, authToken);

      this._connection.write(message, observer, true);
    }
  }, {
    key: "prepareToClose",
    value: function prepareToClose(observer) {} // no need to notify the database in this protocol version

    /**
     * Begin an explicit transaction.
     * @param {Bookmark} bookmark the bookmark.
     * @param {TxConfig} txConfig the configuration.
     * @param {string} mode the access mode.
     * @param {StreamObserver} observer the response observer.
     */

  }, {
    key: "beginTransaction",
    value: function beginTransaction(bookmark, txConfig, mode, observer) {
      assertTxConfigIsEmpty(txConfig, this._connection, observer);

      var runMessage = _requestMessage["default"].run('BEGIN', bookmark.asBeginTransactionParameters());

      var pullAllMessage = _requestMessage["default"].pullAll();

      this._connection.write(runMessage, observer, false);

      this._connection.write(pullAllMessage, observer, false);
    }
    /**
     * Commit the explicit transaction.
     * @param {StreamObserver} observer the response observer.
     */

  }, {
    key: "commitTransaction",
    value: function commitTransaction(observer) {
      // WRITE access mode is used as a place holder here, it has
      // no effect on behaviour for Bolt V1 & V2
      this.run('COMMIT', {}, _bookmark["default"].empty(), _txConfig["default"].empty(), _constants.ACCESS_MODE_WRITE, observer);
    }
    /**
     * Rollback the explicit transaction.
     * @param {StreamObserver} observer the response observer.
     */

  }, {
    key: "rollbackTransaction",
    value: function rollbackTransaction(observer) {
      // WRITE access mode is used as a place holder here, it has
      // no effect on behaviour for Bolt V1 & V2
      this.run('ROLLBACK', {}, _bookmark["default"].empty(), _txConfig["default"].empty(), _constants.ACCESS_MODE_WRITE, observer);
    }
    /**
     * Send a Cypher statement through the underlying connection.
     * @param {string} statement the cypher statement.
     * @param {object} parameters the statement parameters.
     * @param {Bookmark} bookmark the bookmark.
     * @param {TxConfig} txConfig the auto-commit transaction configuration.
     * @param {string} mode the access mode.
     * @param {StreamObserver} observer the response observer.
     */

  }, {
    key: "run",
    value: function run(statement, parameters, bookmark, txConfig, mode, observer) {
      // bookmark and mode are ignored in this versioon of the protocol
      assertTxConfigIsEmpty(txConfig, this._connection, observer);

      var runMessage = _requestMessage["default"].run(statement, parameters);

      var pullAllMessage = _requestMessage["default"].pullAll();

      this._connection.write(runMessage, observer, false);

      this._connection.write(pullAllMessage, observer, true);
    }
    /**
     * Send a RESET through the underlying connection.
     * @param {StreamObserver} observer the response observer.
     */

  }, {
    key: "reset",
    value: function reset(observer) {
      var message = _requestMessage["default"].reset();

      this._connection.write(message, observer, true);
    }
  }, {
    key: "_createPacker",
    value: function _createPacker(chunker) {
      return new v1.Packer(chunker);
    }
  }, {
    key: "_createUnpacker",
    value: function _createUnpacker(disableLosslessIntegers) {
      return new v1.Unpacker(disableLosslessIntegers);
    }
  }]);
  return BoltProtocol;
}();
/**
 * @param {TxConfig} txConfig the auto-commit transaction configuration.
 * @param {Connection} connection the connection.
 * @param {StreamObserver} observer the response observer.
 */


exports["default"] = BoltProtocol;

function assertTxConfigIsEmpty(txConfig, connection, observer) {
  if (!txConfig.isEmpty()) {
    var error = (0, _error.newError)('Driver is connected to the database that does not support transaction configuration. ' + 'Please upgrade to neo4j 3.5.0 or later in order to use this functionality'); // unsupported API was used, consider this a fatal error for the current connection

    connection._handleFatalError(error);

    observer.onError(error);
    throw error;
  }
}