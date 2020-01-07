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

var _assertThisInitialized2 = _interopRequireDefault(require("@babel/runtime/helpers/assertThisInitialized"));

var _inherits2 = _interopRequireDefault(require("@babel/runtime/helpers/inherits"));

var _driver = require("../../driver");

var _session = _interopRequireDefault(require("../../session"));

var _util = require("../util");

var _error = require("../../error");

var _httpRequestRunner = _interopRequireDefault(require("./http-request-runner"));

var _connectionHolder = require("../connection-holder");

var _result = _interopRequireDefault(require("../../result"));

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
var HttpSession =
/*#__PURE__*/
function (_Session) {
  (0, _inherits2["default"])(HttpSession, _Session);

  function HttpSession(url, authToken, config, sessionTracker) {
    var _this;

    (0, _classCallCheck2["default"])(this, HttpSession);
    _this = (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(HttpSession).call(this, _driver.WRITE, null, null, config));
    _this._ongoingTransactionIds = [];
    _this._serverInfoSupplier = createServerInfoSupplier(url);
    _this._requestRunner = new _httpRequestRunner["default"](url, authToken);
    _this._sessionTracker = sessionTracker;

    _this._sessionTracker.sessionOpened((0, _assertThisInitialized2["default"])(_this));

    return _this;
  }

  (0, _createClass2["default"])(HttpSession, [{
    key: "run",
    value: function run(statement) {
      var _this2 = this;

      var parameters = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

      var _validateStatementAnd = (0, _util.validateStatementAndParameters)(statement, parameters),
          query = _validateStatementAnd.query,
          params = _validateStatementAnd.params;

      return this._requestRunner.beginTransaction().then(function (transactionId) {
        _this2._ongoingTransactionIds.push(transactionId);

        var queryPromise = _this2._requestRunner.runQuery(transactionId, query, params);

        return queryPromise.then(function (streamObserver) {
          if (streamObserver.hasFailed()) {
            return rollbackTransactionAfterQueryFailure(transactionId, streamObserver, _this2._requestRunner);
          } else {
            return commitTransactionAfterQuerySuccess(transactionId, streamObserver, _this2._requestRunner);
          }
        }).then(function (streamObserver) {
          _this2._ongoingTransactionIds = _this2._ongoingTransactionIds.filter(function (id) {
            return id !== transactionId;
          });
          return new _result["default"](streamObserver, query, params, _this2._serverInfoSupplier, _connectionHolder.EMPTY_CONNECTION_HOLDER);
        });
      });
    }
  }, {
    key: "beginTransaction",
    value: function beginTransaction() {
      throwTransactionsNotSupported();
    }
  }, {
    key: "readTransaction",
    value: function readTransaction() {
      throwTransactionsNotSupported();
    }
  }, {
    key: "writeTransaction",
    value: function writeTransaction() {
      throwTransactionsNotSupported();
    }
  }, {
    key: "lastBookmark",
    value: function lastBookmark() {
      throw new _error.Neo4jError('Experimental HTTP driver does not support bookmarks and routing');
    }
  }, {
    key: "close",
    value: function close() {
      var _this3 = this;

      var callback = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : function () {
        return null;
      };

      var rollbackAllOngoingTransactions = this._ongoingTransactionIds.map(function (transactionId) {
        return rollbackTransactionSilently(transactionId, _this3._requestRunner);
      });

      Promise.all(rollbackAllOngoingTransactions).then(function () {
        _this3._sessionTracker.sessionClosed(_this3);

        callback();
      });
    }
  }]);
  return HttpSession;
}(_session["default"]);

exports["default"] = HttpSession;

function rollbackTransactionAfterQueryFailure(transactionId, streamObserver, requestRunner) {
  return rollbackTransactionSilently(transactionId, requestRunner).then(function () {
    return streamObserver;
  });
}

function commitTransactionAfterQuerySuccess(transactionId, streamObserver, requestRunner) {
  return requestRunner.commitTransaction(transactionId)["catch"](function (error) {
    streamObserver.onError(error);
  }).then(function () {
    return streamObserver;
  });
}

function rollbackTransactionSilently(transactionId, requestRunner) {
  return requestRunner.rollbackTransaction(transactionId)["catch"](function () {// ignore all rollback errors
  });
}

function createServerInfoSupplier(url) {
  return function () {
    return {
      server: {
        address: url.hostAndPort
      }
    };
  };
}

function throwTransactionsNotSupported() {
  throw new _error.Neo4jError('Experimental HTTP driver does not support transactions');
}