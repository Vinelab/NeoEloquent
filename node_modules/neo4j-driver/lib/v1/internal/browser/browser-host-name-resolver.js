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

var _baseHostNameResolver = _interopRequireDefault(require("../resolver/base-host-name-resolver"));

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
var BrowserHostNameResolver =
/*#__PURE__*/
function (_BaseHostNameResolver) {
  (0, _inherits2["default"])(BrowserHostNameResolver, _BaseHostNameResolver);

  function BrowserHostNameResolver() {
    (0, _classCallCheck2["default"])(this, BrowserHostNameResolver);
    return (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(BrowserHostNameResolver).apply(this, arguments));
  }

  (0, _createClass2["default"])(BrowserHostNameResolver, [{
    key: "resolve",
    value: function resolve(address) {
      return this._resolveToItself(address);
    }
  }]);
  return BrowserHostNameResolver;
}(_baseHostNameResolver["default"]);

exports["default"] = BrowserHostNameResolver;