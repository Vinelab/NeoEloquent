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

var _inherits2 = _interopRequireDefault(require("@babel/runtime/helpers/inherits"));

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
var HeapBuffer =
/*#__PURE__*/
function (_BaseBuffer) {
  (0, _inherits2["default"])(HeapBuffer, _BaseBuffer);

  function HeapBuffer(arg) {
    var _this;

    (0, _classCallCheck2["default"])(this, HeapBuffer);
    var buffer = arg instanceof ArrayBuffer ? arg : new ArrayBuffer(arg);
    _this = (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(HeapBuffer).call(this, buffer.byteLength));
    _this._buffer = buffer;
    _this._view = new DataView(_this._buffer);
    return _this;
  }

  (0, _createClass2["default"])(HeapBuffer, [{
    key: "putUInt8",
    value: function putUInt8(position, val) {
      this._view.setUint8(position, val);
    }
  }, {
    key: "getUInt8",
    value: function getUInt8(position) {
      return this._view.getUint8(position);
    }
  }, {
    key: "putInt8",
    value: function putInt8(position, val) {
      this._view.setInt8(position, val);
    }
  }, {
    key: "getInt8",
    value: function getInt8(position) {
      return this._view.getInt8(position);
    }
  }, {
    key: "getFloat64",
    value: function getFloat64(position) {
      return this._view.getFloat64(position);
    }
  }, {
    key: "putFloat64",
    value: function putFloat64(position, val) {
      this._view.setFloat64(position, val);
    }
  }, {
    key: "getSlice",
    value: function getSlice(start, length) {
      if (this._buffer.slice) {
        return new HeapBuffer(this._buffer.slice(start, start + length));
      } else {
        // Some platforms (eg. phantomjs) don't support slice, so fall back to a copy
        // We do this rather than return a SliceBuffer, because sliceBuffer cannot
        // be passed to native network write ops etc - we need ArrayBuffer for that
        var copy = new HeapBuffer(length);

        for (var i = 0; i < length; i++) {
          copy.putUInt8(i, this.getUInt8(i + start));
        }

        return copy;
      }
    }
    /**
     * Specific to HeapBuffer, this gets a DataView from the
     * current position and of the specified length.
     */

  }, {
    key: "readView",
    value: function readView(length) {
      return new DataView(this._buffer, this._updatePos(length), length);
    }
  }]);
  return HeapBuffer;
}(_baseBuf["default"]);

exports["default"] = HeapBuffer;