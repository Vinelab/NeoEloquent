"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _possibleConstructorReturn2 = _interopRequireDefault(require("@babel/runtime/helpers/possibleConstructorReturn"));

var _getPrototypeOf2 = _interopRequireDefault(require("@babel/runtime/helpers/getPrototypeOf"));

var _get2 = _interopRequireDefault(require("@babel/runtime/helpers/get"));

var _inherits2 = _interopRequireDefault(require("@babel/runtime/helpers/inherits"));

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _streamObserver = _interopRequireDefault(require("./internal/stream-observer"));

var _result = _interopRequireDefault(require("./result"));

var _util = require("./internal/util");

var _connectionHolder = require("./internal/connection-holder");

var _bookmark = _interopRequireDefault(require("./internal/bookmark"));

var _txConfig = _interopRequireDefault(require("./internal/tx-config"));

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
 * Represents a transaction in the Neo4j database.
 *
 * @access public
 */
var Transaction =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {ConnectionHolder} connectionHolder - the connection holder to get connection from.
   * @param {function()} onClose - Function to be called when transaction is committed or rolled back.
   * @param {function(bookmark: Bookmark)} onBookmark callback invoked when new bookmark is produced.
   */
  function Transaction(connectionHolder, onClose, onBookmark) {
    (0, _classCallCheck2["default"])(this, Transaction);
    this._connectionHolder = connectionHolder;
    this._state = _states.ACTIVE;
    this._onClose = onClose;
    this._onBookmark = onBookmark;
  }

  (0, _createClass2["default"])(Transaction, [{
    key: "_begin",
    value: function _begin(bookmark, txConfig) {
      var _this = this;

      var streamObserver = new _TransactionStreamObserver(this);

      this._connectionHolder.getConnection(streamObserver).then(function (conn) {
        return conn.protocol().beginTransaction(bookmark, txConfig, _this._connectionHolder.mode(), streamObserver);
      })["catch"](function (error) {
        return streamObserver.onError(error);
      });
    }
    /**
     * Run Cypher statement
     * Could be called with a statement object i.e.: `{text: "MATCH ...", parameters: {param: 1}}`
     * or with the statement and parameters as separate arguments.
     * @param {mixed} statement - Cypher statement to execute
     * @param {Object} parameters - Map with parameters to use in statement
     * @return {Result} New Result
     */

  }, {
    key: "run",
    value: function run(statement, parameters) {
      var _validateStatementAnd = (0, _util.validateStatementAndParameters)(statement, parameters),
          query = _validateStatementAnd.query,
          params = _validateStatementAnd.params;

      return this._state.run(this._connectionHolder, new _TransactionStreamObserver(this), query, params);
    }
    /**
     * Commits the transaction and returns the result.
     *
     * After committing the transaction can no longer be used.
     *
     * @returns {Result} New Result
     */

  }, {
    key: "commit",
    value: function commit() {
      var committed = this._state.commit(this._connectionHolder, new _TransactionStreamObserver(this));

      this._state = committed.state; // clean up

      this._onClose();

      return committed.result;
    }
    /**
     * Rollbacks the transaction.
     *
     * After rolling back, the transaction can no longer be used.
     *
     * @returns {Result} New Result
     */

  }, {
    key: "rollback",
    value: function rollback() {
      var committed = this._state.rollback(this._connectionHolder, new _TransactionStreamObserver(this));

      this._state = committed.state; // clean up

      this._onClose();

      return committed.result;
    }
    /**
     * Check if this transaction is active, which means commit and rollback did not happen.
     * @return {boolean} `true` when not committed and not rolled back, `false` otherwise.
     */

  }, {
    key: "isOpen",
    value: function isOpen() {
      return this._state === _states.ACTIVE;
    }
  }, {
    key: "_onError",
    value: function _onError() {
      // error will be "acknowledged" by sending a RESET message
      // database will then forget about this transaction and cleanup all corresponding resources
      // it is thus safe to move this transaction to a FAILED state and disallow any further interactions with it
      this._state = _states.FAILED;

      this._onClose(); // release connection back to the pool


      return this._connectionHolder.releaseConnection();
    }
  }]);
  return Transaction;
}();
/** Internal stream observer used for transactional results */


var _TransactionStreamObserver =
/*#__PURE__*/
function (_StreamObserver) {
  (0, _inherits2["default"])(_TransactionStreamObserver, _StreamObserver);

  function _TransactionStreamObserver(tx) {
    var _this2;

    (0, _classCallCheck2["default"])(this, _TransactionStreamObserver);
    _this2 = (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(_TransactionStreamObserver).call(this));
    _this2._tx = tx;
    return _this2;
  }

  (0, _createClass2["default"])(_TransactionStreamObserver, [{
    key: "onError",
    value: function onError(error) {
      var _this3 = this;

      if (!this._hasFailed) {
        this._tx._onError().then(function () {
          (0, _get2["default"])((0, _getPrototypeOf2["default"])(_TransactionStreamObserver.prototype), "onError", _this3).call(_this3, error);
        });
      }
    }
  }, {
    key: "onCompleted",
    value: function onCompleted(meta) {
      (0, _get2["default"])((0, _getPrototypeOf2["default"])(_TransactionStreamObserver.prototype), "onCompleted", this).call(this, meta);
      var bookmark = new _bookmark["default"](meta.bookmark);

      this._tx._onBookmark(bookmark);
    }
  }]);
  return _TransactionStreamObserver;
}(_streamObserver["default"]);
/** internal state machine of the transaction */


