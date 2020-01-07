"use strict";

var _interopRequireWildcard = require("@babel/runtime/helpers/interopRequireWildcard");

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.Unpacker = exports.Packer = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _possibleConstructorReturn2 = _interopRequireDefault(require("@babel/runtime/helpers/possibleConstructorReturn"));

var _getPrototypeOf2 = _interopRequireDefault(require("@babel/runtime/helpers/getPrototypeOf"));

var _get2 = _interopRequireDefault(require("@babel/runtime/helpers/get"));

var _inherits2 = _interopRequireDefault(require("@babel/runtime/helpers/inherits"));

var v1 = _interopRequireWildcard(require("./packstream-v1"));

var _spatialTypes = require("../spatial-types");

var _temporalTypes = require("../temporal-types");

var _integer = require("../integer");

var _temporalUtil = require("../internal/temporal-util");

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
var POINT_2D = 0x58;
var POINT_2D_STRUCT_SIZE = 3;
var POINT_3D = 0x59;
var POINT_3D_STRUCT_SIZE = 4;
var DURATION = 0x45;
var DURATION_STRUCT_SIZE = 4;
var LOCAL_TIME = 0x74;
var LOCAL_TIME_STRUCT_SIZE = 1;
var TIME = 0x54;
var TIME_STRUCT_SIZE = 2;
var DATE = 0x44;
var DATE_STRUCT_SIZE = 1;
var LOCAL_DATE_TIME = 0x64;
var LOCAL_DATE_TIME_STRUCT_SIZE = 2;
var DATE_TIME_WITH_ZONE_OFFSET = 0x46;
var DATE_TIME_WITH_ZONE_OFFSET_STRUCT_SIZE = 3;
var DATE_TIME_WITH_ZONE_ID = 0x66;
var DATE_TIME_WITH_ZONE_ID_STRUCT_SIZE = 3;

var Packer =
/*#__PURE__*/
function (_v1$Packer) {
  (0, _inherits2["default"])(Packer, _v1$Packer);

  function Packer() {
    (0, _classCallCheck2["default"])(this, Packer);
    return (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(Packer).apply(this, arguments));
  }

  (0, _createClass2["default"])(Packer, [{
    key: "disableByteArrays",
    value: function disableByteArrays() {
      throw new Error('Bolt V2 should always support byte arrays');
    }
  }, {
    key: "packable",
    value: function packable(obj, onError) {
      var _this = this;

      if ((0, _spatialTypes.isPoint)(obj)) {
        return function () {
          return packPoint(obj, _this, onError);
        };
      } else if ((0, _temporalTypes.isDuration)(obj)) {
        return function () {
          return packDuration(obj, _this, onError);
        };
      } else if ((0, _temporalTypes.isLocalTime)(obj)) {
        return function () {
          return packLocalTime(obj, _this, onError);
        };
      } else if ((0, _temporalTypes.isTime)(obj)) {
        return function () {
          return packTime(obj, _this, onError);
        };
      } else if ((0, _temporalTypes.isDate)(obj)) {
        return function () {
          return packDate(obj, _this, onError);
        };
      } else if ((0, _temporalTypes.isLocalDateTime)(obj)) {
        return function () {
          return packLocalDateTime(obj, _this, onError);
        };
      } else if ((0, _temporalTypes.isDateTime)(obj)) {
        return function () {
          return packDateTime(obj, _this, onError);
        };
      } else {
        return (0, _get2["default"])((0, _getPrototypeOf2["default"])(Packer.prototype), "packable", this).call(this, obj, onError);
      }
    }
  }]);
  return Packer;
}(v1.Packer);

exports.Packer = Packer;

