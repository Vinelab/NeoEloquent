"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _poolConfig = _interopRequireDefault(require("./pool-config"));

var _error = require("../error");

var _logger = _interopRequireDefault(require("./logger"));

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
var Pool =
/*#__PURE__*/
function () {
  /**
   * @param {function(function): Promise<object>} create  an allocation function that creates a promise with a new resource. It's given
   *                a single argument, a function that will return the resource to
   *                the pool if invoked, which is meant to be called on .dispose
   *                or .close or whatever mechanism the resource uses to finalize.
   * @param {function} destroy called with the resource when it is evicted from this pool
   * @param {function} validate called at various times (like when an instance is acquired and
   *                 when it is returned). If this returns false, the resource will
   *                 be evicted
   * @param {function} installIdleObserver called when the resource is released back to pool
   * @param {function} removeIdleObserver called when the resource is acquired from the pool
   * @param {PoolConfig} config configuration for the new driver.
   * @param {Logger} log the driver logger.
   */
  function Pool() {
    var _ref = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
        _ref$create = _ref.create,
        create = _ref$create === void 0 ? function (address, release) {} : _ref$create,
        _ref$destroy = _ref.destroy,
        destroy = _ref$destroy === void 0 ? function (conn) {
      return true;
    } : _ref$destroy,
        _ref$validate = _ref.validate,
        validate = _ref$validate === void 0 ? function (conn) {
      return true;
    } : _ref$validate,
        _ref$installIdleObser = _ref.installIdleObserver,
        installIdleObserver = _ref$installIdleObser === void 0 ? function (conn, observer) {} : _ref$installIdleObser,
        _ref$removeIdleObserv = _ref.removeIdleObserver,
        removeIdleObserver = _ref$removeIdleObserv === void 0 ? function (conn) {} : _ref$removeIdleObserv,
        _ref$config = _ref.config,
        config = _ref$config === void 0 ? _poolConfig["default"].defaultConfig() : _ref$config,
        _ref$log = _ref.log,
        log = _ref$log === void 0 ? _logger["default"].noOp() : _ref$log;

    (0, _classCallCheck2["default"])(this, Pool);
    this._create = create;
    this._destroy = destroy;
    this._validate = validate;
    this._installIdleObserver = installIdleObserver;
    this._removeIdleObserver = removeIdleObserver;
    this._maxSize = config.maxSize;
    this._acquisitionTimeout = config.acquisitionTimeout;
    this._pools = {};
    this._acquireRequests = {};
    this._activeResourceCounts = {};
    this._release = this._release.bind(this);
    this._log = log;
  }
  /**
   * Acquire and idle resource fom the pool or create a new one.
   * @param {ServerAddress} address the address for which we're acquiring.
   * @return {object} resource that is ready to use.
   */


  (0, _createClass2["default"])(Pool, [{
    key: "acquire",
    value: function acquire(address) {
      var _this = this;

      return this._acquire(address).then(function (resource) {
        var key = address.asKey();

        if (resource) {
          resourceAcquired(key, _this._activeResourceCounts);

          if (_this._log.isDebugEnabled()) {
            _this._log.debug("".concat(resource, " acquired from the pool ").concat(key));
          }

          return resource;
        } // We're out of resources and will try to acquire later on when an existing resource is released.


        var allRequests = _this._acquireRequests;
        var requests = allRequests[key];

        if (!requests) {
          allRequests[key] = [];
        }

        return new Promise(function (resolve, reject) {
          var request;
          var timeoutId = setTimeout(function () {
            // acquisition timeout fired
            // remove request from the queue of pending requests, if it's still there
            // request might've been taken out by the release operation
            var pendingRequests = allRequests[key];

            if (pendingRequests) {
              allRequests[key] = pendingRequests.filter(function (item) {
                return item !== request;
              });
            }

            if (request.isCompleted()) {// request already resolved/rejected by the release operation; nothing to do
            } else {
              // request is still pending and needs to be failed
              request.reject((0, _error.newError)("Connection acquisition timed out in ".concat(_this._acquisitionTimeout, " ms.")));
            }
          }, _this._acquisitionTimeout);
          request = new PendingRequest(key, resolve, reject, timeoutId, _this._log);
          allRequests[key].push(request);
        });
      });
    }
    /**
     * Destroy all idle resources for the given address.
     * @param {ServerAddress} address the address of the server to purge its pool.
     */

  }, {
    key: "purge",
    value: function purge(address) {
      this._purgeKey(address.asKey());
    }
    /**
     * Destroy all idle resources in this pool.
     */

  }, {
    key: "purgeAll",
    value: function purgeAll() {
      var _this2 = this;

      Object.keys(this._pools).forEach(function (key) {
        return _this2._purgeKey(key);
      });
    }
    /**
     * Keep the idle resources for the provided addresses and purge the rest.
     */

  }, {
    key: "keepAll",
    value: function keepAll(addresses) {
      var _this3 = this;

      var keysToKeep = addresses.map(function (a) {
        return a.asKey();
      });
      var keysPresent = Object.keys(this._pools);
      var keysToPurge = keysPresent.filter(function (k) {
        return keysToKeep.indexOf(k) === -1;
      });
      keysToPurge.forEach(function (key) {
        return _this3._purgeKey(key);
      });
    }
    /**
     * Check if this pool contains resources for the given address.
     * @param {ServerAddress} address the address of the server to check.
     * @return {boolean} `true` when pool contains entries for the given key, <code>false</code> otherwise.
     */

  }, {
    key: "has",
    value: function has(address) {
      return address.asKey() in this._pools;
    }
    /**
     * Get count of active (checked out of the pool) resources for the given key.
     * @param {ServerAddress} address the address of the server to check.
     * @return {number} count of resources acquired by clients.
     */

  }, {
    key: "activeResourceCount",
    value: function activeResourceCount(address) {
      return this._activeResourceCounts[address.asKey()] || 0;
    }
  }, {
    key: "_acquire",
    value: function _acquire(address) {
      var key = address.asKey();
      var pool = this._pools[key];

      if (!pool) {
        pool = [];
        this._pools[key] = pool;
      }

      while (pool.length) {
        var resource = pool.pop();

        if (this._validate(resource)) {
          if (this._removeIdleObserver) {
            this._removeIdleObserver(resource);
          } // idle resource is valid and can be acquired


          return Promise.resolve(resource);
        } else {
          this._destroy(resource);
        }
      }

      if (this._maxSize && this.activeResourceCount(address) >= this._maxSize) {
        return Promise.resolve(null);
      } // there exist no idle valid resources, create a new one for acquisition


      return this._create(address, this._release);
    }
  }, {
    key: "_release",
    value: function _release(address, resource) {
      var _this4 = this;

      var key = address.asKey();
      var pool = this._pools[key];

      if (pool) {
        // there exist idle connections for the given key
        if (!this._validate(resource)) {
          if (this._log.isDebugEnabled()) {
            this._log.debug("".concat(resource, " destroyed and can't be released to the pool ").concat(key, " because it is not functional"));
          }

          this._destroy(resource);
        } else {
          if (this._log.isDebugEnabled()) {
            this._log.debug("".concat(resource, " released to the pool ").concat(key));
          }

          if (this._installIdleObserver) {
            this._installIdleObserver(resource, {
              onError: function onError() {
                var pool = _this4._pools[key];

                if (pool) {
                  _this4._pools[key] = pool.filter(function (r) {
                    return r !== resource;
                  });
                }

                _this4._destroy(resource);
              }
            });
          }

          pool.push(resource);
        }
      } else {
        // key has been purged, don't put it back, just destroy the resource
        if (this._log.isDebugEnabled()) {
          this._log.debug("".concat(resource, " destroyed and can't be released to the pool ").concat(key, " because pool has been purged"));
        }

        this._destroy(resource);
      }

      resourceReleased(key, this._activeResourceCounts);

      this._processPendingAcquireRequests(address);
    }
  }, {
    key: "_purgeKey",
    value: function _purgeKey(key) {
      var pool = this._pools[key] || [];

      while (pool.length) {
        var resource = pool.pop();

        if (this._removeIdleObserver) {
          this._removeIdleObserver(resource);
        }

        this._destroy(resource);
      }

      delete this._pools[key];
    }
  }, {
    key: "_processPendingAcquireRequests",
    value: function _processPendingAcquireRequests(address) {
      var _this5 = this;

      var key = address.asKey();
      var requests = this._acquireRequests[key];

      if (requests) {
        var pendingRequest = requests.shift(); // pop a pending acquire request

        if (pendingRequest) {
          this._acquire(address)["catch"](function (error) {
            // failed to acquire/create a new connection to resolve the pending acquire request
            // propagate the error by failing the pending request
            pendingRequest.reject(error);
            return null;
          }).then(function (resource) {
            if (resource) {
              // managed to acquire a valid resource from the pool
              if (pendingRequest.isCompleted()) {
                // request has been completed, most likely failed by a timeout
                // return the acquired resource back to the pool
                _this5._release(address, resource);
              } else {
                // request is still pending and can be resolved with the newly acquired resource
                resourceAcquired(key, _this5._activeResourceCounts); // increment the active counter

                pendingRequest.resolve(resource); // resolve the pending request with the acquired resource
              }
            }
          });
        } else {
          delete this._acquireRequests[key];
        }
      }
    }
  }]);
  return Pool;
}();
/**
 * Increment active (checked out of the pool) resource counter.
 * @param {string} key the resource group identifier (server address for connections).
 * @param {Object.<string, number>} activeResourceCounts the object holding active counts per key.
 */


