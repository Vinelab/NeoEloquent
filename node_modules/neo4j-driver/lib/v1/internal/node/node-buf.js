"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _possibleConstructorReturn2 = _interopRequireDefault(require("@babel/runtime/helpers/possibleConstructorReturn"));

var _getPrototypeOf2 = _interopRequireDefault(require("@babel/runtime/helpers/getPrototypeOf"));

var _get2 = _interopRequireDefault(require("@babel/runtime/helpers/get"));

var _inherits2 = _interopRequireDefault(require("@babel/runtime/helpers/inherits"));

var _buffer = _interopRequireDefault(require("buffer"));

var _baseBuf = _interopRequireDefault(require("../buf/base-buf"));

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
var NodeBuffer =
/*#__PURE__*/
function (_BaseBuffer) {
  (0, _inherits2["default"])(NodeBuffer, _BaseBuffer);

  function NodeBuffer(arg) {
    var _this;

    (0, _classCallCheck2["default"])(this, NodeBuffer);
    var buffer = newNodeJSBuffer(arg);
    _this = (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(NodeBuffer).call(this, buffer.length));
    _this._buffer = buffer;
    return _this;
  }

  (0, _createClass2["default"])(NodeBuffer, [{
    key: "getUInt8",
    value: function getUInt8(position) {
      return this._buffer.readUInt8(position);
    }
  }, {
    key: "getInt8",
    value: function getInt8(position) {
      return this._buffer.readInt8(position);
    }
  }, {
    key: "getFloat64",
    value: function getFloat64(position) {
      return this._buffer.readDoubleBE(position);
    }
  }, {
    key: "putUInt8",
    value: function putUInt8(position, val) {
      this._buffer.writeUInt8(val, position);
    }
  }, {
    key: "putInt8",
    value: function putInt8(position, val) {
      this._buffer.writeInt8(val, position);
    }
  }, {
    key: "putFloat64",
    value: function putFloat64(position, val) {
      this._buffer.writeDoubleBE(val, position);
    }
  }, {
    key: "putBytes",
    value: function putBytes(position, val) {
      if (val instanceof NodeBuffer) {
        var bytesToCopy = Math.min(val.length - val.position, this.length - position);

        val._buffer.copy(this._buffer, position, val.position, val.position + bytesToCopy);

        val.position += bytesToCopy;
      } else {
        (0, _get2["default"])((0, _getPrototypeOf2["default"])(NodeBuffer.prototype), "putBytes", this).call(this, position, val);
      }
    }
  }, {
    key: "getSlice",
    value: function getSlice(start, length) {
      return new NodeBuffer(this._buffer.slice(start, start + length));
    }
  }]);
  return NodeBuffer;
}(_baseBuf["default"]);

exports["default"] = NodeBuffer;

function newNodeJSBuffer(arg) {
  if (arg instanceof _buffer["default"].Buffer) {
    return arg;
  } else if (typeof arg === 'number' && typeof _buffer["default"].Buffer.alloc === 'function') {
    // use static factory function present in newer NodeJS versions to allocate new buffer with specified size
    return _buffer["default"].Buffer.alloc(arg);
  } else {
    // fallback to the old, potentially deprecated constructor
    // eslint-disable-next-line node/no-deprecated-api
    return new _buffer["default"].Buffer(arg);
  }
}