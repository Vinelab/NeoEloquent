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

var _boltProtocolV = _interopRequireDefault(require("./bolt-protocol-v2"));

var _requestMessage = _interopRequireDefault(require("./request-message"));

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
    key: "transformMetadata",
    value: function transformMetadata(metadata) {
      if (metadata.t_first) {
        // Bolt V3 uses shorter key 't_first' to represent 'result_available_after'
        // adjust the key to be the same as in Bolt V1 so that ResultSummary can retrieve the value
        metadata.result_available_after = metadata.t_first;
        delete metadata.t_first;
      }

      if (metadata.t_last) {
        // Bolt V3 uses shorter key 't_last' to represent 'result_consumed_after'
        // adjust the key to be the same as in Bolt V1 so that ResultSummary can retrieve the value
        metadata.result_consumed_after = metadata.t_last;
        delete metadata.t_last;
      }

      return metadata;
    }
  }, {
    key: "initialize",
    value: function initialize(userAgent, authToken, observer) {
      prepareToHandleSingleResponse(observer);

      var message = _requestMessage["default"].hello(userAgent, authToken);

      this._connection.write(message, observer, true);
    }
  }, {
    key: "prepareToClose",
    value: function prepareToClose(observer) {
      var message = _requestMessage["default"].goodbye();

      this._connection.write(message, observer, true);
    }
  }, {
    key: "beginTransaction",
    value: function beginTransaction(bookmark, txConfig, mode, observer) {
      prepareToHandleSingleResponse(observer);

      var message = _requestMessage["default"].begin(bookmark, txConfig, mode);

      this._connection.write(message, observer, true);
    }
  }, {
    key: "commitTransaction",
    value: function commitTransaction(observer) {
      prepareToHandleSingleResponse(observer);

      var message = _requestMessage["default"].commit();

      this._connection.write(message, observer, true);
    }
  }, {
    key: "rollbackTransaction",
    value: function rollbackTransaction(observer) {
      prepareToHandleSingleResponse(observer);

      var message = _requestMessage["default"].rollback();

      this._connection.write(message, observer, true);
    }
  }, {
    key: "run",
    value: function run(statement, parameters, bookmark, txConfig, mode, observer) {
      var runMessage = _requestMessage["default"].runWithMetadata(statement, parameters, bookmark, txConfig, mode);

      var pullAllMessage = _requestMessage["default"].pullAll();

      this._connection.write(runMessage, observer, false);

      this._connection.write(pullAllMessage, observer, true);
    }
  }]);
  return BoltProtocol;
}(_boltProtocolV["default"]);

exports["default"] = BoltProtocol;

function prepareToHandleSingleResponse(observer) {
  if (observer && typeof observer.prepareToHandleSingleResponse === 'function') {
    observer.prepareToHandleSingleResponse();
  }
}