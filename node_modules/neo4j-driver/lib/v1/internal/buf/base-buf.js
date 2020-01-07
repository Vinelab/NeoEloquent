"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _possibleConstructorReturn2 = _interopRequireDefault(require("@babel/runtime/helpers/possibleConstructorReturn"));

var _getPrototypeOf2 = _interopRequireDefault(require("@babel/runtime/helpers/getPrototypeOf"));

var _inherits2 = _interopRequireDefault(require("@babel/runtime/helpers/inherits"));

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

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
 * Common base with default implementation for most buffer methods.
 * Buffers are stateful - they track a current "position", this helps greatly
 * when reading and writing from them incrementally. You can also ignore the
 * stateful read/write methods.
 * readXXX and writeXXX-methods move the inner position of the buffer.
 * putXXX and getXXX-methods do not.
 * @access private
 */
var BaseBuffer =
/*#__PURE__*/
function () {
  /**
   * Create a instance with the injected size.
   * @constructor
   * @param {Integer} size
   */
  function BaseBuffer(size) {
    (0, _classCallCheck2["default"])(this, BaseBuffer);
    this.position = 0;
    this.length = size;
  }

  (0, _createClass2["default"])(BaseBuffer, [{
    key: "getUInt8",
    value: function getUInt8(position) {
      throw new Error('Not implemented');
    }
  }, {
    key: "getInt8",
    value: function getInt8(position) {
      throw new Error('Not implemented');
    }
  }, {
    key: "getFloat64",
    value: function getFloat64(position) {
      throw new Error('Not implemented');
    }
  }, {
    key: "putUInt8",
    value: function putUInt8(position, val) {
      throw new Error('Not implemented');
    }
  }, {
    key: "putInt8",
    value: function putInt8(position, val) {
      throw new Error('Not implemented');
    }
  }, {
    key: "putFloat64",
    value: function putFloat64(position, val) {
      throw new Error('Not implemented');
    }
    /**
     * @param p
     */

  }, {
    key: "getInt16",
    value: function getInt16(p) {
      return this.getInt8(p) << 8 | this.getUInt8(p + 1);
    }
    /**
     * @param p
     */

  }, {
    key: "getUInt16",
    value: function getUInt16(p) {
      return this.getUInt8(p) << 8 | this.getUInt8(p + 1);
    }
    /**
     * @param p
     */

  }, {
    key: "getInt32",
    value: function getInt32(p) {
      return this.getInt8(p) << 24 | this.getUInt8(p + 1) << 16 | this.getUInt8(p + 2) << 8 | this.getUInt8(p + 3);
    }
    /**
     * @param p
     */

  }, {
    key: "getUInt32",
    value: function getUInt32(p) {
      return this.getUInt8(p) << 24 | this.getUInt8(p + 1) << 16 | this.getUInt8(p + 2) << 8 | this.getUInt8(p + 3);
    }
    /**
     * @param p
     */

  }, {
    key: "getInt64",
    value: function getInt64(p) {
      return this.getInt8(p) << 56 | this.getUInt8(p + 1) << 48 | this.getUInt8(p + 2) << 40 | this.getUInt8(p + 3) << 32 | this.getUInt8(p + 4) << 24 | this.getUInt8(p + 5) << 16 | this.getUInt8(p + 6) << 8 | this.getUInt8(p + 7);
    }
    /**
     * Get a slice of this buffer. This method does not copy any data,
     * but simply provides a slice view of this buffer
     * @param start
     * @param length
     */

  }, {
    key: "getSlice",
    value: function getSlice(start, length) {
      return new SliceBuffer(start, length, this);
    }
    /**
     * @param p
     * @param val
     */

  }, {
    key: "putInt16",
    value: function putInt16(p, val) {
      this.putInt8(p, val >> 8);
      this.putUInt8(p + 1, val & 0xff);
    }
    /**
     * @param p
     * @param val
     */

  }, {
    key: "putUInt16",
    value: function putUInt16(p, val) {
      this.putUInt8(p, val >> 8 & 0xff);
      this.putUInt8(p + 1, val & 0xff);
    }
    /**
     * @param p
     * @param val
     */

  }, {
    key: "putInt32",
    value: function putInt32(p, val) {
      this.putInt8(p, val >> 24);
      this.putUInt8(p + 1, val >> 16 & 0xff);
      this.putUInt8(p + 2, val >> 8 & 0xff);
      this.putUInt8(p + 3, val & 0xff);
    }
    /**
     * @param p
     * @param val
     */

  }, {
    key: "putUInt32",
    value: function putUInt32(p, val) {
      this.putUInt8(p, val >> 24 & 0xff);
      this.putUInt8(p + 1, val >> 16 & 0xff);
      this.putUInt8(p + 2, val >> 8 & 0xff);
      this.putUInt8(p + 3, val & 0xff);
    }
    /**
     * @param p
     * @param val
     */

  }, {
    key: "putInt64",
    value: function putInt64(p, val) {
      this.putInt8(p, val >> 48);
      this.putUInt8(p + 1, val >> 42 & 0xff);
      this.putUInt8(p + 2, val >> 36 & 0xff);
      this.putUInt8(p + 3, val >> 30 & 0xff);
      this.putUInt8(p + 4, val >> 24 & 0xff);
      this.putUInt8(p + 5, val >> 16 & 0xff);
      this.putUInt8(p + 6, val >> 8 & 0xff);
      this.putUInt8(p + 7, val & 0xff);
    }
    /**
     * @param position
     * @param other
     */

  }, {
    key: "putBytes",
    value: function putBytes(position, other) {
      for (var i = 0, end = other.remaining(); i < end; i++) {
        this.putUInt8(position + i, other.readUInt8());
      }
    }
    /**
     * Read from state position.
     */

  }, {
    key: "readUInt8",
    value: function readUInt8() {
      return this.getUInt8(this._updatePos(1));
    }
    /**
     * Read from state position.
     */

  }, {
    key: "readInt8",
    value: function readInt8() {
      return this.getInt8(this._updatePos(1));
    }
    /**
     * Read from state position.
     */

  }, {
    key: "readUInt16",
    value: function readUInt16() {
      return this.getUInt16(this._updatePos(2));
    }
    /**
     * Read from state position.
     */

  }, {
    key: "readUInt32",
    value: function readUInt32() {
      return this.getUInt32(this._updatePos(4));
    }
    /**
     * Read from state position.
     */

  }, {
    key: "readInt16",
    value: function readInt16() {
      return this.getInt16(this._updatePos(2));
    }
    /**
     * Read from state position.
     */

  }, {
    key: "readInt32",
    value: function readInt32() {
      return this.getInt32(this._updatePos(4));
    }
    /**
     * Read from state position.
     */

  }, {
    key: "readInt64",
    value: function readInt64() {
      return this.getInt32(this._updatePos(8));
    }
    /**
     * Read from state position.
     */

  }, {
    key: "readFloat64",
    value: function readFloat64() {
      return this.getFloat64(this._updatePos(8));
    }
    /**
     * Write to state position.
     * @param val
     */

  }, {
    key: "writeUInt8",
    value: function writeUInt8(val) {
      this.putUInt8(this._updatePos(1), val);
    }
    /**
     * Write to state position.
     * @param val
     */

  }, {
    key: "writeInt8",
    value: function writeInt8(val) {
      this.putInt8(this._updatePos(1), val);
    }
    /**
     * Write to state position.
     * @param val
     */

  }, {
    key: "writeInt16",
    value: function writeInt16(val) {
      this.putInt16(this._updatePos(2), val);
    }
    /**
     * Write to state position.
     * @param val
     */

  }, {
    key: "writeInt32",
    value: function writeInt32(val) {
      this.putInt32(this._updatePos(4), val);
    }
    /**
     * Write to state position.
     * @param val
     */

  }, {
    key: "writeUInt32",
    value: function writeUInt32(val) {
      this.putUInt32(this._updatePos(4), val);
    }
    /**
     * Write to state position.
     * @param val
     */

  }, {
    key: "writeInt64",
    value: function writeInt64(val) {
      this.putInt64(this._updatePos(8), val);
    }
    /**
     * Write to state position.
     * @param val
     */

  }, {
    key: "writeFloat64",
    value: function writeFloat64(val) {
      this.putFloat64(this._updatePos(8), val);
    }
    /**
     * Write to state position.
     * @param val
     */

  }, {
    key: "writeBytes",
    value: function writeBytes(val) {
      this.putBytes(this._updatePos(val.remaining()), val);
    }
    /**
     * Get a slice of this buffer. This method does not copy any data,
     * but simply provides a slice view of this buffer
     * @param length
     */

  }, {
    key: "readSlice",
    value: function readSlice(length) {
      return this.getSlice(this._updatePos(length), length);
    }
  }, {
    key: "_updatePos",
    value: function _updatePos(length) {
      var p = this.position;
      this.position += length;
      return p;
    }
    /**
     * Get remaining
     */

  }, {
    key: "remaining",
    value: function remaining() {
      return this.length - this.position;
    }
    /**
     * Has remaining
     */

  }, {
    key: "hasRemaining",
    value: function hasRemaining() {
      return this.remaining() > 0;
    }
    /**
     * Reset position state
     */

  }, {
    key: "reset",
    value: function reset() {
      this.position = 0;
    }
    /**
     * Get string representation of buffer and it's state.
     * @return {string} Buffer as a string
     */

  }, {
    key: "toString",
    value: function toString() {
      return this.constructor.name + '( position=' + this.position + ' )\n  ' + this.toHex();
    }
    /**
     * Get string representation of buffer.
     * @return {string} Buffer as a string
     */

  }, {
    key: "toHex",
    value: function toHex() {
      var out = '';

      for (var i = 0; i < this.length; i++) {
        var hexByte = this.getUInt8(i).toString(16);

        if (hexByte.length === 1) {
          hexByte = '0' + hexByte;
        }

        out += hexByte;

        if (i !== this.length - 1) {
          out += ' ';
        }
      }

      return out;
    }
  }]);
  return BaseBuffer;
}();
/**
 * Represents a view as slice of another buffer.
 * @access private
 */


