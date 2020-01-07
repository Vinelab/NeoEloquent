"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.isPoint = isPoint;
exports.Point = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _util = require("./internal/util");

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
var POINT_IDENTIFIER_PROPERTY = '__isPoint__';
/**
 * Represents a single two or three-dimensional point in a particular coordinate reference system.
 * Created `Point` objects are frozen with `Object.freeze()` in constructor and thus immutable.
 */

var Point =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {Integer|number} srid the coordinate reference system identifier.
   * @param {number} x the `x` coordinate of the point.
   * @param {number} y the `y` coordinate of the point.
   * @param {number} [z=undefined] the `y` coordinate of the point or `undefined` if point has 2 dimensions.
   */
  function Point(srid, x, y, z) {
    (0, _classCallCheck2["default"])(this, Point);
    this.srid = (0, _util.assertNumberOrInteger)(srid, 'SRID');
    this.x = (0, _util.assertNumber)(x, 'X coordinate');
    this.y = (0, _util.assertNumber)(y, 'Y coordinate');
    this.z = z === null || z === undefined ? z : (0, _util.assertNumber)(z, 'Z coordinate');
    Object.freeze(this);
  }

  (0, _createClass2["default"])(Point, [{
    key: "toString",
    value: function toString() {
      return this.z || this.z === 0 ? "Point{srid=".concat(formatAsFloat(this.srid), ", x=").concat(formatAsFloat(this.x), ", y=").concat(formatAsFloat(this.y), ", z=").concat(formatAsFloat(this.z), "}") : "Point{srid=".concat(formatAsFloat(this.srid), ", x=").concat(formatAsFloat(this.x), ", y=").concat(formatAsFloat(this.y), "}");
    }
  }]);
  return Point;
}();

exports.Point = Point;

function formatAsFloat(number) {
  return Number.isInteger(number) ? number + '.0' : number.toString();
}

Object.defineProperty(Point.prototype, POINT_IDENTIFIER_PROPERTY, {
  value: true,
  enumerable: false,
  configurable: false
});
/**
 * Test if given object is an instance of {@link Point} class.
 * @param {object} obj the object to test.
 * @return {boolean} `true` if given object is a {@link Point}, `false` otherwise.
 */

function isPoint(obj) {
  return (obj && obj[POINT_IDENTIFIER_PROPERTY]) === true;
}