var _states = {
  // The transaction is running with no explicit success or failure marked
  ACTIVE: {
    commit: function commit(connectionHolder, observer) {
      return {
        result: finishTransaction(true, connectionHolder, observer),
        state: _states.SUCCEEDED
      };
    },
    rollback: function rollback(connectionHolder, observer) {
      return {
        result: finishTransaction(false, connectionHolder, observer),
        state: _states.ROLLED_BACK
      };
    },
    run: function run(connectionHolder, observer, statement, parameters) {
      // RUN in explicit transaction can't contain bookmarks and transaction configuration
      var bookmark = _bookmark["default"].empty();

      var txConfig = _txConfig["default"].empty();

      connectionHolder.getConnection(observer).then(function (conn) {
        return conn.protocol().run(statement, parameters, bookmark, txConfig, connectionHolder.mode(), observer);
      })["catch"](function (error) {
        return observer.onError(error);
      });
      return _newRunResult(observer, statement, parameters, function () {
        return observer.serverMetadata();
      });
    }
  },
  // An error has occurred, transaction can no longer be used and no more messages will
  // be sent for this transaction.
  FAILED: {
    commit: function commit(connectionHolder, observer) {
      observer.onError({
        error: 'Cannot commit statements in this transaction, because previous statements in the ' + 'transaction has failed and the transaction has been rolled back. Please start a new' + ' transaction to run another statement.'
      });
      return {
        result: _newDummyResult(observer, 'COMMIT', {}),
        state: _states.FAILED
      };
    },
    rollback: function rollback(connectionHolder, observer) {
      observer.markCompleted();
      return {
        result: _newDummyResult(observer, 'ROLLBACK', {}),
        state: _states.FAILED
      };
    },
    run: function run(connectionHolder, observer, statement, parameters) {
      observer.onError({
        error: 'Cannot run statement, because previous statements in the ' + 'transaction has failed and the transaction has already been rolled back.'
      });
      return _newDummyResult(observer, statement, parameters);
    }
  },
  // This transaction has successfully committed
  SUCCEEDED: {
    commit: function commit(connectionHolder, observer) {
      observer.onError({
        error: 'Cannot commit statements in this transaction, because commit has already been successfully called on the transaction and transaction has been closed. Please start a new' + ' transaction to run another statement.'
      });
      return {
        result: _newDummyResult(observer, 'COMMIT', {}),
        state: _states.SUCCEEDED
      };
    },
    rollback: function rollback(connectionHolder, observer) {
      observer.onError({
        error: 'Cannot rollback transaction, because transaction has already been successfully closed.'
      });
      return {
        result: _newDummyResult(observer, 'ROLLBACK', {}),
        state: _states.SUCCEEDED
      };
    },
    run: function run(connectionHolder, observer, statement, parameters) {
      observer.onError({
        error: 'Cannot run statement, because transaction has already been successfully closed.'
      });
      return _newDummyResult(observer, statement, parameters);
    }
  },
  // This transaction has been rolled back
  ROLLED_BACK: {
    commit: function commit(connectionHolder, observer) {
      observer.onError({
        error: 'Cannot commit this transaction, because it has already been rolled back.'
      });
      return {
        result: _newDummyResult(observer, 'COMMIT', {}),
        state: _states.ROLLED_BACK
      };
    },
    rollback: function rollback(connectionHolder, observer) {
      observer.onError({
        error: 'Cannot rollback transaction, because transaction has already been rolled back.'
      });
      return {
        result: _newDummyResult(observer, 'ROLLBACK', {}),
        state: _states.ROLLED_BACK
      };
    },
    run: function run(connectionHolder, observer, statement, parameters) {
      observer.onError({
        error: 'Cannot run statement, because transaction has already been rolled back.'
      });
      return _newDummyResult(observer, statement, parameters);
    }
  }
};

function finishTransaction(commit, connectionHolder, observer) {
  connectionHolder.getConnection(observer).then(function (connection) {
    if (commit) {
      return connection.protocol().commitTransaction(observer);
    } else {
      return connection.protocol().rollbackTransaction(observer);
    }
  })["catch"](function (error) {
    return observer.onError(error);
  }); // for commit & rollback we need result that uses real connection holder and notifies it when
  // connection is not needed and can be safely released to the pool

  return new _result["default"](observer, commit ? 'COMMIT' : 'ROLLBACK', {}, emptyMetadataSupplier, connectionHolder);
}
/**
 * Creates a {@link Result} with empty connection holder.
 * Should be used as a result for running cypher statements. They can result in metadata but should not
 * influence real connection holder to release connections because single transaction can have
 * {@link Transaction#run} called multiple times.
 * @param {StreamObserver} observer - an observer for the created result.
 * @param {string} statement - the cypher statement that produced the result.
 * @param {object} parameters - the parameters for cypher statement that produced the result.
 * @param {function} metadataSupplier - the function that returns a metadata object.
 * @return {Result} new result.
 * @private
 */


function _newRunResult(observer, statement, parameters, metadataSupplier) {
  return new _result["default"](observer, statement, parameters, metadataSupplier, _connectionHolder.EMPTY_CONNECTION_HOLDER);
}
/**
 * Creates a {@link Result} without metadata supplier and with empty connection holder.
 * For cases when result represents an intermediate or failed action, does not require any metadata and does not
 * need to influence real connection holder to release connections.
 * @param {StreamObserver} observer - an observer for the created result.
 * @param {string} statement - the cypher statement that produced the result.
 * @param {object} parameters - the parameters for cypher statement that produced the result.
 * @return {Result} new result.
 * @private
 */


function _newDummyResult(observer, statement, parameters) {
  return new _result["default"](observer, statement, parameters, emptyMetadataSupplier, _connectionHolder.EMPTY_CONNECTION_HOLDER);
}

function emptyMetadataSupplier() {
  return {};
}

var _default = Transaction;
exports["default"] = _default;