"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _possibleConstructorReturn2 = _interopRequireDefault(require("@babel/runtime/helpers/possibleConstructorReturn"));

var _getPrototypeOf2 = _interopRequireDefault(require("@babel/runtime/helpers/getPrototypeOf"));

var _inherits2 = _interopRequireDefault(require("@babel/runtime/helpers/inherits"));

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _defineProperty2 = _interopRequireDefault(require("@babel/runtime/helpers/defineProperty"));

var _error = require("../error");

var _levels;

var ERROR = 'error';
var WARN = 'warn';
var INFO = 'info';
var DEBUG = 'debug';
var DEFAULT_LEVEL = INFO;
var levels = (_levels = {}, (0, _defineProperty2["default"])(_levels, ERROR, 0), (0, _defineProperty2["default"])(_levels, WARN, 1), (0, _defineProperty2["default"])(_levels, INFO, 2), (0, _defineProperty2["default"])(_levels, DEBUG, 3), _levels);
/**
 * Logger used by the driver to notify about various internal events. Single logger should be used per driver.
 */

var Logger =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {string} level the enabled logging level.
   * @param {function(level: string, message: string)} loggerFunction the function to write the log level and message.
   */
  function Logger(level, loggerFunction) {
    (0, _classCallCheck2["default"])(this, Logger);
    this._level = level;
    this._loggerFunction = loggerFunction;
  }
  /**
   * Create a new logger based on the given driver configuration.
   * @param {object} driverConfig the driver configuration as supplied by the user.
   * @return {Logger} a new logger instance or a no-op logger when not configured.
   */


  (0, _createClass2["default"])(Logger, [{
    key: "isErrorEnabled",

    /**
     * Check if error logging is enabled, i.e. it is not a no-op implementation.
     * @return {boolean} `true` when enabled, `false` otherwise.
     */
    value: function isErrorEnabled() {
      return isLevelEnabled(this._level, ERROR);
    }
    /**
     * Log an error message.
     * @param {string} message the message to log.
     */

  }, {
    key: "error",
    value: function error(message) {
      if (this.isErrorEnabled()) {
        this._loggerFunction(ERROR, message);
      }
    }
    /**
     * Check if warn logging is enabled, i.e. it is not a no-op implementation.
     * @return {boolean} `true` when enabled, `false` otherwise.
     */

  }, {
    key: "isWarnEnabled",
    value: function isWarnEnabled() {
      return isLevelEnabled(this._level, WARN);
    }
    /**
     * Log an warning message.
     * @param {string} message the message to log.
     */

  }, {
    key: "warn",
    value: function warn(message) {
      if (this.isWarnEnabled()) {
        this._loggerFunction(WARN, message);
      }
    }
    /**
     * Check if info logging is enabled, i.e. it is not a no-op implementation.
     * @return {boolean} `true` when enabled, `false` otherwise.
     */

  }, {
    key: "isInfoEnabled",
    value: function isInfoEnabled() {
      return isLevelEnabled(this._level, INFO);
    }
    /**
     * Log an info message.
     * @param {string} message the message to log.
     */

  }, {
    key: "info",
    value: function info(message) {
      if (this.isInfoEnabled()) {
        this._loggerFunction(INFO, message);
      }
    }
    /**
     * Check if debug logging is enabled, i.e. it is not a no-op implementation.
     * @return {boolean} `true` when enabled, `false` otherwise.
     */

  }, {
    key: "isDebugEnabled",
    value: function isDebugEnabled() {
      return isLevelEnabled(this._level, DEBUG);
    }
    /**
     * Log a debug message.
     * @param {string} message the message to log.
     */

  }, {
    key: "debug",
    value: function debug(message) {
      if (this.isDebugEnabled()) {
        this._loggerFunction(DEBUG, message);
      }
    }
  }], [{
    key: "create",
    value: function create(driverConfig) {
      if (driverConfig && driverConfig.logging) {
        var loggingConfig = driverConfig.logging;
        var level = extractConfiguredLevel(loggingConfig);
        var loggerFunction = extractConfiguredLogger(loggingConfig);
        return new Logger(level, loggerFunction);
      }

      return this.noOp();
    }
    /**
     * Create a no-op logger implementation.
     * @return {Logger} the no-op logger implementation.
     */

  }, {
    key: "noOp",
    value: function noOp() {
      return noOpLogger;
    }
  }]);
  return Logger;
}();

var NoOpLogger =
/*#__PURE__*/
function (_Logger) {
  (0, _inherits2["default"])(NoOpLogger, _Logger);

  function NoOpLogger() {
    (0, _classCallCheck2["default"])(this, NoOpLogger);
    return (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(NoOpLogger).call(this, null, null));
  }

  (0, _createClass2["default"])(NoOpLogger, [{
    key: "isErrorEnabled",
    value: function isErrorEnabled() {
      return false;
    }
  }, {
    key: "error",
    value: function error(message) {}
  }, {
    key: "isWarnEnabled",
    value: function isWarnEnabled() {
      return false;
    }
  }, {
    key: "warn",
    value: function warn(message) {}
  }, {
    key: "isInfoEnabled",
    value: function isInfoEnabled() {
      return false;
    }
  }, {
    key: "info",
    value: function info(message) {}
  }, {
    key: "isDebugEnabled",
    value: function isDebugEnabled() {
      return false;
    }
  }, {
    key: "debug",
    value: function debug(message) {}
  }]);
  return NoOpLogger;
}(Logger);

var noOpLogger = new NoOpLogger();
/**
 * Check if the given logging level is enabled.
 * @param {string} configuredLevel the configured level.
 * @param {string} targetLevel the level to check.
 * @return {boolean} value of `true` when enabled, `false` otherwise.
 */

function isLevelEnabled(configuredLevel, targetLevel) {
  return levels[configuredLevel] >= levels[targetLevel];
}
/**
 * Extract the configured logging level from the driver's logging configuration.
 * @param {object} loggingConfig the logging configuration.
 * @return {string} the configured log level or default when none configured.
 */


function extractConfiguredLevel(loggingConfig) {
  if (loggingConfig && loggingConfig.level) {
    var configuredLevel = loggingConfig.level;
    var value = levels[configuredLevel];

    if (!value && value !== 0) {
      throw (0, _error.newError)("Illegal logging level: ".concat(configuredLevel, ". Supported levels are: ").concat(Object.keys(levels)));
    }

    return configuredLevel;
  }

  return DEFAULT_LEVEL;
}
/**
 * Extract the configured logger function from the driver's logging configuration.
 * @param {object} loggingConfig the logging configuration.
 * @return {function(level: string, message: string)} the configured logging function.
 */


function extractConfiguredLogger(loggingConfig) {
  if (loggingConfig && loggingConfig.logger) {
    var configuredLogger = loggingConfig.logger;

    if (configuredLogger && typeof configuredLogger === 'function') {
      return configuredLogger;
    }
  }

  throw (0, _error.newError)("Illegal logger function: ".concat(loggingConfig.logger));
}

var _default = Logger;
exports["default"] = _default;