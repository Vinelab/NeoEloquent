"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = exports.statementType = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _integer = require("./integer");

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
 * A ResultSummary instance contains structured metadata for a {@link Result}.
 * @access public
 */
var ResultSummary =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {string} statement - The statement this summary is for
   * @param {Object} parameters - Parameters for the statement
   * @param {Object} metadata - Statement metadata
   */
  function ResultSummary(statement, parameters, metadata) {
    (0, _classCallCheck2["default"])(this, ResultSummary);

    /**
     * The statement and parameters this summary is for.
     * @type {{text: string, parameters: Object}}
     * @public
     */
    this.statement = {
      text: statement,
      parameters: parameters
      /**
       * The type of statement executed. Can be "r" for read-only statement, "rw" for read-write statement,
       * "w" for write-only statement and "s" for schema-write statement.
       * String constants are available in {@link statementType} object.
       * @type {string}
       * @public
       */

    };
    this.statementType = metadata.type;
    /**
     * Counters for operations the statement triggered.
     * @type {StatementStatistics}
     * @public
     */

    this.counters = new StatementStatistics(metadata.stats || {}); // for backwards compatibility, remove in future version

    this.updateStatistics = this.counters;
    /**
     * This describes how the database will execute the statement.
     * Statement plan for the executed statement if available, otherwise undefined.
     * Will only be populated for queries that start with "EXPLAIN".
     * @type {Plan}
     */

    this.plan = metadata.plan || metadata.profile ? new Plan(metadata.plan || metadata.profile) : false;
    /**
     * This describes how the database did execute your statement. This will contain detailed information about what
     * each step of the plan did. Profiled statement plan for the executed statement if available, otherwise undefined.
     * Will only be populated for queries that start with "PROFILE".
     * @type {ProfiledPlan}
     * @public
     */

    this.profile = metadata.profile ? new ProfiledPlan(metadata.profile) : false;
    /**
     * An array of notifications that might arise when executing the statement. Notifications can be warnings about
     * problematic statements or other valuable information that can be presented in a client. Unlike failures
     * or errors, notifications do not affect the execution of a statement.
     * @type {Array<Notification>}
     * @public
     */

    this.notifications = this._buildNotifications(metadata.notifications);
    /**
     * The basic information of the server where the result is obtained from.
     * @type {ServerInfo}
     * @public
     */

    this.server = new ServerInfo(metadata.server);
    /**
     * The time it took the server to consume the result.
     * @type {number}
     * @public
     */

    this.resultConsumedAfter = metadata.result_consumed_after;
    /**
     * The time it took the server to make the result available for consumption in milliseconds.
     * @type {number}
     * @public
     */

    this.resultAvailableAfter = metadata.result_available_after;
  }

  (0, _createClass2["default"])(ResultSummary, [{
    key: "_buildNotifications",
    value: function _buildNotifications(notifications) {
      if (!notifications) {
        return [];
      }

      return notifications.map(function (n) {
        return new Notification(n);
      });
    }
    /**
     * Check if the result summary has a plan
     * @return {boolean}
     */

  }, {
    key: "hasPlan",
    value: function hasPlan() {
      return this.plan instanceof Plan;
    }
    /**
     * Check if the result summary has a profile
     * @return {boolean}
     */

  }, {
    key: "hasProfile",
    value: function hasProfile() {
      return this.profile instanceof ProfiledPlan;
    }
  }]);
  return ResultSummary;
}();
/**
 * Class for execution plan received by prepending Cypher with EXPLAIN.
 * @access public
 */


var Plan =
/**
 * Create a Plan instance
 * @constructor
 * @param {Object} plan - Object with plan data
 */
function Plan(plan) {
  (0, _classCallCheck2["default"])(this, Plan);
  this.operatorType = plan.operatorType;
  this.identifiers = plan.identifiers;
  this.arguments = plan.args;
  this.children = plan.children ? plan.children.map(function (child) {
    return new Plan(child);
  }) : [];
};
/**
 * Class for execution plan received by prepending Cypher with PROFILE.
 * @access public
 */


var ProfiledPlan =
/**
 * Create a ProfiledPlan instance
 * @constructor
 * @param {Object} profile - Object with profile data
 */
function ProfiledPlan(profile) {
  (0, _classCallCheck2["default"])(this, ProfiledPlan);
  this.operatorType = profile.operatorType;
  this.identifiers = profile.identifiers;
  this.arguments = profile.args;
  this.dbHits = profile.args.DbHits.toInt();
  this.rows = profile.args.Rows.toInt();
  this.children = profile.children ? profile.children.map(function (child) {
    return new ProfiledPlan(child);
  }) : [];
};
/**
 * Get statistical information for a {@link Result}.
 * @access public
 */