exports["default"] = BaseBuffer;

var SliceBuffer =
/*#__PURE__*/
function (_BaseBuffer) {
  (0, _inherits2["default"])(SliceBuffer, _BaseBuffer);

  function SliceBuffer(start, length, inner) {
    var _this;

    (0, _classCallCheck2["default"])(this, SliceBuffer);
    _this = (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(SliceBuffer).call(this, length));
    _this._start = start;
    _this._inner = inner;
    return _this;
  }

  (0, _createClass2["default"])(SliceBuffer, [{
    key: "putUInt8",
    value: function putUInt8(position, val) {
      this._inner.putUInt8(this._start + position, val);
    }
  }, {
    key: "getUInt8",
    value: function getUInt8(position) {
      return this._inner.getUInt8(this._start + position);
    }
  }, {
    key: "putInt8",
    value: function putInt8(position, val) {
      this._inner.putInt8(this._start + position, val);
    }
  }, {
    key: "putFloat64",
    value: function putFloat64(position, val) {
      this._inner.putFloat64(this._start + position, val);
    }
  }, {
    key: "getInt8",
    value: function getInt8(position) {
      return this._inner.getInt8(this._start + position);
    }
  }, {
    key: "getFloat64",
    value: function getFloat64(position) {
      return this._inner.getFloat64(this._start + position);
    }
  }]);
  return SliceBuffer;
}(BaseBuffer);