function resourceAcquired(key, activeResourceCounts) {
  var currentCount = activeResourceCounts[key] || 0;
  activeResourceCounts[key] = currentCount + 1;
}
/**
 * Decrement active (checked out of the pool) resource counter.
 * @param {string} key the resource group identifier (server address for connections).
 * @param {Object.<string, number>} activeResourceCounts the object holding active counts per key.
 */


function resourceReleased(key, activeResourceCounts) {
  var currentCount = activeResourceCounts[key] || 0;
  var nextCount = currentCount - 1;

  if (nextCount > 0) {
    activeResourceCounts[key] = nextCount;
  } else {
    delete activeResourceCounts[key];
  }
}

var PendingRequest =
/*#__PURE__*/
function () {
  function PendingRequest(key, resolve, reject, timeoutId, log) {
    (0, _classCallCheck2["default"])(this, PendingRequest);
    this._key = key;
    this._resolve = resolve;
    this._reject = reject;
    this._timeoutId = timeoutId;
    this._log = log;
    this._completed = false;
  }

  (0, _createClass2["default"])(PendingRequest, [{
    key: "isCompleted",
    value: function isCompleted() {
      return this._completed;
    }
  }, {
    key: "resolve",
    value: function resolve(resource) {
      if (this._completed) {
        return;
      }

      this._completed = true;
      clearTimeout(this._timeoutId);

      if (this._log.isDebugEnabled()) {
        this._log.debug("".concat(resource, " acquired from the pool ").concat(this._key));
      }

      this._resolve(resource);
    }
  }, {
    key: "reject",
    value: function reject(error) {
      if (this._completed) {
        return;
      }

      this._completed = true;
      clearTimeout(this._timeoutId);

      this._reject(error);
    }
  }]);
  return PendingRequest;
}();

var _default = Pool;
exports["default"] = _default;