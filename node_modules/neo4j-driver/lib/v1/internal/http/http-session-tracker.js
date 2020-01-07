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
var HttpSessionTracker =
/*#__PURE__*/
function () {
  function HttpSessionTracker() {
    (0, _classCallCheck2["default"])(this, HttpSessionTracker);
    this._openSessions = new Set();
  }
  /**
   * Record given session as open.
   * @param {HttpSession} session the newly open session.
   */


  (0, _createClass2["default"])(HttpSessionTracker, [{
    key: "sessionOpened",
    value: function sessionOpened(session) {
      this._openSessions.add(session);
    }
    /**
     * Record given session as close.
     * @param {HttpSession} session the just closed session.
     */

  }, {
    key: "sessionClosed",
    value: function sessionClosed(session) {
      this._openSessions["delete"](session);
    }
    /**
     * Close this tracker and all open sessions.
     */

  }, {
    key: "close",
    value: function close() {
      var sessions = Array.from(this._openSessions);

      this._openSessions.clear();

      return Promise.all(sessions.map(function (session) {
        return closeSession(session);
      }));
    }
  }]);
  return HttpSessionTracker;
}();
/**
 * Close given session and get a promise back.
 * @param {HttpSession} session the session to close.
 * @return {Promise<void>} promise resolved when session is closed.
 */


exports["default"] = HttpSessionTracker;

function closeSession(session) {
  return new Promise(function (resolve) {
    session.close(function () {
      resolve();
    });
  });
}