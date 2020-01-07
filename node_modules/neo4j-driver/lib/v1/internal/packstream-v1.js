"use strict";

var _interopRequireWildcard = require("@babel/runtime/helpers/interopRequireWildcard");

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.Structure = exports.Unpacker = exports.Packer = void 0;

var _typeof2 = _interopRequireDefault(require("@babel/runtime/helpers/typeof"));

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _node = require("./node");

var _integer = _interopRequireWildcard(require("../integer"));

var _error = require("./../error");

var _graphTypes = require("../graph-types");

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
var TINY_STRING = 0x80;
var TINY_LIST = 0x90;
var TINY_MAP = 0xa0;
var TINY_STRUCT = 0xb0;
var NULL = 0xc0;
var FLOAT_64 = 0xc1;
var FALSE = 0xc2;
var TRUE = 0xc3;
var INT_8 = 0xc8;
var INT_16 = 0xc9;
var INT_32 = 0xca;
var INT_64 = 0xcb;
var STRING_8 = 0xd0;
var STRING_16 = 0xd1;
var STRING_32 = 0xd2;
var LIST_8 = 0xd4;
var LIST_16 = 0xd5;
var LIST_32 = 0xd6;
var BYTES_8 = 0xcc;
var BYTES_16 = 0xcd;
var BYTES_32 = 0xce;
var MAP_8 = 0xd8;
var MAP_16 = 0xd9;
var MAP_32 = 0xda;
var STRUCT_8 = 0xdc;
var STRUCT_16 = 0xdd;
var NODE = 0x4e;
var NODE_STRUCT_SIZE = 3;
var RELATIONSHIP = 0x52;
var RELATIONSHIP_STRUCT_SIZE = 5;
var UNBOUND_RELATIONSHIP = 0x72;
var UNBOUND_RELATIONSHIP_STRUCT_SIZE = 3;
var PATH = 0x50;
var PATH_STRUCT_SIZE = 3;
/**
 * A Structure have a signature and fields.
 * @access private
 */

var Structure =
/*#__PURE__*/
function () {
  /**
   * Create new instance
   */
  function Structure(signature, fields) {
    (0, _classCallCheck2["default"])(this, Structure);
    this.signature = signature;
    this.fields = fields;
  }

  (0, _createClass2["default"])(Structure, [{
    key: "toString",
    value: function toString() {
      var fieldStr = '';

      for (var i = 0; i < this.fields.length; i++) {
        if (i > 0) {
          fieldStr += ', ';
        }

        fieldStr += this.fields[i];
      }

      return 'Structure(' + this.signature + ', [' + fieldStr + '])';
    }
  }]);
  return Structure;
}();
/**
 * Class to pack
 * @access private
 */


exports.Structure = Structure;

