"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _construct2 = _interopRequireDefault(require("@babel/runtime/helpers/construct"));

var _toConsumableArray2 = _interopRequireDefault(require("@babel/runtime/helpers/toConsumableArray"));

var _typeof2 = _interopRequireDefault(require("@babel/runtime/helpers/typeof"));

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _integer = require("../../integer");

var _graphTypes = require("../../graph-types");

var _error = require("../../error");

var _spatialTypes = require("../../spatial-types");

var _temporalTypes = require("../../temporal-types");

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
var CREDENTIALS_EXPIRED_CODE = 'Neo.ClientError.Security.CredentialsExpired';

var HttpResponseConverter =
/*#__PURE__*/
function () {
  function HttpResponseConverter() {
    (0, _classCallCheck2["default"])(this, HttpResponseConverter);
  }

  (0, _createClass2["default"])(HttpResponseConverter, [{
    key: "encodeStatementParameters",
    value: function encodeStatementParameters(parameters) {
      return encodeQueryParameters(parameters);
    }
    /**
     * Attempts to extract error from transactional cypher endpoint response and convert it to {@link Neo4jError}.
     * @param {object} response the response.
     * @return {Neo4jError|null} new driver friendly error, if exists.
     */

  }, {
    key: "extractError",
    value: function extractError(response) {
      var errors = response.errors;

      if (errors) {
        var error = errors[0];

        if (error) {
          // endpoint returns 'Neo.ClientError.Security.Forbidden' code and 'password_change' that points to another endpoint
          // this is different from code returned via Bolt and less descriptive
          // make code same as in Bolt, if password change is required
          var code = response.password_change ? CREDENTIALS_EXPIRED_CODE : error.code;
          var message = error.message;
          return new _error.Neo4jError(message, code);
        }
      }

      return null;
    }
    /**
     * Extracts transaction id from the db/data/transaction endpoint response.
     * @param {object} response the response.
     * @return {number} the transaction id.
     */

  }, {
    key: "extractTransactionId",
    value: function extractTransactionId(response) {
      var commitUrl = response.commit;

      if (commitUrl) {
        // extract id 42 from commit url like 'http://localhost:7474/db/data/transaction/42/commit'
        var url = commitUrl.replace('/commit', '');
        var transactionIdString = url.substring(url.lastIndexOf('/') + 1);
        var transactionId = parseInt(transactionIdString, 10);

        if (transactionId || transactionId === 0) {
          return transactionId;
        }
      }

      throw new _error.Neo4jError("Unable to extract transaction id from the response JSON: ".concat(JSON.stringify(response)));
    }
    /**
     * Extracts record metadata (array of column names) from transactional cypher endpoint response.
     * @param {object} response the response.
     * @return {object} new metadata object.
     */

  }, {
    key: "extractRecordMetadata",
    value: function extractRecordMetadata(response) {
      var result = extractResult(response);
      var fields = result ? result.columns : [];
      return {
        fields: fields
      };
    }
    /**
     * Extracts raw records (each raw record is just an array of value) from transactional cypher endpoint response.
     * @param {object} response the response.
     * @return {object[][]} raw records from the response.
     */

  }, {
    key: "extractRawRecords",
    value: function extractRawRecords(response) {
      var result = extractResult(response);

      if (result) {
        var data = result.data;

        if (data) {
          return data.map(function (element) {
            return extractRawRecord(element);
          });
        }
      }

      return [];
    }
    /**
     * Extracts metadata for a completed statement.
     * @param {object} response the response.
     * @return {object} metadata as object.
     */

  }, {
    key: "extractStatementMetadata",
    value: function extractStatementMetadata(response) {
      var result = extractResult(response);

      if (result) {
        var stats = result.stats;

        if (stats) {
          var convertedStats = Object.keys(stats).reduce(function (newStats, key) {
            if (key === 'contains_updates') {
              // skip because such key does not exist in bolt
              return newStats;
            } // fix key name for future parsing by StatementStatistics class


            var newKey = (key === 'relationship_deleted' ? 'relationships_deleted' : key).replace('_', '-');
            newStats[newKey] = stats[key];
            return newStats;
          }, {});
          return {
            stats: convertedStats
          };
        }
      }

      return {};
    }
  }]);
  return HttpResponseConverter;
}();

exports["default"] = HttpResponseConverter;

function encodeQueryParameters(parameters) {
  if (parameters && (0, _typeof2["default"])(parameters) === 'object') {
    return Object.keys(parameters).reduce(function (result, key) {
      result[key] = encodeQueryParameter(parameters[key]);
      return result;
    }, {});
  }

  return parameters;
}