var Unpacker =
/*#__PURE__*/
function (_v1$Unpacker) {
  (0, _inherits2["default"])(Unpacker, _v1$Unpacker);

  /**
   * @constructor
   * @param {boolean} disableLosslessIntegers if this unpacker should convert all received integers to native JS numbers.
   */
  function Unpacker() {
    var disableLosslessIntegers = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
    (0, _classCallCheck2["default"])(this, Unpacker);
    return (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(Unpacker).call(this, disableLosslessIntegers));
  }

  (0, _createClass2["default"])(Unpacker, [{
    key: "_unpackUnknownStruct",
    value: function _unpackUnknownStruct(signature, structSize, buffer) {
      if (signature === POINT_2D) {
        return unpackPoint2D(this, structSize, buffer);
      } else if (signature === POINT_3D) {
        return unpackPoint3D(this, structSize, buffer);
      } else if (signature === DURATION) {
        return unpackDuration(this, structSize, buffer);
      } else if (signature === LOCAL_TIME) {
        return unpackLocalTime(this, structSize, buffer, this._disableLosslessIntegers);
      } else if (signature === TIME) {
        return unpackTime(this, structSize, buffer, this._disableLosslessIntegers);
      } else if (signature === DATE) {
        return unpackDate(this, structSize, buffer, this._disableLosslessIntegers);
      } else if (signature === LOCAL_DATE_TIME) {
        return unpackLocalDateTime(this, structSize, buffer, this._disableLosslessIntegers);
      } else if (signature === DATE_TIME_WITH_ZONE_OFFSET) {
        return unpackDateTimeWithZoneOffset(this, structSize, buffer, this._disableLosslessIntegers);
      } else if (signature === DATE_TIME_WITH_ZONE_ID) {
        return unpackDateTimeWithZoneId(this, structSize, buffer, this._disableLosslessIntegers);
      } else {
        return (0, _get2["default"])((0, _getPrototypeOf2["default"])(Unpacker.prototype), "_unpackUnknownStruct", this).call(this, signature, structSize, buffer, this._disableLosslessIntegers);
      }
    }
  }]);
  return Unpacker;
}(v1.Unpacker);
/**
 * Pack given 2D or 3D point.
 * @param {Point} point the point value to pack.
 * @param {Packer} packer the packer to use.
 * @param {function} onError the error callback.
 */


exports.Unpacker = Unpacker;

function packPoint(point, packer, onError) {
  var is2DPoint = point.z === null || point.z === undefined;

  if (is2DPoint) {
    packPoint2D(point, packer, onError);
  } else {
    packPoint3D(point, packer, onError);
  }
}
/**
 * Pack given 2D point.
 * @param {Point} point the point value to pack.
 * @param {Packer} packer the packer to use.
 * @param {function} onError the error callback.
 */


function packPoint2D(point, packer, onError) {
  var packableStructFields = [packer.packable((0, _integer["int"])(point.srid), onError), packer.packable(point.x, onError), packer.packable(point.y, onError)];
  packer.packStruct(POINT_2D, packableStructFields, onError);
}
/**
 * Pack given 3D point.
 * @param {Point} point the point value to pack.
 * @param {Packer} packer the packer to use.
 * @param {function} onError the error callback.
 */


function packPoint3D(point, packer, onError) {
  var packableStructFields = [packer.packable((0, _integer["int"])(point.srid), onError), packer.packable(point.x, onError), packer.packable(point.y, onError), packer.packable(point.z, onError)];
  packer.packStruct(POINT_3D, packableStructFields, onError);
}
/**
 * Unpack 2D point value using the given unpacker.
 * @param {Unpacker} unpacker the unpacker to use.
 * @param {number} structSize the retrieved struct size.
 * @param {BaseBuffer} buffer the buffer to unpack from.
 * @return {Point} the unpacked 2D point value.
 */


function unpackPoint2D(unpacker, structSize, buffer) {
  unpacker._verifyStructSize('Point2D', POINT_2D_STRUCT_SIZE, structSize);

  return new _spatialTypes.Point(unpacker.unpack(buffer), // srid
  unpacker.unpack(buffer), // x
  unpacker.unpack(buffer), // y
  undefined // z
  );
}
/**
 * Unpack 3D point value using the given unpacker.
 * @param {Unpacker} unpacker the unpacker to use.
 * @param {number} structSize the retrieved struct size.
 * @param {BaseBuffer} buffer the buffer to unpack from.
 * @return {Point} the unpacked 3D point value.
 */