var Packer =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {Chunker} channel the chunker backed by a network channel.
   */
  function Packer(channel) {
    (0, _classCallCheck2["default"])(this, Packer);
    this._ch = channel;
    this._byteArraysSupported = true;
  }
  /**
   * Creates a packable function out of the provided value
   * @param x the value to pack
   * @param onError callback for the case when value cannot be packed
   * @returns Function
   */


  (0, _createClass2["default"])(Packer, [{
    key: "packable",
    value: function packable(x, onError) {
      var _this = this;

      if (x === null) {
        return function () {
          return _this._ch.writeUInt8(NULL);
        };
      } else if (x === true) {
        return function () {
          return _this._ch.writeUInt8(TRUE);
        };
      } else if (x === false) {
        return function () {
          return _this._ch.writeUInt8(FALSE);
        };
      } else if (typeof x === 'number') {
        return function () {
          return _this.packFloat(x);
        };
      } else if (typeof x === 'string') {
        return function () {
          return _this.packString(x, onError);
        };
      } else if ((0, _integer.isInt)(x)) {
        return function () {
          return _this.packInteger(x);
        };
      } else if (x instanceof Int8Array) {
        return function () {
          return _this.packBytes(x, onError);
        };
      } else if (x instanceof Array) {
        return function () {
          _this.packListHeader(x.length, onError);

          for (var _i = 0; _i < x.length; _i++) {
            _this.packable(x[_i] === undefined ? null : x[_i], onError)();
          }
        };
      } else if (isIterable(x)) {
        return this.packableIterable(x, onError);
      } else if (x instanceof _graphTypes.Node) {
        return this._nonPackableValue("It is not allowed to pass nodes in query parameters, given: ".concat(x), onError);
      } else if (x instanceof _graphTypes.Relationship) {
        return this._nonPackableValue("It is not allowed to pass relationships in query parameters, given: ".concat(x), onError);
      } else if (x instanceof _graphTypes.Path) {
        return this._nonPackableValue("It is not allowed to pass paths in query parameters, given: ".concat(x), onError);
      } else if (x instanceof Structure) {
        var packableFields = [];

        for (var i = 0; i < x.fields.length; i++) {
          packableFields[i] = this.packable(x.fields[i], onError);
        }

        return function () {
          return _this.packStruct(x.signature, packableFields);
        };
      } else if ((0, _typeof2["default"])(x) === 'object') {
        return function () {
          var keys = Object.keys(x);
          var count = 0;

          for (var _i2 = 0; _i2 < keys.length; _i2++) {
            if (x[keys[_i2]] !== undefined) {
              count++;
            }
          }

          _this.packMapHeader(count, onError);

          for (var _i3 = 0; _i3 < keys.length; _i3++) {
            var key = keys[_i3];

            if (x[key] !== undefined) {
              _this.packString(key);

              _this.packable(x[key], onError)();
            }
          }
        };
      } else {
        return this._nonPackableValue("Unable to pack the given value: ".concat(x), onError);
      }
    }
  }, {
    key: "packableIterable",
    value: function packableIterable(iterable, onError) {
      try {
        var array = Array.from(iterable);
        return this.packable(array, onError);
      } catch (e) {
        // handle errors from iterable to array conversion
        onError((0, _error.newError)("Cannot pack given iterable, ".concat(e.message, ": ").concat(iterable)));
      }
    }
    /**
     * Packs a struct
     * @param signature the signature of the struct
     * @param packableFields the fields of the struct, make sure you call `packable on all fields`
     */

  }, {
    key: "packStruct",
    value: function packStruct(signature, packableFields, onError) {
      packableFields = packableFields || [];
      this.packStructHeader(packableFields.length, signature, onError);

      for (var i = 0; i < packableFields.length; i++) {
        packableFields[i]();
      }
    }
  }, {
    key: "packInteger",
    value: function packInteger(x) {
      var high = x.high;
      var low = x.low;

      if (x.greaterThanOrEqual(-0x10) && x.lessThan(0x80)) {
        this._ch.writeInt8(low);
      } else if (x.greaterThanOrEqual(-0x80) && x.lessThan(-0x10)) {
        this._ch.writeUInt8(INT_8);

        this._ch.writeInt8(low);
      } else if (x.greaterThanOrEqual(-0x8000) && x.lessThan(0x8000)) {
        this._ch.writeUInt8(INT_16);

        this._ch.writeInt16(low);
      } else if (x.greaterThanOrEqual(-0x80000000) && x.lessThan(0x80000000)) {
        this._ch.writeUInt8(INT_32);

        this._ch.writeInt32(low);
      } else {
        this._ch.writeUInt8(INT_64);

        this._ch.writeInt32(high);

        this._ch.writeInt32(low);
      }
    }
  }, {
    key: "packFloat",
    value: function packFloat(x) {
      this._ch.writeUInt8(FLOAT_64);

      this._ch.writeFloat64(x);
    }
  }, {
    key: "packString",
    value: function packString(x, onError) {
      var bytes = _node.utf8.encode(x);

      var size = bytes.length;

      if (size < 0x10) {
        this._ch.writeUInt8(TINY_STRING | size);

        this._ch.writeBytes(bytes);
      } else if (size < 0x100) {
        this._ch.writeUInt8(STRING_8);

        this._ch.writeUInt8(size);

        this._ch.writeBytes(bytes);
      } else if (size < 0x10000) {
        this._ch.writeUInt8(STRING_16);

        this._ch.writeUInt8(size / 256 >> 0);

        this._ch.writeUInt8(size % 256);

        this._ch.writeBytes(bytes);
      } else if (size < 0x100000000) {
        this._ch.writeUInt8(STRING_32);

        this._ch.writeUInt8((size / 16777216 >> 0) % 256);

        this._ch.writeUInt8((size / 65536 >> 0) % 256);

        this._ch.writeUInt8((size / 256 >> 0) % 256);

        this._ch.writeUInt8(size % 256);

        this._ch.writeBytes(bytes);
      } else {
        onError((0, _error.newError)('UTF-8 strings of size ' + size + ' are not supported'));
      }
    }
  }, {
    key: "packListHeader",
    value: function packListHeader(size, onError) {
      if (size < 0x10) {
        this._ch.writeUInt8(TINY_LIST | size);
      } else if (size < 0x100) {
        this._ch.writeUInt8(LIST_8);

        this._ch.writeUInt8(size);
      } else if (size < 0x10000) {
        this._ch.writeUInt8(LIST_16);

        this._ch.writeUInt8((size / 256 >> 0) % 256);

        this._ch.writeUInt8(size % 256);
      } else if (size < 0x100000000) {
        this._ch.writeUInt8(LIST_32);

        this._ch.writeUInt8((size / 16777216 >> 0) % 256);

        this._ch.writeUInt8((size / 65536 >> 0) % 256);

        this._ch.writeUInt8((size / 256 >> 0) % 256);

        this._ch.writeUInt8(size % 256);
      } else {
        onError((0, _error.newError)('Lists of size ' + size + ' are not supported'));
      }
    }
  }, {
    key: "packBytes",
    value: function packBytes(array, onError) {
      if (this._byteArraysSupported) {
        this.packBytesHeader(array.length, onError);

        for (var i = 0; i < array.length; i++) {
          this._ch.writeInt8(array[i]);
        }
      } else {
        onError((0, _error.newError)('Byte arrays are not supported by the database this driver is connected to'));
      }
    }
  }, {
    key: "packBytesHeader",
    value: function packBytesHeader(size, onError) {
      if (size < 0x100) {
        this._ch.writeUInt8(BYTES_8);

        this._ch.writeUInt8(size);
      } else if (size < 0x10000) {
        this._ch.writeUInt8(BYTES_16);

        this._ch.writeUInt8((size / 256 >> 0) % 256);

        this._ch.writeUInt8(size % 256);
      } else if (size < 0x100000000) {
        this._ch.writeUInt8(BYTES_32);

        this._ch.writeUInt8((size / 16777216 >> 0) % 256);

        this._ch.writeUInt8((size / 65536 >> 0) % 256);

        this._ch.writeUInt8((size / 256 >> 0) % 256);

        this._ch.writeUInt8(size % 256);
      } else {
        onError((0, _error.newError)('Byte arrays of size ' + size + ' are not supported'));
      }
    }
  }, {
    key: "packMapHeader",
    value: function packMapHeader(size, onError) {
      if (size < 0x10) {
        this._ch.writeUInt8(TINY_MAP | size);
      } else if (size < 0x100) {
        this._ch.writeUInt8(MAP_8);

        this._ch.writeUInt8(size);
      } else if (size < 0x10000) {
        this._ch.writeUInt8(MAP_16);

        this._ch.writeUInt8(size / 256 >> 0);

        this._ch.writeUInt8(size % 256);
      } else if (size < 0x100000000) {
        this._ch.writeUInt8(MAP_32);

        this._ch.writeUInt8((size / 16777216 >> 0) % 256);

        this._ch.writeUInt8((size / 65536 >> 0) % 256);

        this._ch.writeUInt8((size / 256 >> 0) % 256);

        this._ch.writeUInt8(size % 256);
      } else {
        onError((0, _error.newError)('Maps of size ' + size + ' are not supported'));
      }
    }
  }, {
    key: "packStructHeader",
    value: function packStructHeader(size, signature, onError) {
      if (size < 0x10) {
        this._ch.writeUInt8(TINY_STRUCT | size);

        this._ch.writeUInt8(signature);
      } else if (size < 0x100) {
        this._ch.writeUInt8(STRUCT_8);

        this._ch.writeUInt8(size);

        this._ch.writeUInt8(signature);
      } else if (size < 0x10000) {
        this._ch.writeUInt8(STRUCT_16);

        this._ch.writeUInt8(size / 256 >> 0);

        this._ch.writeUInt8(size % 256);
      } else {
        onError((0, _error.newError)('Structures of size ' + size + ' are not supported'));
      }
    }
  }, {
    key: "disableByteArrays",
    value: function disableByteArrays() {
      this._byteArraysSupported = false;
    }
  }, {
    key: "_nonPackableValue",
    value: function _nonPackableValue(message, onError) {
      if (onError) {
        onError((0, _error.newError)(message, _error.PROTOCOL_ERROR));
      }

      return function () {
        return undefined;
      };
    }
  }]);
  return Packer;
}();
/**
 * Class to unpack
 * @access private
 */