var StatementStatistics =
/*#__PURE__*/
function () {
  /**
   * Structurize the statistics
   * @constructor
   * @param {Object} statistics - Result statistics
   */
  function StatementStatistics(statistics) {
    var _this = this;

    (0, _classCallCheck2["default"])(this, StatementStatistics);
    this._stats = {
      nodesCreated: 0,
      nodesDeleted: 0,
      relationshipsCreated: 0,
      relationshipsDeleted: 0,
      propertiesSet: 0,
      labelsAdded: 0,
      labelsRemoved: 0,
      indexesAdded: 0,
      indexesRemoved: 0,
      constraintsAdded: 0,
      constraintsRemoved: 0
    };
    Object.keys(statistics).forEach(function (index) {
      // To camelCase
      _this._stats[index.replace(/(-\w)/g, function (m) {
        return m[1].toUpperCase();
      })] = (0, _integer.isInt)(statistics[index]) ? statistics[index].toInt() : statistics[index];
    });
  }
  /**
   * Did the database get updated?
   * @return {boolean}
   */


  (0, _createClass2["default"])(StatementStatistics, [{
    key: "containsUpdates",
    value: function containsUpdates() {
      var _this2 = this;

      return Object.keys(this._stats).reduce(function (last, current) {
        return last + _this2._stats[current];
      }, 0) > 0;
    }
    /**
     * @return {Number} - Number of nodes created.
     */

  }, {
    key: "nodesCreated",
    value: function nodesCreated() {
      return this._stats.nodesCreated;
    }
    /**
     * @return {Number} - Number of nodes deleted.
     */

  }, {
    key: "nodesDeleted",
    value: function nodesDeleted() {
      return this._stats.nodesDeleted;
    }
    /**
     * @return {Number} - Number of relationships created.
     */

  }, {
    key: "relationshipsCreated",
    value: function relationshipsCreated() {
      return this._stats.relationshipsCreated;
    }
    /**
     * @return {Number} - Number of nodes deleted.
     */

  }, {
    key: "relationshipsDeleted",
    value: function relationshipsDeleted() {
      return this._stats.relationshipsDeleted;
    }
    /**
     * @return {Number} - Number of properties set.
     */

  }, {
    key: "propertiesSet",
    value: function propertiesSet() {
      return this._stats.propertiesSet;
    }
    /**
     * @return {Number} - Number of labels added.
     */

  }, {
    key: "labelsAdded",
    value: function labelsAdded() {
      return this._stats.labelsAdded;
    }
    /**
     * @return {Number} - Number of labels removed.
     */

  }, {
    key: "labelsRemoved",
    value: function labelsRemoved() {
      return this._stats.labelsRemoved;
    }
    /**
     * @return {Number} - Number of indexes added.
     */

  }, {
    key: "indexesAdded",
    value: function indexesAdded() {
      return this._stats.indexesAdded;
    }
    /**
     * @return {Number} - Number of indexes removed.
     */

  }, {
    key: "indexesRemoved",
    value: function indexesRemoved() {
      return this._stats.indexesRemoved;
    }
    /**
     * @return {Number} - Number of constraints added.
     */

  }, {
    key: "constraintsAdded",
    value: function constraintsAdded() {
      return this._stats.constraintsAdded;
    }
    /**
     * @return {Number} - Number of constraints removed.
     */

  }, {
    key: "constraintsRemoved",
    value: function constraintsRemoved() {
      return this._stats.constraintsRemoved;
    }
  }]);
  return StatementStatistics;
}();
/**
 * Class for Cypher notifications
 * @access public
 */


var Notification =
/*#__PURE__*/
function () {
  /**
   * Create a Notification instance
   * @constructor
   * @param {Object} notification - Object with notification data
   */
  function Notification(notification) {
    (0, _classCallCheck2["default"])(this, Notification);
    this.code = notification.code;
    this.title = notification.title;
    this.description = notification.description;
    this.severity = notification.severity;
    this.position = Notification._constructPosition(notification.position);
  }

  (0, _createClass2["default"])(Notification, null, [{
    key: "_constructPosition",
    value: function _constructPosition(pos) {
      if (!pos) {
        return {};
      }

      return {
        offset: pos.offset.toInt(),
        line: pos.line.toInt(),
        column: pos.column.toInt()
      };
    }
  }]);
  return Notification;
}();
/**
 * Class for exposing server info from a result.
 * @access public
 */


var ServerInfo =
/**
 * Create a ServerInfo instance
 * @constructor
 * @param {Object} serverMeta - Object with serverMeta data
 */
function ServerInfo(serverMeta) {
  (0, _classCallCheck2["default"])(this, ServerInfo);

  if (serverMeta) {
    this.address = serverMeta.address;
    this.version = serverMeta.version;
  }
};

var statementType = {
  READ_ONLY: 'r',
  READ_WRITE: 'rw',
  WRITE_ONLY: 'w',
  SCHEMA_WRITE: 's'
};
exports.statementType = statementType;
var _default = ResultSummary;
exports["default"] = _default;