function unpackPoint3D(unpacker, structSize, buffer) {
  unpacker._verifyStructSize('Point3D', POINT_3D_STRUCT_SIZE, structSize);

  return new _spatialTypes.Point(unpacker.unpack(buffer), // srid
  unpacker.unpack(buffer), // x
  unpacker.unpack(buffer), // y
  unpacker.unpack(buffer) // z
  );
}
/**
 * Pack given duration.
 * @param {Duration} value the duration value to pack.
 * @param {Packer} packer the packer to use.
 * @param {function} onError the error callback.
 */


function packDuration(value, packer, onError) {
  var months = (0, _integer["int"])(value.months);
  var days = (0, _integer["int"])(value.days);
  var seconds = (0, _integer["int"])(value.seconds);
  var nanoseconds = (0, _integer["int"])(value.nanoseconds);
  var packableStructFields = [packer.packable(months, onError), packer.packable(days, onError), packer.packable(seconds, onError), packer.packable(nanoseconds, onError)];
  packer.packStruct(DURATION, packableStructFields, onError);
}
/**
 * Unpack duration value using the given unpacker.
 * @param {Unpacker} unpacker the unpacker to use.
 * @param {number} structSize the retrieved struct size.
 * @param {BaseBuffer} buffer the buffer to unpack from.
 * @return {Duration} the unpacked duration value.
 */


function unpackDuration(unpacker, structSize, buffer) {
  unpacker._verifyStructSize('Duration', DURATION_STRUCT_SIZE, structSize);

  var months = unpacker.unpack(buffer);
  var days = unpacker.unpack(buffer);
  var seconds = unpacker.unpack(buffer);
  var nanoseconds = unpacker.unpack(buffer);
  return new _temporalTypes.Duration(months, days, seconds, nanoseconds);
}
/**
 * Pack given local time.
 * @param {LocalTime} value the local time value to pack.
 * @param {Packer} packer the packer to use.
 * @param {function} onError the error callback.
 */


function packLocalTime(value, packer, onError) {
  var nanoOfDay = (0, _temporalUtil.localTimeToNanoOfDay)(value.hour, value.minute, value.second, value.nanosecond);
  var packableStructFields = [packer.packable(nanoOfDay, onError)];
  packer.packStruct(LOCAL_TIME, packableStructFields, onError);
}
/**
 * Unpack local time value using the given unpacker.
 * @param {Unpacker} unpacker the unpacker to use.
 * @param {number} structSize the retrieved struct size.
 * @param {BaseBuffer} buffer the buffer to unpack from.
 * @param {boolean} disableLosslessIntegers if integer properties in the result local time should be native JS numbers.
 * @return {LocalTime} the unpacked local time value.
 */


function unpackLocalTime(unpacker, structSize, buffer, disableLosslessIntegers) {
  unpacker._verifyStructSize('LocalTime', LOCAL_TIME_STRUCT_SIZE, structSize);

  var nanoOfDay = unpacker.unpackInteger(buffer);
  var result = (0, _temporalUtil.nanoOfDayToLocalTime)(nanoOfDay);
  return convertIntegerPropsIfNeeded(result, disableLosslessIntegers);
}
/**
 * Pack given time.
 * @param {Time} value the time value to pack.
 * @param {Packer} packer the packer to use.
 * @param {function} onError the error callback.
 */


function packTime(value, packer, onError) {
  var nanoOfDay = (0, _temporalUtil.localTimeToNanoOfDay)(value.hour, value.minute, value.second, value.nanosecond);
  var offsetSeconds = (0, _integer["int"])(value.timeZoneOffsetSeconds);
  var packableStructFields = [packer.packable(nanoOfDay, onError), packer.packable(offsetSeconds, onError)];
  packer.packStruct(TIME, packableStructFields, onError);
}
/**
 * Unpack time value using the given unpacker.
 * @param {Unpacker} unpacker the unpacker to use.
 * @param {number} structSize the retrieved struct size.
 * @param {BaseBuffer} buffer the buffer to unpack from.
 * @param {boolean} disableLosslessIntegers if integer properties in the result time should be native JS numbers.
 * @return {Time} the unpacked time value.
 */