exports.Packer = Packer;

var Unpacker =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {boolean} disableLosslessIntegers if this unpacker should convert all received integers to native JS numbers.
   */
  function Unpacker() {
    var disableLosslessIntegers = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
    (0, _classCallCheck2["default"])(this, Unpacker);
    this._disableLosslessIntegers = disableLosslessIntegers;
  }

  (0, _createClass2["default"])(Unpacker, [{
    key: "unpack",
    value: function unpack(buffer) {
      var marker = buffer.readUInt8();
      var markerHigh = marker & 0xf0;
      var markerLow = marker & 0x0f;

      if (marker === NULL) {
        return null;
      }

      var _boolean = this._unpackBoolean(marker);

      if (_boolean !== null) {
        return _boolean;
      }

      var numberOrInteger = this._unpackNumberOrInteger(marker, buffer);

      if (numberOrInteger !== null) {
        if (this._disableLosslessIntegers && (0, _integer.isInt)(numberOrInteger)) {
          return numberOrInteger.toNumberOrInfinity();
        }

        return numberOrInteger;
      }

      var string = this._unpackString(marker, markerHigh, markerLow, buffer);

      if (string !== null) {
        return string;
      }

      var list = this._unpackList(marker, markerHigh, markerLow, buffer);

      if (list !== null) {
        return list;
      }

      var byteArray = this._unpackByteArray(marker, buffer);

      if (byteArray !== null) {
        return byteArray;
      }

      var map = this._unpackMap(marker, markerHigh, markerLow, buffer);

      if (map !== null) {
        return map;
      }

      var struct = this._unpackStruct(marker, markerHigh, markerLow, buffer);

      if (struct !== null) {
        return struct;
      }

      throw (0, _error.newError)('Unknown packed value with marker ' + marker.toString(16));
    }
  }, {
    key: "unpackInteger",
    value: function unpackInteger(buffer) {
      var marker = buffer.readUInt8();

      var result = this._unpackInteger(marker, buffer);

      if (result == null) {
        throw (0, _error.newError)('Unable to unpack integer value with marker ' + marker.toString(16));
      }

      return result;
    }
  }, {
    key: "_unpackBoolean",
    value: function _unpackBoolean(marker) {
      if (marker === TRUE) {
        return true;
      } else if (marker === FALSE) {
        return false;
      } else {
        return null;
      }
    }
  }, {
    key: "_unpackNumberOrInteger",
    value: function _unpackNumberOrInteger(marker, buffer) {
      if (marker === FLOAT_64) {
        return buffer.readFloat64();
      } else {
        return this._unpackInteger(marker, buffer);
      }
    }
  }, {
    key: "_unpackInteger",
    value: function _unpackInteger(marker, buffer) {
      if (marker >= 0 && marker < 128) {
        return (0, _integer["int"])(marker);
      } else if (marker >= 240 && marker < 256) {
        return (0, _integer["int"])(marker - 256);
      } else if (marker === INT_8) {
        return (0, _integer["int"])(buffer.readInt8());
      } else if (marker === INT_16) {
        return (0, _integer["int"])(buffer.readInt16());
      } else if (marker === INT_32) {
        var b = buffer.readInt32();
        return (0, _integer["int"])(b);
      } else if (marker === INT_64) {
        var high = buffer.readInt32();
        var low = buffer.readInt32();
        return new _integer["default"](low, high);
      } else {
        return null;
      }
    }
  }, {
    key: "_unpackString",
    value: function _unpackString(marker, markerHigh, markerLow, buffer) {
      if (markerHigh === TINY_STRING) {
        return _node.utf8.decode(buffer, markerLow);
      } else if (marker === STRING_8) {
        return _node.utf8.decode(buffer, buffer.readUInt8());
      } else if (marker === STRING_16) {
        return _node.utf8.decode(buffer, buffer.readUInt16());
      } else if (marker === STRING_32) {
        return _node.utf8.decode(buffer, buffer.readUInt32());
      } else {
        return null;
      }
    }
  }, {
    key: "_unpackList",
    value: function _unpackList(marker, markerHigh, markerLow, buffer) {
      if (markerHigh === TINY_LIST) {
        return this._unpackListWithSize(markerLow, buffer);
      } else if (marker === LIST_8) {
        return this._unpackListWithSize(buffer.readUInt8(), buffer);
      } else if (marker === LIST_16) {
        return this._unpackListWithSize(buffer.readUInt16(), buffer);
      } else if (marker === LIST_32) {
        return this._unpackListWithSize(buffer.readUInt32(), buffer);
      } else {
        return null;
      }
    }
  }, {
    key: "_unpackListWithSize",
    value: function _unpackListWithSize(size, buffer) {
      var value = [];

      for (var i = 0; i < size; i++) {
        value.push(this.unpack(buffer));
      }

      return value;
    }
  }, {
    key: "_unpackByteArray",
    value: function _unpackByteArray(marker, buffer) {
      if (marker === BYTES_8) {
        return this._unpackByteArrayWithSize(buffer.readUInt8(), buffer);
      } else if (marker === BYTES_16) {
        return this._unpackByteArrayWithSize(buffer.readUInt16(), buffer);
      } else if (marker === BYTES_32) {
        return this._unpackByteArrayWithSize(buffer.readUInt32(), buffer);
      } else {
        return null;
      }
    }
  }, {
    key: "_unpackByteArrayWithSize",
    value: function _unpackByteArrayWithSize(size, buffer) {
      var value = new Int8Array(size);

      for (var i = 0; i < size; i++) {
        value[i] = buffer.readInt8();
      }

      return value;
    }
  }, {
    key: "_unpackMap",
    value: function _unpackMap(marker, markerHigh, markerLow, buffer) {
      if (markerHigh === TINY_MAP) {
        return this._unpackMapWithSize(markerLow, buffer);
      } else if (marker === MAP_8) {
        return this._unpackMapWithSize(buffer.readUInt8(), buffer);
      } else if (marker === MAP_16) {
        return this._unpackMapWithSize(buffer.readUInt16(), buffer);
      } else if (marker === MAP_32) {
        return this._unpackMapWithSize(buffer.readUInt32(), buffer);
      } else {
        return null;
      }
    }
  }, {
    key: "_unpackMapWithSize",
    value: function _unpackMapWithSize(size, buffer) {
      var value = {};

      for (var i = 0; i < size; i++) {
        var key = this.unpack(buffer);
        value[key] = this.unpack(buffer);
      }

      return value;
    }
  }, {
    key: "_unpackStruct",
    value: function _unpackStruct(marker, markerHigh, markerLow, buffer) {
      if (markerHigh === TINY_STRUCT) {
        return this._unpackStructWithSize(markerLow, buffer);
      } else if (marker === STRUCT_8) {
        return this._unpackStructWithSize(buffer.readUInt8(), buffer);
      } else if (marker === STRUCT_16) {
        return this._unpackStructWithSize(buffer.readUInt16(), buffer);
      } else {
        return null;
      }
    }
  }, {
    key: "_unpackStructWithSize",
    value: function _unpackStructWithSize(structSize, buffer) {
      var signature = buffer.readUInt8();

      if (signature === NODE) {
        return this._unpackNode(structSize, buffer);
      } else if (signature === RELATIONSHIP) {
        return this._unpackRelationship(structSize, buffer);
      } else if (signature === UNBOUND_RELATIONSHIP) {
        return this._unpackUnboundRelationship(structSize, buffer);
      } else if (signature === PATH) {
        return this._unpackPath(structSize, buffer);
      } else {
        return this._unpackUnknownStruct(signature, structSize, buffer);
      }
    }
  }, {
    key: "_unpackNode",
    value: function _unpackNode(structSize, buffer) {
      this._verifyStructSize('Node', NODE_STRUCT_SIZE, structSize);

      return new _graphTypes.Node(this.unpack(buffer), // Identity
      this.unpack(buffer), // Labels
      this.unpack(buffer) // Properties
      );
    }
  }, {
    key: "_unpackRelationship",
    value: function _unpackRelationship(structSize, buffer) {
      this._verifyStructSize('Relationship', RELATIONSHIP_STRUCT_SIZE, structSize);

      return new _graphTypes.Relationship(this.unpack(buffer), // Identity
      this.unpack(buffer), // Start Node Identity
      this.unpack(buffer), // End Node Identity
      this.unpack(buffer), // Type
      this.unpack(buffer) // Properties
      );
    }
  }, {
    key: "_unpackUnboundRelationship",
    value: function _unpackUnboundRelationship(structSize, buffer) {
      this._verifyStructSize('UnboundRelationship', UNBOUND_RELATIONSHIP_STRUCT_SIZE, structSize);

      return new _graphTypes.UnboundRelationship(this.unpack(buffer), // Identity
      this.unpack(buffer), // Type
      this.unpack(buffer) // Properties
      );
    }
  }, {
    key: "_unpackPath",
    value: function _unpackPath(structSize, buffer) {
      this._verifyStructSize('Path', PATH_STRUCT_SIZE, structSize);

      var nodes = this.unpack(buffer);
      var rels = this.unpack(buffer);
      var sequence = this.unpack(buffer);
      var segments = [];
      var prevNode = nodes[0];

      for (var i = 0; i < sequence.length; i += 2) {
        var nextNode = nodes[sequence[i + 1]];
        var relIndex = sequence[i];
        var rel = void 0;

        if (relIndex > 0) {
          rel = rels[relIndex - 1];

          if (rel instanceof _graphTypes.UnboundRelationship) {
            // To avoid duplication, relationships in a path do not contain
            // information about their start and end nodes, that's instead
            // inferred from the path sequence. This is us inferring (and,
            // for performance reasons remembering) the start/end of a rel.
            rels[relIndex - 1] = rel = rel.bind(prevNode.identity, nextNode.identity);
          }
        } else {
          rel = rels[-relIndex - 1];

          if (rel instanceof _graphTypes.UnboundRelationship) {
            // See above
            rels[-relIndex - 1] = rel = rel.bind(nextNode.identity, prevNode.identity);
          }
        } // Done hydrating one path segment.


        segments.push(new _graphTypes.PathSegment(prevNode, rel, nextNode));
        prevNode = nextNode;
      }

      return new _graphTypes.Path(nodes[0], nodes[nodes.length - 1], segments);
    }
  }, {
    key: "_unpackUnknownStruct",
    value: function _unpackUnknownStruct(signature, structSize, buffer) {
      var result = new Structure(signature, []);

      for (var i = 0; i < structSize; i++) {
        result.fields.push(this.unpack(buffer));
      }

      return result;
    }
  }, {
    key: "_verifyStructSize",
    value: function _verifyStructSize(structName, expectedSize, actualSize) {
      if (expectedSize !== actualSize) {
        throw (0, _error.newError)("Wrong struct size for ".concat(structName, ", expected ").concat(expectedSize, " but was ").concat(actualSize), _error.PROTOCOL_ERROR);
      }
    }
  }]);
  return Unpacker;
}();

exports.Unpacker = Unpacker;

function isIterable(obj) {
  if (obj == null) {
    return false;
  }

  return typeof obj[Symbol.iterator] === 'function';
}