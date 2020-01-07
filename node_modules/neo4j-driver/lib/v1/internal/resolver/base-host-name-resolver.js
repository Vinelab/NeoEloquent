"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

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
var BaseHostNameResolver =
/*#__PURE__*/
function () {
  function BaseHostNameResolver() {
    (0, _classCallCheck2["default"])(this, BaseHostNameResolver);
  }

  (0, _createClass2["default"])(BaseHostNameResolver, [{
    key: "resolve",
    value: function resolve() {
      throw new Error('Abstract function');
    }
    /**
     * @protected
     */

  }, {
    key: "_resolveToItself",
    value: function _resolveToItself(address) {
      return Promise.resolve([address]);
    }
  }]);
  return BaseHostNameResolver;
}();

exports["default"] = BaseHostNameResolver;