function unpackTime(unpacker, structSize, buffer, disableLosslessIntegers) {
  unpacker._verifyStructSize('Time', TIME_STRUCT_SIZE, structSize);

  var nanoOfDay = unpacker.unpackInteger(buffer);
  var offsetSeconds = unpacker.unpackInteger(buffer);
  var localTime = (0, _temporalUtil.nanoOfDayToLocalTime)(nanoOfDay);
  var result = new _temporalTypes.Time(localTime.hour, localTime.minute, localTime.second, localTime.nanosecond, offsetSeconds);
  return convertIntegerPropsIfNeeded(result, disableLosslessIntegers);
}
/**
 * Pack given neo4j date.
 * @param {Date} value the date value to pack.
 * @param {Packer} packer the packer to use.
 * @param {function} onError the error callback.
 */


function packDate(value, packer, onError) {
  var epochDay = (0, _temporalUtil.dateToEpochDay)(value.year, value.month, value.day);
  var packableStructFields = [packer.packable(epochDay, onError)];
  packer.packStruct(DATE, packableStructFields, onError);
}
/**
 * Unpack neo4j date value using the given unpacker.
 * @param {Unpacker} unpacker the unpacker to use.
 * @param {number} structSize the retrieved struct size.
 * @param {BaseBuffer} buffer the buffer to unpack from.
 * @param {boolean} disableLosslessIntegers if integer properties in the result date should be native JS numbers.
 * @return {Date} the unpacked neo4j date value.
 */


function unpackDate(unpacker, structSize, buffer, disableLosslessIntegers) {
  unpacker._verifyStructSize('Date', DATE_STRUCT_SIZE, structSize);

  var epochDay = unpacker.unpackInteger(buffer);
  var result = (0, _temporalUtil.epochDayToDate)(epochDay);
  return convertIntegerPropsIfNeeded(result, disableLosslessIntegers);
}
/**
 * Pack given local date time.
 * @param {LocalDateTime} value the local date time value to pack.
 * @param {Packer} packer the packer to use.
 * @param {function} onError the error callback.
 */


function packLocalDateTime(value, packer, onError) {
  var epochSecond = (0, _temporalUtil.localDateTimeToEpochSecond)(value.year, value.month, value.day, value.hour, value.minute, value.second, value.nanosecond);
  var nano = (0, _integer["int"])(value.nanosecond);
  var packableStructFields = [packer.packable(epochSecond, onError), packer.packable(nano, onError)];
  packer.packStruct(LOCAL_DATE_TIME, packableStructFields, onError);
}
/**
 * Unpack local date time value using the given unpacker.
 * @param {Unpacker} unpacker the unpacker to use.
 * @param {number} structSize the retrieved struct size.
 * @param {BaseBuffer} buffer the buffer to unpack from.
 * @param {boolean} disableLosslessIntegers if integer properties in the result local date-time should be native JS numbers.
 * @return {LocalDateTime} the unpacked local date time value.
 */


function unpackLocalDateTime(unpacker, structSize, buffer, disableLosslessIntegers) {
  unpacker._verifyStructSize('LocalDateTime', LOCAL_DATE_TIME_STRUCT_SIZE, structSize);

  var epochSecond = unpacker.unpackInteger(buffer);
  var nano = unpacker.unpackInteger(buffer);
  var result = (0, _temporalUtil.epochSecondAndNanoToLocalDateTime)(epochSecond, nano);
  return convertIntegerPropsIfNeeded(result, disableLosslessIntegers);
}
/**
 * Pack given date time.
 * @param {DateTime} value the date time value to pack.
 * @param {Packer} packer the packer to use.
 * @param {function} onError the error callback.
 */


function packDateTime(value, packer, onError) {
  if (value.timeZoneId) {
    packDateTimeWithZoneId(value, packer, onError);
  } else {
    packDateTimeWithZoneOffset(value, packer, onError);
  }
}
/**
 * Pack given date time with zone offset.
 * @param {DateTime} value the date time value to pack.
 * @param {Packer} packer the packer to use.
 * @param {function} onError the error callback.
 */


