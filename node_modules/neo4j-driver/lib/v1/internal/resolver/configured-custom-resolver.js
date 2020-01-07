"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _serverAddress = _interopRequireDefault(require("../server-address"));

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
function resolveToSelf(address) {
  return Promise.resolve([address]);
}

var ConfiguredCustomResolver =
/*#__PURE__*/
function () {
  function ConfiguredCustomResolver(resolverFunction) {
    (0, _classCallCheck2["default"])(this, ConfiguredCustomResolver);
    this._resolverFunction = resolverFunction || resolveToSelf;
  }

  (0, _createClass2["default"])(ConfiguredCustomResolver, [{
    key: "resolve",
    value: function resolve(seedRouter) {
      var _this = this;

      return new Promise(function (resolve) {
        return resolve(_this._resolverFunction(seedRouter.asHostPort()));
      }).then(function (resolved) {
        if (!Array.isArray(resolved)) {
          throw new TypeError("Configured resolver function should either return an array of addresses or a Promise resolved with an array of addresses." + "Each address is '<host>:<port>'. Got: ".concat(resolved));
        }

        return resolved.map(function (r) {
          return _serverAddress["default"].fromUrl(r);
        });
      });
    }
  }]);
  return ConfiguredCustomResolver;
}();

exports["default"] = ConfiguredCustomResolver;