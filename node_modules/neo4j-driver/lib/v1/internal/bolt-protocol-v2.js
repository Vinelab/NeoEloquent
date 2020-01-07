"use strict";

var _interopRequireWildcard = require("@babel/runtime/helpers/interopRequireWildcard");

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

var _boltProtocolV = _interopRequireDefault(require("./bolt-protocol-v1"));

var v2 = _interopRequireWildcard(require("./packstream-v2"));

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
function (_BoltProtocolV) {
  (0, _inherits2["default"])(BoltProtocol, _BoltProtocolV);

  function BoltProtocol() {
    (0, _classCallCheck2["default"])(this, BoltProtocol);
    return (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(BoltProtocol).apply(this, arguments));
  }

  (0, _createClass2["default"])(BoltProtocol, [{
    key: "_createPacker",
    value: function _createPacker(chunker) {
      return new v2.Packer(chunker);
    }
  }, {
    key: "_createUnpacker",
    value: function _createUnpacker(disableLosslessIntegers) {
      return new v2.Unpacker(disableLosslessIntegers);
    }
  }]);
  return BoltProtocol;
}(_boltProtocolV["default"]);

exports["default"] = BoltProtocol;