function packDateTimeWithZoneOffset(value, packer, onError) {
  var epochSecond = (0, _temporalUtil.localDateTimeToEpochSecond)(value.year, value.month, value.day, value.hour, value.minute, value.second, value.nanosecond);
  var nano = (0, _integer["int"])(value.nanosecond);
  var timeZoneOffsetSeconds = (0, _integer["int"])(value.timeZoneOffsetSeconds);
  var packableStructFields = [packer.packable(epochSecond, onError), packer.packable(nano, onError), packer.packable(timeZoneOffsetSeconds, onError)];
  packer.packStruct(DATE_TIME_WITH_ZONE_OFFSET, packableStructFields, onError);
}
/**
 * Unpack date time with zone offset value using the given unpacker.
 * @param {Unpacker} unpacker the unpacker to use.
 * @param {number} structSize the retrieved struct size.
 * @param {BaseBuffer} buffer the buffer to unpack from.
 * @param {boolean} disableLosslessIntegers if integer properties in the result date-time should be native JS numbers.
 * @return {DateTime} the unpacked date time with zone offset value.
 */


function unpackDateTimeWithZoneOffset(unpacker, structSize, buffer, disableLosslessIntegers) {
  unpacker._verifyStructSize('DateTimeWithZoneOffset', DATE_TIME_WITH_ZONE_OFFSET_STRUCT_SIZE, structSize);

  var epochSecond = unpacker.unpackInteger(buffer);
  var nano = unpacker.unpackInteger(buffer);
  var timeZoneOffsetSeconds = unpacker.unpackInteger(buffer);
  var localDateTime = (0, _temporalUtil.epochSecondAndNanoToLocalDateTime)(epochSecond, nano);
  var result = new _temporalTypes.DateTime(localDateTime.year, localDateTime.month, localDateTime.day, localDateTime.hour, localDateTime.minute, localDateTime.second, localDateTime.nanosecond, timeZoneOffsetSeconds, null);
  return convertIntegerPropsIfNeeded(result, disableLosslessIntegers);
}
/**
 * Pack given date time with zone id.
 * @param {DateTime} value the date time value to pack.
 * @param {Packer} packer the packer to use.
 * @param {function} onError the error callback.
 */


function packDateTimeWithZoneId(value, packer, onError) {
  var epochSecond = (0, _temporalUtil.localDateTimeToEpochSecond)(value.year, value.month, value.day, value.hour, value.minute, value.second, value.nanosecond);
  var nano = (0, _integer["int"])(value.nanosecond);
  var timeZoneId = value.timeZoneId;
  var packableStructFields = [packer.packable(epochSecond, onError), packer.packable(nano, onError), packer.packable(timeZoneId, onError)];
  packer.packStruct(DATE_TIME_WITH_ZONE_ID, packableStructFields, onError);
}
/**
 * Unpack date time with zone id value using the given unpacker.
 * @param {Unpacker} unpacker the unpacker to use.
 * @param {number} structSize the retrieved struct size.
 * @param {BaseBuffer} buffer the buffer to unpack from.
 * @param {boolean} disableLosslessIntegers if integer properties in the result date-time should be native JS numbers.
 * @return {DateTime} the unpacked date time with zone id value.
 */


function unpackDateTimeWithZoneId(unpacker, structSize, buffer, disableLosslessIntegers) {
  unpacker._verifyStructSize('DateTimeWithZoneId', DATE_TIME_WITH_ZONE_ID_STRUCT_SIZE, structSize);

  var epochSecond = unpacker.unpackInteger(buffer);
  var nano = unpacker.unpackInteger(buffer);
  var timeZoneId = unpacker.unpack(buffer);
  var localDateTime = (0, _temporalUtil.epochSecondAndNanoToLocalDateTime)(epochSecond, nano);
  var result = new _temporalTypes.DateTime(localDateTime.year, localDateTime.month, localDateTime.day, localDateTime.hour, localDateTime.minute, localDateTime.second, localDateTime.nanosecond, null, timeZoneId);
  return convertIntegerPropsIfNeeded(result, disableLosslessIntegers);
}

function convertIntegerPropsIfNeeded(obj, disableLosslessIntegers) {
  if (!disableLosslessIntegers) {
    return obj;
  }

  var clone = Object.create(Object.getPrototypeOf(obj));

  for (var prop in obj) {
    if (obj.hasOwnProperty(prop)) {
      var value = obj[prop];
      clone[prop] = (0, _integer.isInt)(value) ? value.toNumberOrInfinity() : value;
    }
  }

  Object.freeze(clone);
  return clone;
}