"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.PathSegment = exports.Path = exports.UnboundRelationship = exports.Relationship = exports.Node = void 0;

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
 * Class for Node Type.
 */
var Node =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {Integer} identity - Unique identity
   * @param {Array<string>} labels - Array for all labels
   * @param {Object} properties - Map with node properties
   */
  function Node(identity, labels, properties) {
    (0, _classCallCheck2["default"])(this, Node);
    this.identity = identity;
    this.labels = labels;
    this.properties = properties;
  }

  (0, _createClass2["default"])(Node, [{
    key: "toString",
    value: function toString() {
      var s = '(' + this.identity;

      for (var i = 0; i < this.labels.length; i++) {
        s += ':' + this.labels[i];
      }

      var keys = Object.keys(this.properties);

      if (keys.length > 0) {
        s += ' {';

        for (var _i = 0; _i < keys.length; _i++) {
          if (_i > 0) s += ',';
          s += keys[_i] + ':' + JSON.stringify(this.properties[keys[_i]]);
        }

        s += '}';
      }

      s += ')';
      return s;
    }
  }]);
  return Node;
}();
/**
 * Class for Relationship Type.
 */


exports.Node = Node;

var Relationship =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {Integer} identity - Unique identity
   * @param {Integer} start - Identity of start Node
   * @param {Integer} end - Identity of end Node
   * @param {string} type - Relationship type
   * @param {Object} properties - Map with relationship properties
   */
  function Relationship(identity, start, end, type, properties) {
    (0, _classCallCheck2["default"])(this, Relationship);
    this.identity = identity;
    this.start = start;
    this.end = end;
    this.type = type;
    this.properties = properties;
  }

  (0, _createClass2["default"])(Relationship, [{
    key: "toString",
    value: function toString() {
      var s = '(' + this.start + ')-[:' + this.type;
      var keys = Object.keys(this.properties);

      if (keys.length > 0) {
        s += ' {';

        for (var i = 0; i < keys.length; i++) {
          if (i > 0) s += ',';
          s += keys[i] + ':' + JSON.stringify(this.properties[keys[i]]);
        }

        s += '}';
      }

      s += ']->(' + this.end + ')';
      return s;
    }
  }]);
  return Relationship;
}();
/**
 * Class for UnboundRelationship Type.
 * @access private
 */


exports.Relationship = Relationship;

var UnboundRelationship =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {Integer} identity - Unique identity
   * @param {string} type - Relationship type
   * @param {Object} properties - Map with relationship properties
   */
  function UnboundRelationship(identity, type, properties) {
    (0, _classCallCheck2["default"])(this, UnboundRelationship);
    this.identity = identity;
    this.type = type;
    this.properties = properties;
  }
  /**
   * Bind relationship
   * @param {Integer} start - Identity of start node
   * @param {Integer} end - Identity of end node
   * @return {Relationship} - Created relationship
   */


  (0, _createClass2["default"])(UnboundRelationship, [{
    key: "bind",
    value: function bind(start, end) {
      return new Relationship(this.identity, start, end, this.type, this.properties);
    }
  }, {
    key: "toString",
    value: function toString() {
      var s = '-[:' + this.type;
      var keys = Object.keys(this.properties);

      if (keys.length > 0) {
        s += ' {';

        for (var i = 0; i < keys.length; i++) {
          if (i > 0) s += ',';
          s += keys[i] + ':' + JSON.stringify(this.properties[keys[i]]);
        }

        s += '}';
      }

      s += ']->';
      return s;
    }
  }]);
  return UnboundRelationship;
}();
/**
 * Class for PathSegment Type.
 */


exports.UnboundRelationship = UnboundRelationship;

var PathSegment =
/**
 * @constructor
 * @param {Node} start - start node
 * @param {Relationship} rel - relationship that connects start and end node
 * @param {Node} end - end node
 */
function PathSegment(start, rel, end) {
  (0, _classCallCheck2["default"])(this, PathSegment);
  this.start = start;
  this.relationship = rel;
  this.end = end;
};
/**
 * Class for Path Type.
 */


exports.PathSegment = PathSegment;

var Path =
/**
 * @constructor
 * @param {Node} start  - start node
 * @param {Node} end - end node
 * @param {Array<PathSegment>} segments - Array of Segments
 */
function Path(start, end, segments) {
  (0, _classCallCheck2["default"])(this, Path);
  this.start = start;
  this.end = end;
  this.segments = segments;
  this.length = segments.length;
};

exports.Path = Path;