function encodeQueryParameter(value) {
  if (value instanceof _graphTypes.Node) {
    throw new _error.Neo4jError('It is not allowed to pass nodes in query parameters', _error.PROTOCOL_ERROR);
  } else if (value instanceof _graphTypes.Relationship) {
    throw new _error.Neo4jError('It is not allowed to pass relationships in query parameters', _error.PROTOCOL_ERROR);
  } else if (value instanceof _graphTypes.Path) {
    throw new _error.Neo4jError('It is not allowed to pass paths in query parameters', _error.PROTOCOL_ERROR);
  } else if ((0, _spatialTypes.isPoint)(value)) {
    throw newUnsupportedParameterError('points');
  } else if ((0, _temporalTypes.isDate)(value)) {
    throw newUnsupportedParameterError('dates');
  } else if ((0, _temporalTypes.isDateTime)(value)) {
    throw newUnsupportedParameterError('date-time');
  } else if ((0, _temporalTypes.isDuration)(value)) {
    throw newUnsupportedParameterError('durations');
  } else if ((0, _temporalTypes.isLocalDateTime)(value)) {
    throw newUnsupportedParameterError('local date-time');
  } else if ((0, _temporalTypes.isLocalTime)(value)) {
    throw newUnsupportedParameterError('local time');
  } else if ((0, _temporalTypes.isTime)(value)) {
    throw newUnsupportedParameterError('time');
  } else if ((0, _integer.isInt)(value)) {
    return value.toNumber();
  } else if (Array.isArray(value)) {
    return value.map(function (element) {
      return encodeQueryParameter(element);
    });
  } else if ((0, _typeof2["default"])(value) === 'object') {
    return encodeQueryParameters(value);
  } else {
    return value;
  }
}

function newUnsupportedParameterError(name) {
  return new _error.Neo4jError("It is not allowed to pass ".concat(name, " in query parameters when using HTTP endpoint. ") + "Please consider using Cypher functions to create ".concat(name, " so that query parameters are plain objects."), _error.PROTOCOL_ERROR);
}

function extractResult(response) {
  var results = response.results;

  if (results) {
    var result = results[0];

    if (result) {
      return result;
    }
  }

  return null;
}

function extractRawRecord(data) {
  var row = data.row;
  var nodesById = indexNodesById(data);
  var relationshipsById = indexRelationshipsById(data);

  if (row) {
    return row.map(function (ignore, index) {
      return extractRawRecordElement(index, data, nodesById, relationshipsById);
    });
  }

  return [];
}

function indexNodesById(data) {
  var graph = data.graph;

  if (graph) {
    var nodes = graph.nodes;

    if (nodes) {
      return nodes.reduce(function (result, node) {
        var identity = convertNumber(node.id);
        var labels = node.labels;
        var properties = convertPrimitiveValue(node.properties);
        result[node.id] = new _graphTypes.Node(identity, labels, properties);
        return result;
      }, {});
    }
  }

  return {};
}

function indexRelationshipsById(data) {
  var graph = data.graph;

  if (graph) {
    var relationships = graph.relationships;

    if (relationships) {
      return relationships.reduce(function (result, relationship) {
        var identity = convertNumber(relationship.id);
        var startNode = convertNumber(relationship.startNode);
        var endNode = convertNumber(relationship.endNode);
        var type = relationship.type;
        var properties = convertPrimitiveValue(relationship.properties);
        result[relationship.id] = new _graphTypes.Relationship(identity, startNode, endNode, type, properties);
        return result;
      }, {});
    }
  }

  return {};
}

function extractRawRecordElement(index, data, nodesById, relationshipsById) {
  var element = data.row ? data.row[index] : null;
  var elementMetadata = data.meta ? data.meta[index] : null;

  if (elementMetadata) {
    // element is either a graph, spatial or temporal type
    return convertComplexValue(element, elementMetadata, nodesById, relationshipsById);
  } else {
    // element is a primitive, like number, string, array or object
    return convertPrimitiveValue(element);
  }
}

function convertComplexValue(element, elementMetadata, nodesById, relationshipsById) {
  if (isNodeMetadata(elementMetadata)) {
    return nodesById[elementMetadata.id];
  } else if (isRelationshipMetadata(elementMetadata)) {
    return relationshipsById[elementMetadata.id];
  } else if (isPathMetadata(elementMetadata)) {
    return convertPath(elementMetadata, nodesById, relationshipsById);
  } else if (isPointMetadata(elementMetadata)) {
    return convertPoint(element);
  } else {
    return element;
  }
}

