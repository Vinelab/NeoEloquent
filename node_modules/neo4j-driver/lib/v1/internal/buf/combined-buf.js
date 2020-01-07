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

var _baseBuf = _interopRequireDefault(require("./base-buf"));

var _node = require("../node");

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
 * Buffer that combines multiple buffers, exposing them as one single buffer.
 */
var CombinedBuffer =
/*#__PURE__*/
function (_BaseBuffer) {
  (0, _inherits2["default"])(CombinedBuffer, _BaseBuffer);

  function CombinedBuffer(buffers) {
    var _this;

    (0, _classCallCheck2["default"])(this, CombinedBuffer);
    var length = 0;

    for (var i = 0; i < buffers.length; i++) {
      length += buffers[i].length;
    }

    _this = (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(CombinedBuffer).call(this, length));
    _this._buffers = buffers;
    return _this;
  }

  (0, _createClass2["default"])(CombinedBuffer, [{
    key: "getUInt8",
    value: function getUInt8(position) {
      // Surely there's a faster way to do this.. some sort of lookup table thing?
      for (var i = 0; i < this._buffers.length; i++) {
        var buffer = this._buffers[i]; // If the position is not in the current buffer, skip the current buffer

        if (position >= buffer.length) {
          position -= buffer.length;
        } else {
          return buffer.getUInt8(position);
        }
      }
    }
  }, {
    key: "getInt8",
    value: function getInt8(position) {
      // Surely there's a faster way to do this.. some sort of lookup table thing?
      for (var i = 0; i < this._buffers.length; i++) {
        var buffer = this._buffers[i]; // If the position is not in the current buffer, skip the current buffer

        if (position >= buffer.length) {
          position -= buffer.length;
        } else {
          return buffer.getInt8(position);
        }
      }
    }
  }, {
    key: "getFloat64",
    value: function getFloat64(position) {
      // At some point, a more efficient impl. For now, we copy the 8 bytes
      // we want to read and depend on the platform impl of IEEE 754.
      var b = (0, _node.alloc)(8);

      for (var i = 0; i < 8; i++) {
        b.putUInt8(i, this.getUInt8(position + i));
      }

      return b.getFloat64(0);
    }
  }]);
  return CombinedBuffer;
}(_baseBuf["default"]);

exports["default"] = CombinedBuffer;