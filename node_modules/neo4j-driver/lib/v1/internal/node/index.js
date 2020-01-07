"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.utf8 = exports.HostNameResolver = exports.Channel = exports.alloc = void 0;

var _nodeBuf = _interopRequireDefault(require("./node-buf"));

var _nodeChannel = _interopRequireDefault(require("./node-channel"));

var _nodeHostNameResolver = _interopRequireDefault(require("./node-host-name-resolver"));

var _nodeUtf = _interopRequireDefault(require("./node-utf8"));

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

/*

This module exports a set of components to be used in NodeJS environment.
They are not compatible with browser environment.
All files that require environment-dependent APIs should import this file by default.
Imports/requires are replaced at build time with `browser/index.js` when building a browser bundle.

NOTE: exports in this module should have exactly the same names/structure as exports in `browser/index.js`.

 */
var alloc = function alloc(arg) {
  return new _nodeBuf["default"](arg);
};

exports.alloc = alloc;
var Channel = _nodeChannel["default"];
exports.Channel = Channel;
var HostNameResolver = _nodeHostNameResolver["default"];
exports.HostNameResolver = HostNameResolver;
var utf8 = _nodeUtf["default"];
exports.utf8 = utf8;