function convertPath(metadata, nodesById, relationshipsById) {
  var startNode = null;
  var relationship = null;
  var pathSegments = [];

  for (var i = 0; i < metadata.length; i++) {
    var element = metadata[i];
    var elementId = element.id;
    var elementType = element.type;
    var nodeExpected = startNode === null && relationship === null || startNode !== null && relationship !== null;

    if (nodeExpected && elementType !== 'node') {
      throw new _error.Neo4jError("Unable to parse path, node expected but got: ".concat(JSON.stringify(element), " in ").concat(JSON.stringify(metadata)));
    }

    if (!nodeExpected && elementType === 'node') {
      throw new _error.Neo4jError("Unable to parse path, relationship expected but got: ".concat(JSON.stringify(element), " in ").concat(JSON.stringify(metadata)));
    }

    if (nodeExpected) {
      var node = nodesById[elementId];

      if (startNode === null) {
        startNode = node;
      } else if (startNode !== null && relationship !== null) {
        var pathSegment = new _graphTypes.PathSegment(startNode, relationship, node);
        pathSegments.push(pathSegment);
        startNode = node;
        relationship = null;
      } else {
        throw new _error.Neo4jError("Unable to parse path, illegal node configuration: ".concat(JSON.stringify(metadata)));
      }
    } else {
      if (relationship === null) {
        relationship = relationshipsById[elementId];
      } else {
        throw new _error.Neo4jError("Unable to parse path, illegal relationship configuration: ".concat(JSON.stringify(metadata)));
      }
    }
  }

  var lastPathSegment = pathSegments[pathSegments.length - 1];

  if (lastPathSegment && lastPathSegment.end !== startNode || relationship !== null) {
    throw new _error.Neo4jError("Unable to parse path: ".concat(JSON.stringify(metadata)));
  }

  return createPath(pathSegments);
}

function createPath(pathSegments) {
  var pathStartNode = pathSegments[0].start;
  var pathEndNode = pathSegments[pathSegments.length - 1].end;
  return new _graphTypes.Path(pathStartNode, pathEndNode, pathSegments);
}

function convertPoint(element) {
  var type = element.type;

  if (type !== 'Point') {
    throw new _error.Neo4jError("Unexpected Point type received: ".concat(JSON.stringify(element)));
  }

  var coordinates = element.coordinates;

  if (!Array.isArray(coordinates) && (coordinates.length !== 2 || coordinates.length !== 3)) {
    throw new _error.Neo4jError("Unexpected Point coordinates received: ".concat(JSON.stringify(element)));
  }

  var srid = convertCrsToId(element);
  return (0, _construct2["default"])(_spatialTypes.Point, [srid].concat((0, _toConsumableArray2["default"])(coordinates)));
}

function convertCrsToId(element) {
  var crs = element.crs;

  if (!crs || !crs.name) {
    throw new _error.Neo4jError("Unexpected Point crs received: ".concat(JSON.stringify(element)));
  }

  var name = crs.name.toLowerCase();

  if (name === 'wgs-84') {
    return 4326;
  } else if (name === 'wgs-84-3d') {
    return 4979;
  } else if (name === 'cartesian') {
    return 7203;
  } else if (name === 'cartesian-3d') {
    return 9157;
  } else {
    throw new _error.Neo4jError("Unexpected Point crs received: ".concat(JSON.stringify(element)));
  }
}

function convertPrimitiveValue(element) {
  if (element == null || element === undefined) {
    return null;
  } else if (typeof element === 'number') {
    return convertNumber(element);
  } else if (Array.isArray(element)) {
    return element.map(function (element) {
      return convertPrimitiveValue(element);
    });
  } else if ((0, _typeof2["default"])(element) === 'object') {
    return Object.keys(element).reduce(function (result, key) {
      result[key] = convertPrimitiveValue(element[key]);
      return result;
    }, {});
  } else {
    return element;
  }
}

function convertNumber(value) {
  return typeof value === 'number' ? value : Number(value);
}

function isNodeMetadata(metadata) {
  return isMetadataForType('node', metadata);
}

function isRelationshipMetadata(metadata) {
  return isMetadataForType('relationship', metadata);
}

function isPointMetadata(metadata) {
  return isMetadataForType('point', metadata);
}

function isMetadataForType(name, metadata) {
  return !Array.isArray(metadata) && (0, _typeof2["default"])(metadata) === 'object' && metadata.type === name;
}

function isPathMetadata(metadata) {
  return Array.isArray(metadata);
}