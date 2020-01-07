"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _node = require("./node");

var _error = require("../error");

var _boltProtocolV = _interopRequireDefault(require("./bolt-protocol-v1"));

var _boltProtocolV2 = _interopRequireDefault(require("./bolt-protocol-v2"));

var _boltProtocolV3 = _interopRequireDefault(require("./bolt-protocol-v3"));

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
var HTTP_MAGIC_PREAMBLE = 1213486160; // == 0x48545450 == "HTTP"

var BOLT_MAGIC_PREAMBLE = 0x6060b017;

var ProtocolHandshaker =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {Connection} connection the connection owning this protocol.
   * @param {Channel} channel the network channel.
   * @param {Chunker} chunker the message chunker.
   * @param {boolean} disableLosslessIntegers flag to use native JS numbers.
   * @param {Logger} log the logger.
   */
  function ProtocolHandshaker(connection, channel, chunker, disableLosslessIntegers, log) {
    (0, _classCallCheck2["default"])(this, ProtocolHandshaker);
    this._connection = connection;
    this._channel = channel;
    this._chunker = chunker;
    this._disableLosslessIntegers = disableLosslessIntegers;
    this._log = log;
  }
  /**
   * Write a Bolt handshake into the underlying network channel.
   */


  (0, _createClass2["default"])(ProtocolHandshaker, [{
    key: "writeHandshakeRequest",
    value: function writeHandshakeRequest() {
      this._channel.write(newHandshakeBuffer());
    }
    /**
     * Read the given handshake response and create the negotiated bolt protocol.
     * @param {BaseBuffer} buffer byte buffer containing the handshake response.
     * @return {BoltProtocol} bolt protocol corresponding to the version suggested by the database.
     * @throws {Neo4jError} when bolt protocol can't be instantiated.
     */

  }, {
    key: "createNegotiatedProtocol",
    value: function createNegotiatedProtocol(buffer) {
      var negotiatedVersion = buffer.readInt32();

      if (this._log.isDebugEnabled()) {
        this._log.debug("".concat(this._connection, " negotiated protocol version ").concat(negotiatedVersion));
      }

      return this._createProtocolWithVersion(negotiatedVersion);
    }
    /**
     * @return {BoltProtocol}
     * @private
     */

  }, {
    key: "_createProtocolWithVersion",
    value: function _createProtocolWithVersion(version) {
      switch (version) {
        case 1:
          return new _boltProtocolV["default"](this._connection, this._chunker, this._disableLosslessIntegers);

        case 2:
          return new _boltProtocolV2["default"](this._connection, this._chunker, this._disableLosslessIntegers);

        case 3:
          return new _boltProtocolV3["default"](this._connection, this._chunker, this._disableLosslessIntegers);

        case HTTP_MAGIC_PREAMBLE:
          throw (0, _error.newError)('Server responded HTTP. Make sure you are not trying to connect to the http endpoint ' + '(HTTP defaults to port 7474 whereas BOLT defaults to port 7687)');

        default:
          throw (0, _error.newError)('Unknown Bolt protocol version: ' + version);
      }
    }
  }]);
  return ProtocolHandshaker;
}();
/**
 * @return {BaseBuffer}
 * @private
 */


exports["default"] = ProtocolHandshaker;

function newHandshakeBuffer() {
  var handshakeBuffer = (0, _node.alloc)(5 * 4); // magic preamble

  handshakeBuffer.writeInt32(BOLT_MAGIC_PREAMBLE); // proposed versions

  handshakeBuffer.writeInt32(3);
  handshakeBuffer.writeInt32(2);
  handshakeBuffer.writeInt32(1);
  handshakeBuffer.writeInt32(0); // reset the reader position

  handshakeBuffer.reset();
  return handshakeBuffer;
}