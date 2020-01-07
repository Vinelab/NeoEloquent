"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _streamObserver = _interopRequireDefault(require("../stream-observer"));

var _httpResponseConverter = _interopRequireDefault(require("./http-response-converter"));

var _error = require("../../error");

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
var HttpRequestRunner =
/*#__PURE__*/
function () {
  function HttpRequestRunner(url, authToken) {
    (0, _classCallCheck2["default"])(this, HttpRequestRunner);
    this._url = url;
    this._authToken = authToken;
    this._converter = new _httpResponseConverter["default"]();
  }
  /**
   * Send a HTTP request to begin a transaction.
   * @return {Promise<number>} promise resolved with the transaction id or rejected with an error.
   */


  (0, _createClass2["default"])(HttpRequestRunner, [{
    key: "beginTransaction",
    value: function beginTransaction() {
      var _this = this;

      var url = beginTransactionUrl(this._url);
      return sendRequest('POST', url, null, this._authToken).then(function (responseJson) {
        var neo4jError = _this._converter.extractError(responseJson);

        if (neo4jError) {
          throw neo4jError;
        }

        return _this._converter.extractTransactionId(responseJson);
      });
    }
    /**
     * Send a HTTP request to commit a transaction.
     * @param {number} transactionId id of the transaction to commit.
     * @return {Promise<void>} promise resolved if transaction got committed or rejected when commit failed.
     */

  }, {
    key: "commitTransaction",
    value: function commitTransaction(transactionId) {
      var _this2 = this;

      var url = commitTransactionUrl(this._url, transactionId);
      return sendRequest('POST', url, null, this._authToken).then(function (responseJson) {
        var neo4jError = _this2._converter.extractError(responseJson);

        if (neo4jError) {
          throw neo4jError;
        }
      });
    }
    /**
     * Send a HTTP request to rollback a transaction.
     * @param {number} transactionId id of the transaction to rollback.
     * @return {Promise<void>} promise resolved if transaction got rolled back or rejected when rollback failed.
     */

  }, {
    key: "rollbackTransaction",
    value: function rollbackTransaction(transactionId) {
      var _this3 = this;

      var url = transactionUrl(this._url, transactionId);
      return sendRequest('DELETE', url, null, this._authToken).then(function (responseJson) {
        var neo4jError = _this3._converter.extractError(responseJson);

        if (neo4jError) {
          throw neo4jError;
        }
      });
    }
    /**
     * Send a HTTP request to execute a query in a transaction with the given id.
     * @param {number} transactionId the transaction id.
     * @param {string} statement the cypher query.
     * @param {object} parameters the cypher query parameters.
     * @return {Promise<StreamObserver>} a promise resolved with {@link StreamObserver} containing either records or error.
     */

  }, {
    key: "runQuery",
    value: function runQuery(transactionId, statement, parameters) {
      var _this4 = this;

      var streamObserver = new _streamObserver["default"]();
      var url = transactionUrl(this._url, transactionId);
      var body = createStatementJson(statement, parameters, this._converter, streamObserver);

      if (!body) {
        // unable to encode given statement and parameters, return a failed stream observer
        return Promise.resolve(streamObserver);
      }

      return sendRequest('POST', url, body, this._authToken).then(function (responseJson) {
        processResponseJson(responseJson, _this4._converter, streamObserver);
      })["catch"](function (error) {
        streamObserver.onError(error);
      }).then(function () {
        return streamObserver;
      });
    }
  }]);
  return HttpRequestRunner;
}();

exports["default"] = HttpRequestRunner;

function sendRequest(method, url, bodyString, authToken) {
  try {
    var options = {
      method: method,
      headers: createHttpHeaders(authToken),
      body: bodyString
    };
    return new Promise(function (resolve, reject) {
      fetch(url, options).then(function (response) {
        return response.json();
      }).then(function (responseJson) {
        return resolve(responseJson);
      })["catch"](function (error) {
        return reject(new _error.Neo4jError(error.message, _error.SERVICE_UNAVAILABLE));
      });
    });
  } catch (e) {
    return Promise.reject(e);
  }
}

function createHttpHeaders(authToken) {
  var headers = new Headers();
  headers.append('Accept', 'application/json; charset=UTF-8');
  headers.append('Content-Type', 'application/json');
  headers.append('Authorization', 'Basic ' + btoa(authToken.principal + ':' + authToken.credentials));
  return headers;
}

function createStatementJson(statement, parameters, converter, streamObserver) {
  try {
    return createStatementJsonOrThrow(statement, parameters, converter);
  } catch (e) {
    streamObserver.onError(e);
    return null;
  }
}

function createStatementJsonOrThrow(statement, parameters, converter) {
  var encodedParameters = converter.encodeStatementParameters(parameters);
  return JSON.stringify({
    statements: [{
      statement: statement,
      parameters: encodedParameters,
      resultDataContents: ['row', 'graph'],
      includeStats: true
    }]
  });
}

function processResponseJson(responseJson, converter, streamObserver) {
  if (!responseJson) {
    // request failed and there is no response
    return;
  }

  try {
    processResponseJsonOrThrow(responseJson, converter, streamObserver);
  } catch (e) {
    streamObserver.onError(e);
  }
}

function processResponseJsonOrThrow(responseJson, converter, streamObserver) {
  var neo4jError = converter.extractError(responseJson);

  if (neo4jError) {
    streamObserver.onError(neo4jError);
  } else {
    var recordMetadata = converter.extractRecordMetadata(responseJson);
    streamObserver.onCompleted(recordMetadata);
    var rawRecords = converter.extractRawRecords(responseJson);
    rawRecords.forEach(function (rawRecord) {
      return streamObserver.onNext(rawRecord);
    });
    var statementMetadata = converter.extractStatementMetadata(responseJson);
    streamObserver.onCompleted(statementMetadata);
  }
}

function beginTransactionUrl(baseUrl) {
  return createUrl(baseUrl, '/db/data/transaction');
}

function commitTransactionUrl(baseUrl, transactionId) {
  return transactionUrl(baseUrl, transactionId) + '/commit';
}

function transactionUrl(baseUrl, transactionId) {
  return beginTransactionUrl(baseUrl) + '/' + transactionId;
}

function createUrl(baseUrl, path) {
  return "".concat(baseUrl.scheme, "://").concat(baseUrl.host, ":").concat(baseUrl.port).concat(path);
}