(function(f){if(typeof exports==="object"&&typeof module!=="undefined"){module.exports=f()}else if(typeof define==="function"&&define.amd){define([],f)}else{var g;if(typeof window!=="undefined"){g=window}else if(typeof global!=="undefined"){g=global}else if(typeof self!=="undefined"){g=self}else{g=this}g.neo4j = f()}})(function(){var define,module,exports;return (function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
function _arrayWithHoles(arr) {
  if (Array.isArray(arr)) return arr;
}

module.exports = _arrayWithHoles;
},{}],2:[function(require,module,exports){
function _arrayWithoutHoles(arr) {
  if (Array.isArray(arr)) {
    for (var i = 0, arr2 = new Array(arr.length); i < arr.length; i++) {
      arr2[i] = arr[i];
    }

    return arr2;
  }
}

module.exports = _arrayWithoutHoles;
},{}],3:[function(require,module,exports){
function _assertThisInitialized(self) {
  if (self === void 0) {
    throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
  }

  return self;
}

module.exports = _assertThisInitialized;
},{}],4:[function(require,module,exports){
function _classCallCheck(instance, Constructor) {
  if (!(instance instanceof Constructor)) {
    throw new TypeError("Cannot call a class as a function");
  }
}

module.exports = _classCallCheck;
},{}],5:[function(require,module,exports){
var setPrototypeOf = require("./setPrototypeOf");

function isNativeReflectConstruct() {
  if (typeof Reflect === "undefined" || !Reflect.construct) return false;
  if (Reflect.construct.sham) return false;
  if (typeof Proxy === "function") return true;

  try {
    Date.prototype.toString.call(Reflect.construct(Date, [], function () {}));
    return true;
  } catch (e) {
    return false;
  }
}

function _construct(Parent, args, Class) {
  if (isNativeReflectConstruct()) {
    module.exports = _construct = Reflect.construct;
  } else {
    module.exports = _construct = function _construct(Parent, args, Class) {
      var a = [null];
      a.push.apply(a, args);
      var Constructor = Function.bind.apply(Parent, a);
      var instance = new Constructor();
      if (Class) setPrototypeOf(instance, Class.prototype);
      return instance;
    };
  }

  return _construct.apply(null, arguments);
}

module.exports = _construct;
},{"./setPrototypeOf":19}],6:[function(require,module,exports){
function _defineProperties(target, props) {
  for (var i = 0; i < props.length; i++) {
    var descriptor = props[i];
    descriptor.enumerable = descriptor.enumerable || false;
    descriptor.configurable = true;
    if ("value" in descriptor) descriptor.writable = true;
    Object.defineProperty(target, descriptor.key, descriptor);
  }
}

function _createClass(Constructor, protoProps, staticProps) {
  if (protoProps) _defineProperties(Constructor.prototype, protoProps);
  if (staticProps) _defineProperties(Constructor, staticProps);
  return Constructor;
}

module.exports = _createClass;
},{}],7:[function(require,module,exports){
function _defineProperty(obj, key, value) {
  if (key in obj) {
    Object.defineProperty(obj, key, {
      value: value,
      enumerable: true,
      configurable: true,
      writable: true
    });
  } else {
    obj[key] = value;
  }

  return obj;
}

module.exports = _defineProperty;
},{}],8:[function(require,module,exports){
var getPrototypeOf = require("./getPrototypeOf");

var superPropBase = require("./superPropBase");

function _get(target, property, receiver) {
  if (typeof Reflect !== "undefined" && Reflect.get) {
    module.exports = _get = Reflect.get;
  } else {
    module.exports = _get = function _get(target, property, receiver) {
      var base = superPropBase(target, property);
      if (!base) return;
      var desc = Object.getOwnPropertyDescriptor(base, property);

      if (desc.get) {
        return desc.get.call(receiver);
      }

      return desc.value;
    };
  }

  return _get(target, property, receiver || target);
}

module.exports = _get;
},{"./getPrototypeOf":9,"./superPropBase":21}],9:[function(require,module,exports){
function _getPrototypeOf(o) {
  module.exports = _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) {
    return o.__proto__ || Object.getPrototypeOf(o);
  };
  return _getPrototypeOf(o);
}

module.exports = _getPrototypeOf;
},{}],10:[function(require,module,exports){
var setPrototypeOf = require("./setPrototypeOf");

function _inherits(subClass, superClass) {
  if (typeof superClass !== "function" && superClass !== null) {
    throw new TypeError("Super expression must either be null or a function");
  }

  subClass.prototype = Object.create(superClass && superClass.prototype, {
    constructor: {
      value: subClass,
      writable: true,
      configurable: true
    }
  });
  if (superClass) setPrototypeOf(subClass, superClass);
}

module.exports = _inherits;
},{"./setPrototypeOf":19}],11:[function(require,module,exports){
function _interopRequireDefault(obj) {
  return obj && obj.__esModule ? obj : {
    "default": obj
  };
}

module.exports = _interopRequireDefault;
},{}],12:[function(require,module,exports){
function _interopRequireWildcard(obj) {
  if (obj && obj.__esModule) {
    return obj;
  } else {
    var newObj = {};

    if (obj != null) {
      for (var key in obj) {
        if (Object.prototype.hasOwnProperty.call(obj, key)) {
          var desc = Object.defineProperty && Object.getOwnPropertyDescriptor ? Object.getOwnPropertyDescriptor(obj, key) : {};

          if (desc.get || desc.set) {
            Object.defineProperty(newObj, key, desc);
          } else {
            newObj[key] = obj[key];
          }
        }
      }
    }

    newObj["default"] = obj;
    return newObj;
  }
}

module.exports = _interopRequireWildcard;
},{}],13:[function(require,module,exports){
function _isNativeFunction(fn) {
  return Function.toString.call(fn).indexOf("[native code]") !== -1;
}

module.exports = _isNativeFunction;
},{}],14:[function(require,module,exports){
function _iterableToArray(iter) {
  if (Symbol.iterator in Object(iter) || Object.prototype.toString.call(iter) === "[object Arguments]") return Array.from(iter);
}

module.exports = _iterableToArray;
},{}],15:[function(require,module,exports){
function _iterableToArrayLimit(arr, i) {
  var _arr = [];
  var _n = true;
  var _d = false;
  var _e = undefined;

  try {
    for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) {
      _arr.push(_s.value);

      if (i && _arr.length === i) break;
    }
  } catch (err) {
    _d = true;
    _e = err;
  } finally {
    try {
      if (!_n && _i["return"] != null) _i["return"]();
    } finally {
      if (_d) throw _e;
    }
  }

  return _arr;
}

module.exports = _iterableToArrayLimit;
},{}],16:[function(require,module,exports){
function _nonIterableRest() {
  throw new TypeError("Invalid attempt to destructure non-iterable instance");
}

module.exports = _nonIterableRest;
},{}],17:[function(require,module,exports){
function _nonIterableSpread() {
  throw new TypeError("Invalid attempt to spread non-iterable instance");
}

module.exports = _nonIterableSpread;
},{}],18:[function(require,module,exports){
var _typeof = require("../helpers/typeof");

var assertThisInitialized = require("./assertThisInitialized");

function _possibleConstructorReturn(self, call) {
  if (call && (_typeof(call) === "object" || typeof call === "function")) {
    return call;
  }

  return assertThisInitialized(self);
}

module.exports = _possibleConstructorReturn;
},{"../helpers/typeof":23,"./assertThisInitialized":3}],19:[function(require,module,exports){
function _setPrototypeOf(o, p) {
  module.exports = _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) {
    o.__proto__ = p;
    return o;
  };

  return _setPrototypeOf(o, p);
}

module.exports = _setPrototypeOf;
},{}],20:[function(require,module,exports){
var arrayWithHoles = require("./arrayWithHoles");

var iterableToArrayLimit = require("./iterableToArrayLimit");

var nonIterableRest = require("./nonIterableRest");

function _slicedToArray(arr, i) {
  return arrayWithHoles(arr) || iterableToArrayLimit(arr, i) || nonIterableRest();
}

module.exports = _slicedToArray;
},{"./arrayWithHoles":1,"./iterableToArrayLimit":15,"./nonIterableRest":16}],21:[function(require,module,exports){
var getPrototypeOf = require("./getPrototypeOf");

function _superPropBase(object, property) {
  while (!Object.prototype.hasOwnProperty.call(object, property)) {
    object = getPrototypeOf(object);
    if (object === null) break;
  }

  return object;
}

module.exports = _superPropBase;
},{"./getPrototypeOf":9}],22:[function(require,module,exports){
var arrayWithoutHoles = require("./arrayWithoutHoles");

var iterableToArray = require("./iterableToArray");

var nonIterableSpread = require("./nonIterableSpread");

function _toConsumableArray(arr) {
  return arrayWithoutHoles(arr) || iterableToArray(arr) || nonIterableSpread();
}

module.exports = _toConsumableArray;
},{"./arrayWithoutHoles":2,"./iterableToArray":14,"./nonIterableSpread":17}],23:[function(require,module,exports){
function _typeof2(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof2 = function _typeof2(obj) { return typeof obj; }; } else { _typeof2 = function _typeof2(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof2(obj); }

function _typeof(obj) {
  if (typeof Symbol === "function" && _typeof2(Symbol.iterator) === "symbol") {
    module.exports = _typeof = function _typeof(obj) {
      return _typeof2(obj);
    };
  } else {
    module.exports = _typeof = function _typeof(obj) {
      return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : _typeof2(obj);
    };
  }

  return _typeof(obj);
}

module.exports = _typeof;
},{}],24:[function(require,module,exports){
var getPrototypeOf = require("./getPrototypeOf");

var setPrototypeOf = require("./setPrototypeOf");

var isNativeFunction = require("./isNativeFunction");

var construct = require("./construct");

function _wrapNativeSuper(Class) {
  var _cache = typeof Map === "function" ? new Map() : undefined;

  module.exports = _wrapNativeSuper = function _wrapNativeSuper(Class) {
    if (Class === null || !isNativeFunction(Class)) return Class;

    if (typeof Class !== "function") {
      throw new TypeError("Super expression must either be null or a function");
    }

    if (typeof _cache !== "undefined") {
      if (_cache.has(Class)) return _cache.get(Class);

      _cache.set(Class, Wrapper);
    }

    function Wrapper() {
      return construct(Class, arguments, getPrototypeOf(this).constructor);
    }

    Wrapper.prototype = Object.create(Class.prototype, {
      constructor: {
        value: Wrapper,
        enumerable: false,
        writable: true,
        configurable: true
      }
    });
    return setPrototypeOf(Wrapper, Class);
  };

  return _wrapNativeSuper(Class);
}

module.exports = _wrapNativeSuper;
},{"./construct":5,"./getPrototypeOf":9,"./isNativeFunction":13,"./setPrototypeOf":19}],25:[function(require,module,exports){
'use strict';

// This is free and unencumbered software released into the public domain.
// See LICENSE.md for more information.

//
// Utilities
//

/**
 * @param {number} a The number to test.
 * @param {number} min The minimum value in the range, inclusive.
 * @param {number} max The maximum value in the range, inclusive.
 * @return {boolean} True if a >= min and a <= max.
 */
function inRange(a, min, max) {
  return min <= a && a <= max;
}

/**
 * @param {*} o
 * @return {Object}
 */
function ToDictionary(o) {
  if (o === undefined) return {};
  if (o === Object(o)) return o;
  throw TypeError('Could not convert argument to dictionary');
}

/**
 * @param {string} string Input string of UTF-16 code units.
 * @return {!Array.<number>} Code points.
 */
function stringToCodePoints(string) {
  // https://heycam.github.io/webidl/#dfn-obtain-unicode

  // 1. Let S be the DOMString value.
  var s = String(string);

  // 2. Let n be the length of S.
  var n = s.length;

  // 3. Initialize i to 0.
  var i = 0;

  // 4. Initialize U to be an empty sequence of Unicode characters.
  var u = [];

  // 5. While i < n:
  while (i < n) {

    // 1. Let c be the code unit in S at index i.
    var c = s.charCodeAt(i);

    // 2. Depending on the value of c:

    // c < 0xD800 or c > 0xDFFF
    if (c < 0xD800 || c > 0xDFFF) {
      // Append to U the Unicode character with code point c.
      u.push(c);
    }

    // 0xDC00 ≤ c ≤ 0xDFFF
    else if (0xDC00 <= c && c <= 0xDFFF) {
      // Append to U a U+FFFD REPLACEMENT CHARACTER.
      u.push(0xFFFD);
    }

    // 0xD800 ≤ c ≤ 0xDBFF
    else if (0xD800 <= c && c <= 0xDBFF) {
      // 1. If i = n−1, then append to U a U+FFFD REPLACEMENT
      // CHARACTER.
      if (i === n - 1) {
        u.push(0xFFFD);
      }
      // 2. Otherwise, i < n−1:
      else {
        // 1. Let d be the code unit in S at index i+1.
        var d = string.charCodeAt(i + 1);

        // 2. If 0xDC00 ≤ d ≤ 0xDFFF, then:
        if (0xDC00 <= d && d <= 0xDFFF) {
          // 1. Let a be c & 0x3FF.
          var a = c & 0x3FF;

          // 2. Let b be d & 0x3FF.
          var b = d & 0x3FF;

          // 3. Append to U the Unicode character with code point
          // 2^16+2^10*a+b.
          u.push(0x10000 + (a << 10) + b);

          // 4. Set i to i+1.
          i += 1;
        }

        // 3. Otherwise, d < 0xDC00 or d > 0xDFFF. Append to U a
        // U+FFFD REPLACEMENT CHARACTER.
        else  {
          u.push(0xFFFD);
        }
      }
    }

    // 3. Set i to i+1.
    i += 1;
  }

  // 6. Return U.
  return u;
}

/**
 * @param {!Array.<number>} code_points Array of code points.
 * @return {string} string String of UTF-16 code units.
 */
function codePointsToString(code_points) {
  var s = '';
  for (var i = 0; i < code_points.length; ++i) {
    var cp = code_points[i];
    if (cp <= 0xFFFF) {
      s += String.fromCharCode(cp);
    } else {
      cp -= 0x10000;
      s += String.fromCharCode((cp >> 10) + 0xD800,
                               (cp & 0x3FF) + 0xDC00);
    }
  }
  return s;
}


//
// Implementation of Encoding specification
// https://encoding.spec.whatwg.org/
//

//
// 3. Terminology
//

/**
 * End-of-stream is a special token that signifies no more tokens
 * are in the stream.
 * @const
 */ var end_of_stream = -1;

/**
 * A stream represents an ordered sequence of tokens.
 *
 * @constructor
 * @param {!(Array.<number>|Uint8Array)} tokens Array of tokens that provide the
 * stream.
 */
function Stream(tokens) {
  /** @type {!Array.<number>} */
  this.tokens = [].slice.call(tokens);
}

Stream.prototype = {
  /**
   * @return {boolean} True if end-of-stream has been hit.
   */
  endOfStream: function() {
    return !this.tokens.length;
  },

  /**
   * When a token is read from a stream, the first token in the
   * stream must be returned and subsequently removed, and
   * end-of-stream must be returned otherwise.
   *
   * @return {number} Get the next token from the stream, or
   * end_of_stream.
   */
   read: function() {
    if (!this.tokens.length)
      return end_of_stream;
     return this.tokens.shift();
   },

  /**
   * When one or more tokens are prepended to a stream, those tokens
   * must be inserted, in given order, before the first token in the
   * stream.
   *
   * @param {(number|!Array.<number>)} token The token(s) to prepend to the stream.
   */
  prepend: function(token) {
    if (Array.isArray(token)) {
      var tokens = /**@type {!Array.<number>}*/(token);
      while (tokens.length)
        this.tokens.unshift(tokens.pop());
    } else {
      this.tokens.unshift(token);
    }
  },

  /**
   * When one or more tokens are pushed to a stream, those tokens
   * must be inserted, in given order, after the last token in the
   * stream.
   *
   * @param {(number|!Array.<number>)} token The tokens(s) to prepend to the stream.
   */
  push: function(token) {
    if (Array.isArray(token)) {
      var tokens = /**@type {!Array.<number>}*/(token);
      while (tokens.length)
        this.tokens.push(tokens.shift());
    } else {
      this.tokens.push(token);
    }
  }
};

//
// 4. Encodings
//

// 4.1 Encoders and decoders

/** @const */
var finished = -1;

/**
 * @param {boolean} fatal If true, decoding errors raise an exception.
 * @param {number=} opt_code_point Override the standard fallback code point.
 * @return {number} The code point to insert on a decoding error.
 */
function decoderError(fatal, opt_code_point) {
  if (fatal)
    throw TypeError('Decoder error');
  return opt_code_point || 0xFFFD;
}

//
// 7. API
//

/** @const */ var DEFAULT_ENCODING = 'utf-8';

// 7.1 Interface TextDecoder

/**
 * @constructor
 * @param {string=} encoding The label of the encoding;
 *     defaults to 'utf-8'.
 * @param {Object=} options
 */
function TextDecoder(encoding, options) {
  if (!(this instanceof TextDecoder)) {
    return new TextDecoder(encoding, options);
  }
  encoding = encoding !== undefined ? String(encoding).toLowerCase() : DEFAULT_ENCODING;
  if (encoding !== DEFAULT_ENCODING) {
    throw new Error('Encoding not supported. Only utf-8 is supported');
  }
  options = ToDictionary(options);

  /** @private @type {boolean} */
  this._streaming = false;
  /** @private @type {boolean} */
  this._BOMseen = false;
  /** @private @type {?Decoder} */
  this._decoder = null;
  /** @private @type {boolean} */
  this._fatal = Boolean(options['fatal']);
  /** @private @type {boolean} */
  this._ignoreBOM = Boolean(options['ignoreBOM']);

  Object.defineProperty(this, 'encoding', {value: 'utf-8'});
  Object.defineProperty(this, 'fatal', {value: this._fatal});
  Object.defineProperty(this, 'ignoreBOM', {value: this._ignoreBOM});
}

TextDecoder.prototype = {
  /**
   * @param {ArrayBufferView=} input The buffer of bytes to decode.
   * @param {Object=} options
   * @return {string} The decoded string.
   */
  decode: function decode(input, options) {
    var bytes;
    if (typeof input === 'object' && input instanceof ArrayBuffer) {
      bytes = new Uint8Array(input);
    } else if (typeof input === 'object' && 'buffer' in input &&
               input.buffer instanceof ArrayBuffer) {
      bytes = new Uint8Array(input.buffer,
                             input.byteOffset,
                             input.byteLength);
    } else {
      bytes = new Uint8Array(0);
    }

    options = ToDictionary(options);

    if (!this._streaming) {
      this._decoder = new UTF8Decoder({fatal: this._fatal});
      this._BOMseen = false;
    }
    this._streaming = Boolean(options['stream']);

    var input_stream = new Stream(bytes);

    var code_points = [];

    /** @type {?(number|!Array.<number>)} */
    var result;

    while (!input_stream.endOfStream()) {
      result = this._decoder.handler(input_stream, input_stream.read());
      if (result === finished)
        break;
      if (result === null)
        continue;
      if (Array.isArray(result))
        code_points.push.apply(code_points, /**@type {!Array.<number>}*/(result));
      else
        code_points.push(result);
    }
    if (!this._streaming) {
      do {
        result = this._decoder.handler(input_stream, input_stream.read());
        if (result === finished)
          break;
        if (result === null)
          continue;
        if (Array.isArray(result))
          code_points.push.apply(code_points, /**@type {!Array.<number>}*/(result));
        else
          code_points.push(result);
      } while (!input_stream.endOfStream());
      this._decoder = null;
    }

    if (code_points.length) {
      // If encoding is one of utf-8, utf-16be, and utf-16le, and
      // ignore BOM flag and BOM seen flag are unset, run these
      // subsubsteps:
      if (['utf-8'].indexOf(this.encoding) !== -1 &&
          !this._ignoreBOM && !this._BOMseen) {
        // If token is U+FEFF, set BOM seen flag.
        if (code_points[0] === 0xFEFF) {
          this._BOMseen = true;
          code_points.shift();
        } else {
          // Otherwise, if token is not end-of-stream, set BOM seen
          // flag and append token to output.
          this._BOMseen = true;
        }
      }
    }

    return codePointsToString(code_points);
  }
};

// 7.2 Interface TextEncoder

/**
 * @constructor
 * @param {string=} encoding The label of the encoding;
 *     defaults to 'utf-8'.
 * @param {Object=} options
 */
function TextEncoder(encoding, options) {
  if (!(this instanceof TextEncoder))
    return new TextEncoder(encoding, options);
  encoding = encoding !== undefined ? String(encoding).toLowerCase() : DEFAULT_ENCODING;
  if (encoding !== DEFAULT_ENCODING) {
    throw new Error('Encoding not supported. Only utf-8 is supported');
  }
  options = ToDictionary(options);

  /** @private @type {boolean} */
  this._streaming = false;
  /** @private @type {?Encoder} */
  this._encoder = null;
  /** @private @type {{fatal: boolean}} */
  this._options = {fatal: Boolean(options['fatal'])};

  Object.defineProperty(this, 'encoding', {value: 'utf-8'});
}

TextEncoder.prototype = {
  /**
   * @param {string=} opt_string The string to encode.
   * @param {Object=} options
   * @return {Uint8Array} Encoded bytes, as a Uint8Array.
   */
  encode: function encode(opt_string, options) {
    opt_string = opt_string ? String(opt_string) : '';
    options = ToDictionary(options);

    // NOTE: This option is nonstandard. None of the encodings
    // permitted for encoding (i.e. UTF-8, UTF-16) are stateful,
    // so streaming is not necessary.
    if (!this._streaming)
      this._encoder = new UTF8Encoder(this._options);
    this._streaming = Boolean(options['stream']);

    var bytes = [];
    var input_stream = new Stream(stringToCodePoints(opt_string));
    /** @type {?(number|!Array.<number>)} */
    var result;
    while (!input_stream.endOfStream()) {
      result = this._encoder.handler(input_stream, input_stream.read());
      if (result === finished)
        break;
      if (Array.isArray(result))
        bytes.push.apply(bytes, /**@type {!Array.<number>}*/(result));
      else
        bytes.push(result);
    }
    if (!this._streaming) {
      while (true) {
        result = this._encoder.handler(input_stream, input_stream.read());
        if (result === finished)
          break;
        if (Array.isArray(result))
          bytes.push.apply(bytes, /**@type {!Array.<number>}*/(result));
        else
          bytes.push(result);
      }
      this._encoder = null;
    }
    return new Uint8Array(bytes);
  }
};

//
// 8. The encoding
//

// 8.1 utf-8

/**
 * @constructor
 * @implements {Decoder}
 * @param {{fatal: boolean}} options
 */
function UTF8Decoder(options) {
  var fatal = options.fatal;

  // utf-8's decoder's has an associated utf-8 code point, utf-8
  // bytes seen, and utf-8 bytes needed (all initially 0), a utf-8
  // lower boundary (initially 0x80), and a utf-8 upper boundary
  // (initially 0xBF).
  var /** @type {number} */ utf8_code_point = 0,
      /** @type {number} */ utf8_bytes_seen = 0,
      /** @type {number} */ utf8_bytes_needed = 0,
      /** @type {number} */ utf8_lower_boundary = 0x80,
      /** @type {number} */ utf8_upper_boundary = 0xBF;

  /**
   * @param {Stream} stream The stream of bytes being decoded.
   * @param {number} bite The next byte read from the stream.
   * @return {?(number|!Array.<number>)} The next code point(s)
   *     decoded, or null if not enough data exists in the input
   *     stream to decode a complete code point.
   */
  this.handler = function(stream, bite) {
    // 1. If byte is end-of-stream and utf-8 bytes needed is not 0,
    // set utf-8 bytes needed to 0 and return error.
    if (bite === end_of_stream && utf8_bytes_needed !== 0) {
      utf8_bytes_needed = 0;
      return decoderError(fatal);
    }

    // 2. If byte is end-of-stream, return finished.
    if (bite === end_of_stream)
      return finished;

    // 3. If utf-8 bytes needed is 0, based on byte:
    if (utf8_bytes_needed === 0) {

      // 0x00 to 0x7F
      if (inRange(bite, 0x00, 0x7F)) {
        // Return a code point whose value is byte.
        return bite;
      }

      // 0xC2 to 0xDF
      if (inRange(bite, 0xC2, 0xDF)) {
        // Set utf-8 bytes needed to 1 and utf-8 code point to byte
        // − 0xC0.
        utf8_bytes_needed = 1;
        utf8_code_point = bite - 0xC0;
      }

      // 0xE0 to 0xEF
      else if (inRange(bite, 0xE0, 0xEF)) {
        // 1. If byte is 0xE0, set utf-8 lower boundary to 0xA0.
        if (bite === 0xE0)
          utf8_lower_boundary = 0xA0;
        // 2. If byte is 0xED, set utf-8 upper boundary to 0x9F.
        if (bite === 0xED)
          utf8_upper_boundary = 0x9F;
        // 3. Set utf-8 bytes needed to 2 and utf-8 code point to
        // byte − 0xE0.
        utf8_bytes_needed = 2;
        utf8_code_point = bite - 0xE0;
      }

      // 0xF0 to 0xF4
      else if (inRange(bite, 0xF0, 0xF4)) {
        // 1. If byte is 0xF0, set utf-8 lower boundary to 0x90.
        if (bite === 0xF0)
          utf8_lower_boundary = 0x90;
        // 2. If byte is 0xF4, set utf-8 upper boundary to 0x8F.
        if (bite === 0xF4)
          utf8_upper_boundary = 0x8F;
        // 3. Set utf-8 bytes needed to 3 and utf-8 code point to
        // byte − 0xF0.
        utf8_bytes_needed = 3;
        utf8_code_point = bite - 0xF0;
      }

      // Otherwise
      else {
        // Return error.
        return decoderError(fatal);
      }

      // Then (byte is in the range 0xC2 to 0xF4) set utf-8 code
      // point to utf-8 code point << (6 × utf-8 bytes needed) and
      // return continue.
      utf8_code_point = utf8_code_point << (6 * utf8_bytes_needed);
      return null;
    }

    // 4. If byte is not in the range utf-8 lower boundary to utf-8
    // upper boundary, run these substeps:
    if (!inRange(bite, utf8_lower_boundary, utf8_upper_boundary)) {

      // 1. Set utf-8 code point, utf-8 bytes needed, and utf-8
      // bytes seen to 0, set utf-8 lower boundary to 0x80, and set
      // utf-8 upper boundary to 0xBF.
      utf8_code_point = utf8_bytes_needed = utf8_bytes_seen = 0;
      utf8_lower_boundary = 0x80;
      utf8_upper_boundary = 0xBF;

      // 2. Prepend byte to stream.
      stream.prepend(bite);

      // 3. Return error.
      return decoderError(fatal);
    }

    // 5. Set utf-8 lower boundary to 0x80 and utf-8 upper boundary
    // to 0xBF.
    utf8_lower_boundary = 0x80;
    utf8_upper_boundary = 0xBF;

    // 6. Increase utf-8 bytes seen by one and set utf-8 code point
    // to utf-8 code point + (byte − 0x80) << (6 × (utf-8 bytes
    // needed − utf-8 bytes seen)).
    utf8_bytes_seen += 1;
    utf8_code_point += (bite - 0x80) << (6 * (utf8_bytes_needed - utf8_bytes_seen));

    // 7. If utf-8 bytes seen is not equal to utf-8 bytes needed,
    // continue.
    if (utf8_bytes_seen !== utf8_bytes_needed)
      return null;

    // 8. Let code point be utf-8 code point.
    var code_point = utf8_code_point;

    // 9. Set utf-8 code point, utf-8 bytes needed, and utf-8 bytes
    // seen to 0.
    utf8_code_point = utf8_bytes_needed = utf8_bytes_seen = 0;

    // 10. Return a code point whose value is code point.
    return code_point;
  };
}

/**
 * @constructor
 * @implements {Encoder}
 * @param {{fatal: boolean}} options
 */
function UTF8Encoder(options) {
  var fatal = options.fatal;
  /**
   * @param {Stream} stream Input stream.
   * @param {number} code_point Next code point read from the stream.
   * @return {(number|!Array.<number>)} Byte(s) to emit.
   */
  this.handler = function(stream, code_point) {
    // 1. If code point is end-of-stream, return finished.
    if (code_point === end_of_stream)
      return finished;

    // 2. If code point is in the range U+0000 to U+007F, return a
    // byte whose value is code point.
    if (inRange(code_point, 0x0000, 0x007f))
      return code_point;

    // 3. Set count and offset based on the range code point is in:
    var count, offset;
    // U+0080 to U+07FF:    1 and 0xC0
    if (inRange(code_point, 0x0080, 0x07FF)) {
      count = 1;
      offset = 0xC0;
    }
    // U+0800 to U+FFFF:    2 and 0xE0
    else if (inRange(code_point, 0x0800, 0xFFFF)) {
      count = 2;
      offset = 0xE0;
    }
    // U+10000 to U+10FFFF: 3 and 0xF0
    else if (inRange(code_point, 0x10000, 0x10FFFF)) {
      count = 3;
      offset = 0xF0;
    }

    // 4.Let bytes be a byte sequence whose first byte is (code
    // point >> (6 × count)) + offset.
    var bytes = [(code_point >> (6 * count)) + offset];

    // 5. Run these substeps while count is greater than 0:
    while (count > 0) {

      // 1. Set temp to code point >> (6 × (count − 1)).
      var temp = code_point >> (6 * (count - 1));

      // 2. Append to bytes 0x80 | (temp & 0x3F).
      bytes.push(0x80 | (temp & 0x3F));

      // 3. Decrease count by one.
      count -= 1;
    }

    // 6. Return bytes bytes, in order.
    return bytes;
  };
}

exports.TextEncoder = TextEncoder;
exports.TextDecoder = TextDecoder;
},{}],26:[function(require,module,exports){
/** @license URI.js v4.2.1 (c) 2011 Gary Court. License: http://github.com/garycourt/uri-js */
(function (global, factory) {
	typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports) :
	typeof define === 'function' && define.amd ? define(['exports'], factory) :
	(factory((global.URI = global.URI || {})));
}(this, (function (exports) { 'use strict';

function merge() {
    for (var _len = arguments.length, sets = Array(_len), _key = 0; _key < _len; _key++) {
        sets[_key] = arguments[_key];
    }

    if (sets.length > 1) {
        sets[0] = sets[0].slice(0, -1);
        var xl = sets.length - 1;
        for (var x = 1; x < xl; ++x) {
            sets[x] = sets[x].slice(1, -1);
        }
        sets[xl] = sets[xl].slice(1);
        return sets.join('');
    } else {
        return sets[0];
    }
}
function subexp(str) {
    return "(?:" + str + ")";
}
function typeOf(o) {
    return o === undefined ? "undefined" : o === null ? "null" : Object.prototype.toString.call(o).split(" ").pop().split("]").shift().toLowerCase();
}
function toUpperCase(str) {
    return str.toUpperCase();
}
function toArray(obj) {
    return obj !== undefined && obj !== null ? obj instanceof Array ? obj : typeof obj.length !== "number" || obj.split || obj.setInterval || obj.call ? [obj] : Array.prototype.slice.call(obj) : [];
}
function assign(target, source) {
    var obj = target;
    if (source) {
        for (var key in source) {
            obj[key] = source[key];
        }
    }
    return obj;
}

function buildExps(isIRI) {
    var ALPHA$$ = "[A-Za-z]",
        CR$ = "[\\x0D]",
        DIGIT$$ = "[0-9]",
        DQUOTE$$ = "[\\x22]",
        HEXDIG$$ = merge(DIGIT$$, "[A-Fa-f]"),
        //case-insensitive
    LF$$ = "[\\x0A]",
        SP$$ = "[\\x20]",
        PCT_ENCODED$ = subexp(subexp("%[EFef]" + HEXDIG$$ + "%" + HEXDIG$$ + HEXDIG$$ + "%" + HEXDIG$$ + HEXDIG$$) + "|" + subexp("%[89A-Fa-f]" + HEXDIG$$ + "%" + HEXDIG$$ + HEXDIG$$) + "|" + subexp("%" + HEXDIG$$ + HEXDIG$$)),
        //expanded
    GEN_DELIMS$$ = "[\\:\\/\\?\\#\\[\\]\\@]",
        SUB_DELIMS$$ = "[\\!\\$\\&\\'\\(\\)\\*\\+\\,\\;\\=]",
        RESERVED$$ = merge(GEN_DELIMS$$, SUB_DELIMS$$),
        UCSCHAR$$ = isIRI ? "[\\xA0-\\u200D\\u2010-\\u2029\\u202F-\\uD7FF\\uF900-\\uFDCF\\uFDF0-\\uFFEF]" : "[]",
        //subset, excludes bidi control characters
    IPRIVATE$$ = isIRI ? "[\\uE000-\\uF8FF]" : "[]",
        //subset
    UNRESERVED$$ = merge(ALPHA$$, DIGIT$$, "[\\-\\.\\_\\~]", UCSCHAR$$),
        SCHEME$ = subexp(ALPHA$$ + merge(ALPHA$$, DIGIT$$, "[\\+\\-\\.]") + "*"),
        USERINFO$ = subexp(subexp(PCT_ENCODED$ + "|" + merge(UNRESERVED$$, SUB_DELIMS$$, "[\\:]")) + "*"),
        DEC_OCTET$ = subexp(subexp("25[0-5]") + "|" + subexp("2[0-4]" + DIGIT$$) + "|" + subexp("1" + DIGIT$$ + DIGIT$$) + "|" + subexp("[1-9]" + DIGIT$$) + "|" + DIGIT$$),
        DEC_OCTET_RELAXED$ = subexp(subexp("25[0-5]") + "|" + subexp("2[0-4]" + DIGIT$$) + "|" + subexp("1" + DIGIT$$ + DIGIT$$) + "|" + subexp("0?[1-9]" + DIGIT$$) + "|0?0?" + DIGIT$$),
        //relaxed parsing rules
    IPV4ADDRESS$ = subexp(DEC_OCTET_RELAXED$ + "\\." + DEC_OCTET_RELAXED$ + "\\." + DEC_OCTET_RELAXED$ + "\\." + DEC_OCTET_RELAXED$),
        H16$ = subexp(HEXDIG$$ + "{1,4}"),
        LS32$ = subexp(subexp(H16$ + "\\:" + H16$) + "|" + IPV4ADDRESS$),
        IPV6ADDRESS1$ = subexp(subexp(H16$ + "\\:") + "{6}" + LS32$),
        //                           6( h16 ":" ) ls32
    IPV6ADDRESS2$ = subexp("\\:\\:" + subexp(H16$ + "\\:") + "{5}" + LS32$),
        //                      "::" 5( h16 ":" ) ls32
    IPV6ADDRESS3$ = subexp(subexp(H16$) + "?\\:\\:" + subexp(H16$ + "\\:") + "{4}" + LS32$),
        //[               h16 ] "::" 4( h16 ":" ) ls32
    IPV6ADDRESS4$ = subexp(subexp(subexp(H16$ + "\\:") + "{0,1}" + H16$) + "?\\:\\:" + subexp(H16$ + "\\:") + "{3}" + LS32$),
        //[ *1( h16 ":" ) h16 ] "::" 3( h16 ":" ) ls32
    IPV6ADDRESS5$ = subexp(subexp(subexp(H16$ + "\\:") + "{0,2}" + H16$) + "?\\:\\:" + subexp(H16$ + "\\:") + "{2}" + LS32$),
        //[ *2( h16 ":" ) h16 ] "::" 2( h16 ":" ) ls32
    IPV6ADDRESS6$ = subexp(subexp(subexp(H16$ + "\\:") + "{0,3}" + H16$) + "?\\:\\:" + H16$ + "\\:" + LS32$),
        //[ *3( h16 ":" ) h16 ] "::"    h16 ":"   ls32
    IPV6ADDRESS7$ = subexp(subexp(subexp(H16$ + "\\:") + "{0,4}" + H16$) + "?\\:\\:" + LS32$),
        //[ *4( h16 ":" ) h16 ] "::"              ls32
    IPV6ADDRESS8$ = subexp(subexp(subexp(H16$ + "\\:") + "{0,5}" + H16$) + "?\\:\\:" + H16$),
        //[ *5( h16 ":" ) h16 ] "::"              h16
    IPV6ADDRESS9$ = subexp(subexp(subexp(H16$ + "\\:") + "{0,6}" + H16$) + "?\\:\\:"),
        //[ *6( h16 ":" ) h16 ] "::"
    IPV6ADDRESS$ = subexp([IPV6ADDRESS1$, IPV6ADDRESS2$, IPV6ADDRESS3$, IPV6ADDRESS4$, IPV6ADDRESS5$, IPV6ADDRESS6$, IPV6ADDRESS7$, IPV6ADDRESS8$, IPV6ADDRESS9$].join("|")),
        ZONEID$ = subexp(subexp(UNRESERVED$$ + "|" + PCT_ENCODED$) + "+"),
        //RFC 6874
    IPV6ADDRZ$ = subexp(IPV6ADDRESS$ + "\\%25" + ZONEID$),
        //RFC 6874
    IPV6ADDRZ_RELAXED$ = subexp(IPV6ADDRESS$ + subexp("\\%25|\\%(?!" + HEXDIG$$ + "{2})") + ZONEID$),
        //RFC 6874, with relaxed parsing rules
    IPVFUTURE$ = subexp("[vV]" + HEXDIG$$ + "+\\." + merge(UNRESERVED$$, SUB_DELIMS$$, "[\\:]") + "+"),
        IP_LITERAL$ = subexp("\\[" + subexp(IPV6ADDRZ_RELAXED$ + "|" + IPV6ADDRESS$ + "|" + IPVFUTURE$) + "\\]"),
        //RFC 6874
    REG_NAME$ = subexp(subexp(PCT_ENCODED$ + "|" + merge(UNRESERVED$$, SUB_DELIMS$$)) + "*"),
        HOST$ = subexp(IP_LITERAL$ + "|" + IPV4ADDRESS$ + "(?!" + REG_NAME$ + ")" + "|" + REG_NAME$),
        PORT$ = subexp(DIGIT$$ + "*"),
        AUTHORITY$ = subexp(subexp(USERINFO$ + "@") + "?" + HOST$ + subexp("\\:" + PORT$) + "?"),
        PCHAR$ = subexp(PCT_ENCODED$ + "|" + merge(UNRESERVED$$, SUB_DELIMS$$, "[\\:\\@]")),
        SEGMENT$ = subexp(PCHAR$ + "*"),
        SEGMENT_NZ$ = subexp(PCHAR$ + "+"),
        SEGMENT_NZ_NC$ = subexp(subexp(PCT_ENCODED$ + "|" + merge(UNRESERVED$$, SUB_DELIMS$$, "[\\@]")) + "+"),
        PATH_ABEMPTY$ = subexp(subexp("\\/" + SEGMENT$) + "*"),
        PATH_ABSOLUTE$ = subexp("\\/" + subexp(SEGMENT_NZ$ + PATH_ABEMPTY$) + "?"),
        //simplified
    PATH_NOSCHEME$ = subexp(SEGMENT_NZ_NC$ + PATH_ABEMPTY$),
        //simplified
    PATH_ROOTLESS$ = subexp(SEGMENT_NZ$ + PATH_ABEMPTY$),
        //simplified
    PATH_EMPTY$ = "(?!" + PCHAR$ + ")",
        PATH$ = subexp(PATH_ABEMPTY$ + "|" + PATH_ABSOLUTE$ + "|" + PATH_NOSCHEME$ + "|" + PATH_ROOTLESS$ + "|" + PATH_EMPTY$),
        QUERY$ = subexp(subexp(PCHAR$ + "|" + merge("[\\/\\?]", IPRIVATE$$)) + "*"),
        FRAGMENT$ = subexp(subexp(PCHAR$ + "|[\\/\\?]") + "*"),
        HIER_PART$ = subexp(subexp("\\/\\/" + AUTHORITY$ + PATH_ABEMPTY$) + "|" + PATH_ABSOLUTE$ + "|" + PATH_ROOTLESS$ + "|" + PATH_EMPTY$),
        URI$ = subexp(SCHEME$ + "\\:" + HIER_PART$ + subexp("\\?" + QUERY$) + "?" + subexp("\\#" + FRAGMENT$) + "?"),
        RELATIVE_PART$ = subexp(subexp("\\/\\/" + AUTHORITY$ + PATH_ABEMPTY$) + "|" + PATH_ABSOLUTE$ + "|" + PATH_NOSCHEME$ + "|" + PATH_EMPTY$),
        RELATIVE$ = subexp(RELATIVE_PART$ + subexp("\\?" + QUERY$) + "?" + subexp("\\#" + FRAGMENT$) + "?"),
        URI_REFERENCE$ = subexp(URI$ + "|" + RELATIVE$),
        ABSOLUTE_URI$ = subexp(SCHEME$ + "\\:" + HIER_PART$ + subexp("\\?" + QUERY$) + "?"),
        GENERIC_REF$ = "^(" + SCHEME$ + ")\\:" + subexp(subexp("\\/\\/(" + subexp("(" + USERINFO$ + ")@") + "?(" + HOST$ + ")" + subexp("\\:(" + PORT$ + ")") + "?)") + "?(" + PATH_ABEMPTY$ + "|" + PATH_ABSOLUTE$ + "|" + PATH_ROOTLESS$ + "|" + PATH_EMPTY$ + ")") + subexp("\\?(" + QUERY$ + ")") + "?" + subexp("\\#(" + FRAGMENT$ + ")") + "?$",
        RELATIVE_REF$ = "^(){0}" + subexp(subexp("\\/\\/(" + subexp("(" + USERINFO$ + ")@") + "?(" + HOST$ + ")" + subexp("\\:(" + PORT$ + ")") + "?)") + "?(" + PATH_ABEMPTY$ + "|" + PATH_ABSOLUTE$ + "|" + PATH_NOSCHEME$ + "|" + PATH_EMPTY$ + ")") + subexp("\\?(" + QUERY$ + ")") + "?" + subexp("\\#(" + FRAGMENT$ + ")") + "?$",
        ABSOLUTE_REF$ = "^(" + SCHEME$ + ")\\:" + subexp(subexp("\\/\\/(" + subexp("(" + USERINFO$ + ")@") + "?(" + HOST$ + ")" + subexp("\\:(" + PORT$ + ")") + "?)") + "?(" + PATH_ABEMPTY$ + "|" + PATH_ABSOLUTE$ + "|" + PATH_ROOTLESS$ + "|" + PATH_EMPTY$ + ")") + subexp("\\?(" + QUERY$ + ")") + "?$",
        SAMEDOC_REF$ = "^" + subexp("\\#(" + FRAGMENT$ + ")") + "?$",
        AUTHORITY_REF$ = "^" + subexp("(" + USERINFO$ + ")@") + "?(" + HOST$ + ")" + subexp("\\:(" + PORT$ + ")") + "?$";
    return {
        NOT_SCHEME: new RegExp(merge("[^]", ALPHA$$, DIGIT$$, "[\\+\\-\\.]"), "g"),
        NOT_USERINFO: new RegExp(merge("[^\\%\\:]", UNRESERVED$$, SUB_DELIMS$$), "g"),
        NOT_HOST: new RegExp(merge("[^\\%\\[\\]\\:]", UNRESERVED$$, SUB_DELIMS$$), "g"),
        NOT_PATH: new RegExp(merge("[^\\%\\/\\:\\@]", UNRESERVED$$, SUB_DELIMS$$), "g"),
        NOT_PATH_NOSCHEME: new RegExp(merge("[^\\%\\/\\@]", UNRESERVED$$, SUB_DELIMS$$), "g"),
        NOT_QUERY: new RegExp(merge("[^\\%]", UNRESERVED$$, SUB_DELIMS$$, "[\\:\\@\\/\\?]", IPRIVATE$$), "g"),
        NOT_FRAGMENT: new RegExp(merge("[^\\%]", UNRESERVED$$, SUB_DELIMS$$, "[\\:\\@\\/\\?]"), "g"),
        ESCAPE: new RegExp(merge("[^]", UNRESERVED$$, SUB_DELIMS$$), "g"),
        UNRESERVED: new RegExp(UNRESERVED$$, "g"),
        OTHER_CHARS: new RegExp(merge("[^\\%]", UNRESERVED$$, RESERVED$$), "g"),
        PCT_ENCODED: new RegExp(PCT_ENCODED$, "g"),
        IPV4ADDRESS: new RegExp("^(" + IPV4ADDRESS$ + ")$"),
        IPV6ADDRESS: new RegExp("^\\[?(" + IPV6ADDRESS$ + ")" + subexp(subexp("\\%25|\\%(?!" + HEXDIG$$ + "{2})") + "(" + ZONEID$ + ")") + "?\\]?$") //RFC 6874, with relaxed parsing rules
    };
}
var URI_PROTOCOL = buildExps(false);

var IRI_PROTOCOL = buildExps(true);

var slicedToArray = function () {
  function sliceIterator(arr, i) {
    var _arr = [];
    var _n = true;
    var _d = false;
    var _e = undefined;

    try {
      for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) {
        _arr.push(_s.value);

        if (i && _arr.length === i) break;
      }
    } catch (err) {
      _d = true;
      _e = err;
    } finally {
      try {
        if (!_n && _i["return"]) _i["return"]();
      } finally {
        if (_d) throw _e;
      }
    }

    return _arr;
  }

  return function (arr, i) {
    if (Array.isArray(arr)) {
      return arr;
    } else if (Symbol.iterator in Object(arr)) {
      return sliceIterator(arr, i);
    } else {
      throw new TypeError("Invalid attempt to destructure non-iterable instance");
    }
  };
}();













var toConsumableArray = function (arr) {
  if (Array.isArray(arr)) {
    for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) arr2[i] = arr[i];

    return arr2;
  } else {
    return Array.from(arr);
  }
};

/** Highest positive signed 32-bit float value */

var maxInt = 2147483647; // aka. 0x7FFFFFFF or 2^31-1

/** Bootstring parameters */
var base = 36;
var tMin = 1;
var tMax = 26;
var skew = 38;
var damp = 700;
var initialBias = 72;
var initialN = 128; // 0x80
var delimiter = '-'; // '\x2D'

/** Regular expressions */
var regexPunycode = /^xn--/;
var regexNonASCII = /[^\0-\x7E]/; // non-ASCII chars
var regexSeparators = /[\x2E\u3002\uFF0E\uFF61]/g; // RFC 3490 separators

/** Error messages */
var errors = {
	'overflow': 'Overflow: input needs wider integers to process',
	'not-basic': 'Illegal input >= 0x80 (not a basic code point)',
	'invalid-input': 'Invalid input'
};

/** Convenience shortcuts */
var baseMinusTMin = base - tMin;
var floor = Math.floor;
var stringFromCharCode = String.fromCharCode;

/*--------------------------------------------------------------------------*/

/**
 * A generic error utility function.
 * @private
 * @param {String} type The error type.
 * @returns {Error} Throws a `RangeError` with the applicable error message.
 */
function error$1(type) {
	throw new RangeError(errors[type]);
}

/**
 * A generic `Array#map` utility function.
 * @private
 * @param {Array} array The array to iterate over.
 * @param {Function} callback The function that gets called for every array
 * item.
 * @returns {Array} A new array of values returned by the callback function.
 */
function map(array, fn) {
	var result = [];
	var length = array.length;
	while (length--) {
		result[length] = fn(array[length]);
	}
	return result;
}

/**
 * A simple `Array#map`-like wrapper to work with domain name strings or email
 * addresses.
 * @private
 * @param {String} domain The domain name or email address.
 * @param {Function} callback The function that gets called for every
 * character.
 * @returns {Array} A new string of characters returned by the callback
 * function.
 */
function mapDomain(string, fn) {
	var parts = string.split('@');
	var result = '';
	if (parts.length > 1) {
		// In email addresses, only the domain name should be punycoded. Leave
		// the local part (i.e. everything up to `@`) intact.
		result = parts[0] + '@';
		string = parts[1];
	}
	// Avoid `split(regex)` for IE8 compatibility. See #17.
	string = string.replace(regexSeparators, '\x2E');
	var labels = string.split('.');
	var encoded = map(labels, fn).join('.');
	return result + encoded;
}

/**
 * Creates an array containing the numeric code points of each Unicode
 * character in the string. While JavaScript uses UCS-2 internally,
 * this function will convert a pair of surrogate halves (each of which
 * UCS-2 exposes as separate characters) into a single code point,
 * matching UTF-16.
 * @see `punycode.ucs2.encode`
 * @see <https://mathiasbynens.be/notes/javascript-encoding>
 * @memberOf punycode.ucs2
 * @name decode
 * @param {String} string The Unicode input string (UCS-2).
 * @returns {Array} The new array of code points.
 */
function ucs2decode(string) {
	var output = [];
	var counter = 0;
	var length = string.length;
	while (counter < length) {
		var value = string.charCodeAt(counter++);
		if (value >= 0xD800 && value <= 0xDBFF && counter < length) {
			// It's a high surrogate, and there is a next character.
			var extra = string.charCodeAt(counter++);
			if ((extra & 0xFC00) == 0xDC00) {
				// Low surrogate.
				output.push(((value & 0x3FF) << 10) + (extra & 0x3FF) + 0x10000);
			} else {
				// It's an unmatched surrogate; only append this code unit, in case the
				// next code unit is the high surrogate of a surrogate pair.
				output.push(value);
				counter--;
			}
		} else {
			output.push(value);
		}
	}
	return output;
}

/**
 * Creates a string based on an array of numeric code points.
 * @see `punycode.ucs2.decode`
 * @memberOf punycode.ucs2
 * @name encode
 * @param {Array} codePoints The array of numeric code points.
 * @returns {String} The new Unicode string (UCS-2).
 */
var ucs2encode = function ucs2encode(array) {
	return String.fromCodePoint.apply(String, toConsumableArray(array));
};

/**
 * Converts a basic code point into a digit/integer.
 * @see `digitToBasic()`
 * @private
 * @param {Number} codePoint The basic numeric code point value.
 * @returns {Number} The numeric value of a basic code point (for use in
 * representing integers) in the range `0` to `base - 1`, or `base` if
 * the code point does not represent a value.
 */
var basicToDigit = function basicToDigit(codePoint) {
	if (codePoint - 0x30 < 0x0A) {
		return codePoint - 0x16;
	}
	if (codePoint - 0x41 < 0x1A) {
		return codePoint - 0x41;
	}
	if (codePoint - 0x61 < 0x1A) {
		return codePoint - 0x61;
	}
	return base;
};

/**
 * Converts a digit/integer into a basic code point.
 * @see `basicToDigit()`
 * @private
 * @param {Number} digit The numeric value of a basic code point.
 * @returns {Number} The basic code point whose value (when used for
 * representing integers) is `digit`, which needs to be in the range
 * `0` to `base - 1`. If `flag` is non-zero, the uppercase form is
 * used; else, the lowercase form is used. The behavior is undefined
 * if `flag` is non-zero and `digit` has no uppercase form.
 */
var digitToBasic = function digitToBasic(digit, flag) {
	//  0..25 map to ASCII a..z or A..Z
	// 26..35 map to ASCII 0..9
	return digit + 22 + 75 * (digit < 26) - ((flag != 0) << 5);
};

/**
 * Bias adaptation function as per section 3.4 of RFC 3492.
 * https://tools.ietf.org/html/rfc3492#section-3.4
 * @private
 */
var adapt = function adapt(delta, numPoints, firstTime) {
	var k = 0;
	delta = firstTime ? floor(delta / damp) : delta >> 1;
	delta += floor(delta / numPoints);
	for (; /* no initialization */delta > baseMinusTMin * tMax >> 1; k += base) {
		delta = floor(delta / baseMinusTMin);
	}
	return floor(k + (baseMinusTMin + 1) * delta / (delta + skew));
};

/**
 * Converts a Punycode string of ASCII-only symbols to a string of Unicode
 * symbols.
 * @memberOf punycode
 * @param {String} input The Punycode string of ASCII-only symbols.
 * @returns {String} The resulting string of Unicode symbols.
 */
var decode = function decode(input) {
	// Don't use UCS-2.
	var output = [];
	var inputLength = input.length;
	var i = 0;
	var n = initialN;
	var bias = initialBias;

	// Handle the basic code points: let `basic` be the number of input code
	// points before the last delimiter, or `0` if there is none, then copy
	// the first basic code points to the output.

	var basic = input.lastIndexOf(delimiter);
	if (basic < 0) {
		basic = 0;
	}

	for (var j = 0; j < basic; ++j) {
		// if it's not a basic code point
		if (input.charCodeAt(j) >= 0x80) {
			error$1('not-basic');
		}
		output.push(input.charCodeAt(j));
	}

	// Main decoding loop: start just after the last delimiter if any basic code
	// points were copied; start at the beginning otherwise.

	for (var index = basic > 0 ? basic + 1 : 0; index < inputLength;) /* no final expression */{

		// `index` is the index of the next character to be consumed.
		// Decode a generalized variable-length integer into `delta`,
		// which gets added to `i`. The overflow checking is easier
		// if we increase `i` as we go, then subtract off its starting
		// value at the end to obtain `delta`.
		var oldi = i;
		for (var w = 1, k = base;; /* no condition */k += base) {

			if (index >= inputLength) {
				error$1('invalid-input');
			}

			var digit = basicToDigit(input.charCodeAt(index++));

			if (digit >= base || digit > floor((maxInt - i) / w)) {
				error$1('overflow');
			}

			i += digit * w;
			var t = k <= bias ? tMin : k >= bias + tMax ? tMax : k - bias;

			if (digit < t) {
				break;
			}

			var baseMinusT = base - t;
			if (w > floor(maxInt / baseMinusT)) {
				error$1('overflow');
			}

			w *= baseMinusT;
		}

		var out = output.length + 1;
		bias = adapt(i - oldi, out, oldi == 0);

		// `i` was supposed to wrap around from `out` to `0`,
		// incrementing `n` each time, so we'll fix that now:
		if (floor(i / out) > maxInt - n) {
			error$1('overflow');
		}

		n += floor(i / out);
		i %= out;

		// Insert `n` at position `i` of the output.
		output.splice(i++, 0, n);
	}

	return String.fromCodePoint.apply(String, output);
};

/**
 * Converts a string of Unicode symbols (e.g. a domain name label) to a
 * Punycode string of ASCII-only symbols.
 * @memberOf punycode
 * @param {String} input The string of Unicode symbols.
 * @returns {String} The resulting Punycode string of ASCII-only symbols.
 */
var encode = function encode(input) {
	var output = [];

	// Convert the input in UCS-2 to an array of Unicode code points.
	input = ucs2decode(input);

	// Cache the length.
	var inputLength = input.length;

	// Initialize the state.
	var n = initialN;
	var delta = 0;
	var bias = initialBias;

	// Handle the basic code points.
	var _iteratorNormalCompletion = true;
	var _didIteratorError = false;
	var _iteratorError = undefined;

	try {
		for (var _iterator = input[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
			var _currentValue2 = _step.value;

			if (_currentValue2 < 0x80) {
				output.push(stringFromCharCode(_currentValue2));
			}
		}
	} catch (err) {
		_didIteratorError = true;
		_iteratorError = err;
	} finally {
		try {
			if (!_iteratorNormalCompletion && _iterator.return) {
				_iterator.return();
			}
		} finally {
			if (_didIteratorError) {
				throw _iteratorError;
			}
		}
	}

	var basicLength = output.length;
	var handledCPCount = basicLength;

	// `handledCPCount` is the number of code points that have been handled;
	// `basicLength` is the number of basic code points.

	// Finish the basic string with a delimiter unless it's empty.
	if (basicLength) {
		output.push(delimiter);
	}

	// Main encoding loop:
	while (handledCPCount < inputLength) {

		// All non-basic code points < n have been handled already. Find the next
		// larger one:
		var m = maxInt;
		var _iteratorNormalCompletion2 = true;
		var _didIteratorError2 = false;
		var _iteratorError2 = undefined;

		try {
			for (var _iterator2 = input[Symbol.iterator](), _step2; !(_iteratorNormalCompletion2 = (_step2 = _iterator2.next()).done); _iteratorNormalCompletion2 = true) {
				var currentValue = _step2.value;

				if (currentValue >= n && currentValue < m) {
					m = currentValue;
				}
			}

			// Increase `delta` enough to advance the decoder's <n,i> state to <m,0>,
			// but guard against overflow.
		} catch (err) {
			_didIteratorError2 = true;
			_iteratorError2 = err;
		} finally {
			try {
				if (!_iteratorNormalCompletion2 && _iterator2.return) {
					_iterator2.return();
				}
			} finally {
				if (_didIteratorError2) {
					throw _iteratorError2;
				}
			}
		}

		var handledCPCountPlusOne = handledCPCount + 1;
		if (m - n > floor((maxInt - delta) / handledCPCountPlusOne)) {
			error$1('overflow');
		}

		delta += (m - n) * handledCPCountPlusOne;
		n = m;

		var _iteratorNormalCompletion3 = true;
		var _didIteratorError3 = false;
		var _iteratorError3 = undefined;

		try {
			for (var _iterator3 = input[Symbol.iterator](), _step3; !(_iteratorNormalCompletion3 = (_step3 = _iterator3.next()).done); _iteratorNormalCompletion3 = true) {
				var _currentValue = _step3.value;

				if (_currentValue < n && ++delta > maxInt) {
					error$1('overflow');
				}
				if (_currentValue == n) {
					// Represent delta as a generalized variable-length integer.
					var q = delta;
					for (var k = base;; /* no condition */k += base) {
						var t = k <= bias ? tMin : k >= bias + tMax ? tMax : k - bias;
						if (q < t) {
							break;
						}
						var qMinusT = q - t;
						var baseMinusT = base - t;
						output.push(stringFromCharCode(digitToBasic(t + qMinusT % baseMinusT, 0)));
						q = floor(qMinusT / baseMinusT);
					}

					output.push(stringFromCharCode(digitToBasic(q, 0)));
					bias = adapt(delta, handledCPCountPlusOne, handledCPCount == basicLength);
					delta = 0;
					++handledCPCount;
				}
			}
		} catch (err) {
			_didIteratorError3 = true;
			_iteratorError3 = err;
		} finally {
			try {
				if (!_iteratorNormalCompletion3 && _iterator3.return) {
					_iterator3.return();
				}
			} finally {
				if (_didIteratorError3) {
					throw _iteratorError3;
				}
			}
		}

		++delta;
		++n;
	}
	return output.join('');
};

/**
 * Converts a Punycode string representing a domain name or an email address
 * to Unicode. Only the Punycoded parts of the input will be converted, i.e.
 * it doesn't matter if you call it on a string that has already been
 * converted to Unicode.
 * @memberOf punycode
 * @param {String} input The Punycoded domain name or email address to
 * convert to Unicode.
 * @returns {String} The Unicode representation of the given Punycode
 * string.
 */
var toUnicode = function toUnicode(input) {
	return mapDomain(input, function (string) {
		return regexPunycode.test(string) ? decode(string.slice(4).toLowerCase()) : string;
	});
};

/**
 * Converts a Unicode string representing a domain name or an email address to
 * Punycode. Only the non-ASCII parts of the domain name will be converted,
 * i.e. it doesn't matter if you call it with a domain that's already in
 * ASCII.
 * @memberOf punycode
 * @param {String} input The domain name or email address to convert, as a
 * Unicode string.
 * @returns {String} The Punycode representation of the given domain name or
 * email address.
 */
var toASCII = function toASCII(input) {
	return mapDomain(input, function (string) {
		return regexNonASCII.test(string) ? 'xn--' + encode(string) : string;
	});
};

/*--------------------------------------------------------------------------*/

/** Define the public API */
var punycode = {
	/**
  * A string representing the current Punycode.js version number.
  * @memberOf punycode
  * @type String
  */
	'version': '2.1.0',
	/**
  * An object of methods to convert from JavaScript's internal character
  * representation (UCS-2) to Unicode code points, and back.
  * @see <https://mathiasbynens.be/notes/javascript-encoding>
  * @memberOf punycode
  * @type Object
  */
	'ucs2': {
		'decode': ucs2decode,
		'encode': ucs2encode
	},
	'decode': decode,
	'encode': encode,
	'toASCII': toASCII,
	'toUnicode': toUnicode
};

/**
 * URI.js
 *
 * @fileoverview An RFC 3986 compliant, scheme extendable URI parsing/validating/resolving library for JavaScript.
 * @author <a href="mailto:gary.court@gmail.com">Gary Court</a>
 * @see http://github.com/garycourt/uri-js
 */
/**
 * Copyright 2011 Gary Court. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are
 * permitted provided that the following conditions are met:
 *
 *    1. Redistributions of source code must retain the above copyright notice, this list of
 *       conditions and the following disclaimer.
 *
 *    2. Redistributions in binary form must reproduce the above copyright notice, this list
 *       of conditions and the following disclaimer in the documentation and/or other materials
 *       provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY GARY COURT ``AS IS'' AND ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND
 * FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL GARY COURT OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * The views and conclusions contained in the software and documentation are those of the
 * authors and should not be interpreted as representing official policies, either expressed
 * or implied, of Gary Court.
 */
var SCHEMES = {};
function pctEncChar(chr) {
    var c = chr.charCodeAt(0);
    var e = void 0;
    if (c < 16) e = "%0" + c.toString(16).toUpperCase();else if (c < 128) e = "%" + c.toString(16).toUpperCase();else if (c < 2048) e = "%" + (c >> 6 | 192).toString(16).toUpperCase() + "%" + (c & 63 | 128).toString(16).toUpperCase();else e = "%" + (c >> 12 | 224).toString(16).toUpperCase() + "%" + (c >> 6 & 63 | 128).toString(16).toUpperCase() + "%" + (c & 63 | 128).toString(16).toUpperCase();
    return e;
}
function pctDecChars(str) {
    var newStr = "";
    var i = 0;
    var il = str.length;
    while (i < il) {
        var c = parseInt(str.substr(i + 1, 2), 16);
        if (c < 128) {
            newStr += String.fromCharCode(c);
            i += 3;
        } else if (c >= 194 && c < 224) {
            if (il - i >= 6) {
                var c2 = parseInt(str.substr(i + 4, 2), 16);
                newStr += String.fromCharCode((c & 31) << 6 | c2 & 63);
            } else {
                newStr += str.substr(i, 6);
            }
            i += 6;
        } else if (c >= 224) {
            if (il - i >= 9) {
                var _c = parseInt(str.substr(i + 4, 2), 16);
                var c3 = parseInt(str.substr(i + 7, 2), 16);
                newStr += String.fromCharCode((c & 15) << 12 | (_c & 63) << 6 | c3 & 63);
            } else {
                newStr += str.substr(i, 9);
            }
            i += 9;
        } else {
            newStr += str.substr(i, 3);
            i += 3;
        }
    }
    return newStr;
}
function _normalizeComponentEncoding(components, protocol) {
    function decodeUnreserved(str) {
        var decStr = pctDecChars(str);
        return !decStr.match(protocol.UNRESERVED) ? str : decStr;
    }
    if (components.scheme) components.scheme = String(components.scheme).replace(protocol.PCT_ENCODED, decodeUnreserved).toLowerCase().replace(protocol.NOT_SCHEME, "");
    if (components.userinfo !== undefined) components.userinfo = String(components.userinfo).replace(protocol.PCT_ENCODED, decodeUnreserved).replace(protocol.NOT_USERINFO, pctEncChar).replace(protocol.PCT_ENCODED, toUpperCase);
    if (components.host !== undefined) components.host = String(components.host).replace(protocol.PCT_ENCODED, decodeUnreserved).toLowerCase().replace(protocol.NOT_HOST, pctEncChar).replace(protocol.PCT_ENCODED, toUpperCase);
    if (components.path !== undefined) components.path = String(components.path).replace(protocol.PCT_ENCODED, decodeUnreserved).replace(components.scheme ? protocol.NOT_PATH : protocol.NOT_PATH_NOSCHEME, pctEncChar).replace(protocol.PCT_ENCODED, toUpperCase);
    if (components.query !== undefined) components.query = String(components.query).replace(protocol.PCT_ENCODED, decodeUnreserved).replace(protocol.NOT_QUERY, pctEncChar).replace(protocol.PCT_ENCODED, toUpperCase);
    if (components.fragment !== undefined) components.fragment = String(components.fragment).replace(protocol.PCT_ENCODED, decodeUnreserved).replace(protocol.NOT_FRAGMENT, pctEncChar).replace(protocol.PCT_ENCODED, toUpperCase);
    return components;
}

function _stripLeadingZeros(str) {
    return str.replace(/^0*(.*)/, "$1") || "0";
}
function _normalizeIPv4(host, protocol) {
    var matches = host.match(protocol.IPV4ADDRESS) || [];

    var _matches = slicedToArray(matches, 2),
        address = _matches[1];

    if (address) {
        return address.split(".").map(_stripLeadingZeros).join(".");
    } else {
        return host;
    }
}
function _normalizeIPv6(host, protocol) {
    var matches = host.match(protocol.IPV6ADDRESS) || [];

    var _matches2 = slicedToArray(matches, 3),
        address = _matches2[1],
        zone = _matches2[2];

    if (address) {
        var _address$toLowerCase$ = address.toLowerCase().split('::').reverse(),
            _address$toLowerCase$2 = slicedToArray(_address$toLowerCase$, 2),
            last = _address$toLowerCase$2[0],
            first = _address$toLowerCase$2[1];

        var firstFields = first ? first.split(":").map(_stripLeadingZeros) : [];
        var lastFields = last.split(":").map(_stripLeadingZeros);
        var isLastFieldIPv4Address = protocol.IPV4ADDRESS.test(lastFields[lastFields.length - 1]);
        var fieldCount = isLastFieldIPv4Address ? 7 : 8;
        var lastFieldsStart = lastFields.length - fieldCount;
        var fields = Array(fieldCount);
        for (var x = 0; x < fieldCount; ++x) {
            fields[x] = firstFields[x] || lastFields[lastFieldsStart + x] || '';
        }
        if (isLastFieldIPv4Address) {
            fields[fieldCount - 1] = _normalizeIPv4(fields[fieldCount - 1], protocol);
        }
        var allZeroFields = fields.reduce(function (acc, field, index) {
            if (!field || field === "0") {
                var lastLongest = acc[acc.length - 1];
                if (lastLongest && lastLongest.index + lastLongest.length === index) {
                    lastLongest.length++;
                } else {
                    acc.push({ index: index, length: 1 });
                }
            }
            return acc;
        }, []);
        var longestZeroFields = allZeroFields.sort(function (a, b) {
            return b.length - a.length;
        })[0];
        var newHost = void 0;
        if (longestZeroFields && longestZeroFields.length > 1) {
            var newFirst = fields.slice(0, longestZeroFields.index);
            var newLast = fields.slice(longestZeroFields.index + longestZeroFields.length);
            newHost = newFirst.join(":") + "::" + newLast.join(":");
        } else {
            newHost = fields.join(":");
        }
        if (zone) {
            newHost += "%" + zone;
        }
        return newHost;
    } else {
        return host;
    }
}
var URI_PARSE = /^(?:([^:\/?#]+):)?(?:\/\/((?:([^\/?#@]*)@)?(\[[^\/?#\]]+\]|[^\/?#:]*)(?:\:(\d*))?))?([^?#]*)(?:\?([^#]*))?(?:#((?:.|\n|\r)*))?/i;
var NO_MATCH_IS_UNDEFINED = "".match(/(){0}/)[1] === undefined;
function parse(uriString) {
    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

    var components = {};
    var protocol = options.iri !== false ? IRI_PROTOCOL : URI_PROTOCOL;
    if (options.reference === "suffix") uriString = (options.scheme ? options.scheme + ":" : "") + "//" + uriString;
    var matches = uriString.match(URI_PARSE);
    if (matches) {
        if (NO_MATCH_IS_UNDEFINED) {
            //store each component
            components.scheme = matches[1];
            components.userinfo = matches[3];
            components.host = matches[4];
            components.port = parseInt(matches[5], 10);
            components.path = matches[6] || "";
            components.query = matches[7];
            components.fragment = matches[8];
            //fix port number
            if (isNaN(components.port)) {
                components.port = matches[5];
            }
        } else {
            //IE FIX for improper RegExp matching
            //store each component
            components.scheme = matches[1] || undefined;
            components.userinfo = uriString.indexOf("@") !== -1 ? matches[3] : undefined;
            components.host = uriString.indexOf("//") !== -1 ? matches[4] : undefined;
            components.port = parseInt(matches[5], 10);
            components.path = matches[6] || "";
            components.query = uriString.indexOf("?") !== -1 ? matches[7] : undefined;
            components.fragment = uriString.indexOf("#") !== -1 ? matches[8] : undefined;
            //fix port number
            if (isNaN(components.port)) {
                components.port = uriString.match(/\/\/(?:.|\n)*\:(?:\/|\?|\#|$)/) ? matches[4] : undefined;
            }
        }
        if (components.host) {
            //normalize IP hosts
            components.host = _normalizeIPv6(_normalizeIPv4(components.host, protocol), protocol);
        }
        //determine reference type
        if (components.scheme === undefined && components.userinfo === undefined && components.host === undefined && components.port === undefined && !components.path && components.query === undefined) {
            components.reference = "same-document";
        } else if (components.scheme === undefined) {
            components.reference = "relative";
        } else if (components.fragment === undefined) {
            components.reference = "absolute";
        } else {
            components.reference = "uri";
        }
        //check for reference errors
        if (options.reference && options.reference !== "suffix" && options.reference !== components.reference) {
            components.error = components.error || "URI is not a " + options.reference + " reference.";
        }
        //find scheme handler
        var schemeHandler = SCHEMES[(options.scheme || components.scheme || "").toLowerCase()];
        //check if scheme can't handle IRIs
        if (!options.unicodeSupport && (!schemeHandler || !schemeHandler.unicodeSupport)) {
            //if host component is a domain name
            if (components.host && (options.domainHost || schemeHandler && schemeHandler.domainHost)) {
                //convert Unicode IDN -> ASCII IDN
                try {
                    components.host = punycode.toASCII(components.host.replace(protocol.PCT_ENCODED, pctDecChars).toLowerCase());
                } catch (e) {
                    components.error = components.error || "Host's domain name can not be converted to ASCII via punycode: " + e;
                }
            }
            //convert IRI -> URI
            _normalizeComponentEncoding(components, URI_PROTOCOL);
        } else {
            //normalize encodings
            _normalizeComponentEncoding(components, protocol);
        }
        //perform scheme specific parsing
        if (schemeHandler && schemeHandler.parse) {
            schemeHandler.parse(components, options);
        }
    } else {
        components.error = components.error || "URI can not be parsed.";
    }
    return components;
}

function _recomposeAuthority(components, options) {
    var protocol = options.iri !== false ? IRI_PROTOCOL : URI_PROTOCOL;
    var uriTokens = [];
    if (components.userinfo !== undefined) {
        uriTokens.push(components.userinfo);
        uriTokens.push("@");
    }
    if (components.host !== undefined) {
        //normalize IP hosts, add brackets and escape zone separator for IPv6
        uriTokens.push(_normalizeIPv6(_normalizeIPv4(String(components.host), protocol), protocol).replace(protocol.IPV6ADDRESS, function (_, $1, $2) {
            return "[" + $1 + ($2 ? "%25" + $2 : "") + "]";
        }));
    }
    if (typeof components.port === "number") {
        uriTokens.push(":");
        uriTokens.push(components.port.toString(10));
    }
    return uriTokens.length ? uriTokens.join("") : undefined;
}

var RDS1 = /^\.\.?\//;
var RDS2 = /^\/\.(\/|$)/;
var RDS3 = /^\/\.\.(\/|$)/;
var RDS5 = /^\/?(?:.|\n)*?(?=\/|$)/;
function removeDotSegments(input) {
    var output = [];
    while (input.length) {
        if (input.match(RDS1)) {
            input = input.replace(RDS1, "");
        } else if (input.match(RDS2)) {
            input = input.replace(RDS2, "/");
        } else if (input.match(RDS3)) {
            input = input.replace(RDS3, "/");
            output.pop();
        } else if (input === "." || input === "..") {
            input = "";
        } else {
            var im = input.match(RDS5);
            if (im) {
                var s = im[0];
                input = input.slice(s.length);
                output.push(s);
            } else {
                throw new Error("Unexpected dot segment condition");
            }
        }
    }
    return output.join("");
}

function serialize(components) {
    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

    var protocol = options.iri ? IRI_PROTOCOL : URI_PROTOCOL;
    var uriTokens = [];
    //find scheme handler
    var schemeHandler = SCHEMES[(options.scheme || components.scheme || "").toLowerCase()];
    //perform scheme specific serialization
    if (schemeHandler && schemeHandler.serialize) schemeHandler.serialize(components, options);
    if (components.host) {
        //if host component is an IPv6 address
        if (protocol.IPV6ADDRESS.test(components.host)) {}
        //TODO: normalize IPv6 address as per RFC 5952

        //if host component is a domain name
        else if (options.domainHost || schemeHandler && schemeHandler.domainHost) {
                //convert IDN via punycode
                try {
                    components.host = !options.iri ? punycode.toASCII(components.host.replace(protocol.PCT_ENCODED, pctDecChars).toLowerCase()) : punycode.toUnicode(components.host);
                } catch (e) {
                    components.error = components.error || "Host's domain name can not be converted to " + (!options.iri ? "ASCII" : "Unicode") + " via punycode: " + e;
                }
            }
    }
    //normalize encoding
    _normalizeComponentEncoding(components, protocol);
    if (options.reference !== "suffix" && components.scheme) {
        uriTokens.push(components.scheme);
        uriTokens.push(":");
    }
    var authority = _recomposeAuthority(components, options);
    if (authority !== undefined) {
        if (options.reference !== "suffix") {
            uriTokens.push("//");
        }
        uriTokens.push(authority);
        if (components.path && components.path.charAt(0) !== "/") {
            uriTokens.push("/");
        }
    }
    if (components.path !== undefined) {
        var s = components.path;
        if (!options.absolutePath && (!schemeHandler || !schemeHandler.absolutePath)) {
            s = removeDotSegments(s);
        }
        if (authority === undefined) {
            s = s.replace(/^\/\//, "/%2F"); //don't allow the path to start with "//"
        }
        uriTokens.push(s);
    }
    if (components.query !== undefined) {
        uriTokens.push("?");
        uriTokens.push(components.query);
    }
    if (components.fragment !== undefined) {
        uriTokens.push("#");
        uriTokens.push(components.fragment);
    }
    return uriTokens.join(""); //merge tokens into a string
}

function resolveComponents(base, relative) {
    var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
    var skipNormalization = arguments[3];

    var target = {};
    if (!skipNormalization) {
        base = parse(serialize(base, options), options); //normalize base components
        relative = parse(serialize(relative, options), options); //normalize relative components
    }
    options = options || {};
    if (!options.tolerant && relative.scheme) {
        target.scheme = relative.scheme;
        //target.authority = relative.authority;
        target.userinfo = relative.userinfo;
        target.host = relative.host;
        target.port = relative.port;
        target.path = removeDotSegments(relative.path || "");
        target.query = relative.query;
    } else {
        if (relative.userinfo !== undefined || relative.host !== undefined || relative.port !== undefined) {
            //target.authority = relative.authority;
            target.userinfo = relative.userinfo;
            target.host = relative.host;
            target.port = relative.port;
            target.path = removeDotSegments(relative.path || "");
            target.query = relative.query;
        } else {
            if (!relative.path) {
                target.path = base.path;
                if (relative.query !== undefined) {
                    target.query = relative.query;
                } else {
                    target.query = base.query;
                }
            } else {
                if (relative.path.charAt(0) === "/") {
                    target.path = removeDotSegments(relative.path);
                } else {
                    if ((base.userinfo !== undefined || base.host !== undefined || base.port !== undefined) && !base.path) {
                        target.path = "/" + relative.path;
                    } else if (!base.path) {
                        target.path = relative.path;
                    } else {
                        target.path = base.path.slice(0, base.path.lastIndexOf("/") + 1) + relative.path;
                    }
                    target.path = removeDotSegments(target.path);
                }
                target.query = relative.query;
            }
            //target.authority = base.authority;
            target.userinfo = base.userinfo;
            target.host = base.host;
            target.port = base.port;
        }
        target.scheme = base.scheme;
    }
    target.fragment = relative.fragment;
    return target;
}

function resolve(baseURI, relativeURI, options) {
    var schemelessOptions = assign({ scheme: 'null' }, options);
    return serialize(resolveComponents(parse(baseURI, schemelessOptions), parse(relativeURI, schemelessOptions), schemelessOptions, true), schemelessOptions);
}

function normalize(uri, options) {
    if (typeof uri === "string") {
        uri = serialize(parse(uri, options), options);
    } else if (typeOf(uri) === "object") {
        uri = parse(serialize(uri, options), options);
    }
    return uri;
}

function equal(uriA, uriB, options) {
    if (typeof uriA === "string") {
        uriA = serialize(parse(uriA, options), options);
    } else if (typeOf(uriA) === "object") {
        uriA = serialize(uriA, options);
    }
    if (typeof uriB === "string") {
        uriB = serialize(parse(uriB, options), options);
    } else if (typeOf(uriB) === "object") {
        uriB = serialize(uriB, options);
    }
    return uriA === uriB;
}

function escapeComponent(str, options) {
    return str && str.toString().replace(!options || !options.iri ? URI_PROTOCOL.ESCAPE : IRI_PROTOCOL.ESCAPE, pctEncChar);
}

function unescapeComponent(str, options) {
    return str && str.toString().replace(!options || !options.iri ? URI_PROTOCOL.PCT_ENCODED : IRI_PROTOCOL.PCT_ENCODED, pctDecChars);
}

var handler = {
    scheme: "http",
    domainHost: true,
    parse: function parse(components, options) {
        //report missing host
        if (!components.host) {
            components.error = components.error || "HTTP URIs must have a host.";
        }
        return components;
    },
    serialize: function serialize(components, options) {
        //normalize the default port
        if (components.port === (String(components.scheme).toLowerCase() !== "https" ? 80 : 443) || components.port === "") {
            components.port = undefined;
        }
        //normalize the empty path
        if (!components.path) {
            components.path = "/";
        }
        //NOTE: We do not parse query strings for HTTP URIs
        //as WWW Form Url Encoded query strings are part of the HTML4+ spec,
        //and not the HTTP spec.
        return components;
    }
};

var handler$1 = {
    scheme: "https",
    domainHost: handler.domainHost,
    parse: handler.parse,
    serialize: handler.serialize
};

var O = {};
var isIRI = true;
//RFC 3986
var UNRESERVED$$ = "[A-Za-z0-9\\-\\.\\_\\~" + (isIRI ? "\\xA0-\\u200D\\u2010-\\u2029\\u202F-\\uD7FF\\uF900-\\uFDCF\\uFDF0-\\uFFEF" : "") + "]";
var HEXDIG$$ = "[0-9A-Fa-f]"; //case-insensitive
var PCT_ENCODED$ = subexp(subexp("%[EFef]" + HEXDIG$$ + "%" + HEXDIG$$ + HEXDIG$$ + "%" + HEXDIG$$ + HEXDIG$$) + "|" + subexp("%[89A-Fa-f]" + HEXDIG$$ + "%" + HEXDIG$$ + HEXDIG$$) + "|" + subexp("%" + HEXDIG$$ + HEXDIG$$)); //expanded
//RFC 5322, except these symbols as per RFC 6068: @ : / ? # [ ] & ; =
//const ATEXT$$ = "[A-Za-z0-9\\!\\#\\$\\%\\&\\'\\*\\+\\-\\/\\=\\?\\^\\_\\`\\{\\|\\}\\~]";
//const WSP$$ = "[\\x20\\x09]";
//const OBS_QTEXT$$ = "[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x7F]";  //(%d1-8 / %d11-12 / %d14-31 / %d127)
//const QTEXT$$ = merge("[\\x21\\x23-\\x5B\\x5D-\\x7E]", OBS_QTEXT$$);  //%d33 / %d35-91 / %d93-126 / obs-qtext
//const VCHAR$$ = "[\\x21-\\x7E]";
//const WSP$$ = "[\\x20\\x09]";
//const OBS_QP$ = subexp("\\\\" + merge("[\\x00\\x0D\\x0A]", OBS_QTEXT$$));  //%d0 / CR / LF / obs-qtext
//const FWS$ = subexp(subexp(WSP$$ + "*" + "\\x0D\\x0A") + "?" + WSP$$ + "+");
//const QUOTED_PAIR$ = subexp(subexp("\\\\" + subexp(VCHAR$$ + "|" + WSP$$)) + "|" + OBS_QP$);
//const QUOTED_STRING$ = subexp('\\"' + subexp(FWS$ + "?" + QCONTENT$) + "*" + FWS$ + "?" + '\\"');
var ATEXT$$ = "[A-Za-z0-9\\!\\$\\%\\'\\*\\+\\-\\^\\_\\`\\{\\|\\}\\~]";
var QTEXT$$ = "[\\!\\$\\%\\'\\(\\)\\*\\+\\,\\-\\.0-9\\<\\>A-Z\\x5E-\\x7E]";
var VCHAR$$ = merge(QTEXT$$, "[\\\"\\\\]");
var SOME_DELIMS$$ = "[\\!\\$\\'\\(\\)\\*\\+\\,\\;\\:\\@]";
var UNRESERVED = new RegExp(UNRESERVED$$, "g");
var PCT_ENCODED = new RegExp(PCT_ENCODED$, "g");
var NOT_LOCAL_PART = new RegExp(merge("[^]", ATEXT$$, "[\\.]", '[\\"]', VCHAR$$), "g");
var NOT_HFNAME = new RegExp(merge("[^]", UNRESERVED$$, SOME_DELIMS$$), "g");
var NOT_HFVALUE = NOT_HFNAME;
function decodeUnreserved(str) {
    var decStr = pctDecChars(str);
    return !decStr.match(UNRESERVED) ? str : decStr;
}
var handler$2 = {
    scheme: "mailto",
    parse: function parse$$1(components, options) {
        var mailtoComponents = components;
        var to = mailtoComponents.to = mailtoComponents.path ? mailtoComponents.path.split(",") : [];
        mailtoComponents.path = undefined;
        if (mailtoComponents.query) {
            var unknownHeaders = false;
            var headers = {};
            var hfields = mailtoComponents.query.split("&");
            for (var x = 0, xl = hfields.length; x < xl; ++x) {
                var hfield = hfields[x].split("=");
                switch (hfield[0]) {
                    case "to":
                        var toAddrs = hfield[1].split(",");
                        for (var _x = 0, _xl = toAddrs.length; _x < _xl; ++_x) {
                            to.push(toAddrs[_x]);
                        }
                        break;
                    case "subject":
                        mailtoComponents.subject = unescapeComponent(hfield[1], options);
                        break;
                    case "body":
                        mailtoComponents.body = unescapeComponent(hfield[1], options);
                        break;
                    default:
                        unknownHeaders = true;
                        headers[unescapeComponent(hfield[0], options)] = unescapeComponent(hfield[1], options);
                        break;
                }
            }
            if (unknownHeaders) mailtoComponents.headers = headers;
        }
        mailtoComponents.query = undefined;
        for (var _x2 = 0, _xl2 = to.length; _x2 < _xl2; ++_x2) {
            var addr = to[_x2].split("@");
            addr[0] = unescapeComponent(addr[0]);
            if (!options.unicodeSupport) {
                //convert Unicode IDN -> ASCII IDN
                try {
                    addr[1] = punycode.toASCII(unescapeComponent(addr[1], options).toLowerCase());
                } catch (e) {
                    mailtoComponents.error = mailtoComponents.error || "Email address's domain name can not be converted to ASCII via punycode: " + e;
                }
            } else {
                addr[1] = unescapeComponent(addr[1], options).toLowerCase();
            }
            to[_x2] = addr.join("@");
        }
        return mailtoComponents;
    },
    serialize: function serialize$$1(mailtoComponents, options) {
        var components = mailtoComponents;
        var to = toArray(mailtoComponents.to);
        if (to) {
            for (var x = 0, xl = to.length; x < xl; ++x) {
                var toAddr = String(to[x]);
                var atIdx = toAddr.lastIndexOf("@");
                var localPart = toAddr.slice(0, atIdx).replace(PCT_ENCODED, decodeUnreserved).replace(PCT_ENCODED, toUpperCase).replace(NOT_LOCAL_PART, pctEncChar);
                var domain = toAddr.slice(atIdx + 1);
                //convert IDN via punycode
                try {
                    domain = !options.iri ? punycode.toASCII(unescapeComponent(domain, options).toLowerCase()) : punycode.toUnicode(domain);
                } catch (e) {
                    components.error = components.error || "Email address's domain name can not be converted to " + (!options.iri ? "ASCII" : "Unicode") + " via punycode: " + e;
                }
                to[x] = localPart + "@" + domain;
            }
            components.path = to.join(",");
        }
        var headers = mailtoComponents.headers = mailtoComponents.headers || {};
        if (mailtoComponents.subject) headers["subject"] = mailtoComponents.subject;
        if (mailtoComponents.body) headers["body"] = mailtoComponents.body;
        var fields = [];
        for (var name in headers) {
            if (headers[name] !== O[name]) {
                fields.push(name.replace(PCT_ENCODED, decodeUnreserved).replace(PCT_ENCODED, toUpperCase).replace(NOT_HFNAME, pctEncChar) + "=" + headers[name].replace(PCT_ENCODED, decodeUnreserved).replace(PCT_ENCODED, toUpperCase).replace(NOT_HFVALUE, pctEncChar));
            }
        }
        if (fields.length) {
            components.query = fields.join("&");
        }
        return components;
    }
};

var URN_PARSE = /^([^\:]+)\:(.*)/;
//RFC 2141
var handler$3 = {
    scheme: "urn",
    parse: function parse$$1(components, options) {
        var matches = components.path && components.path.match(URN_PARSE);
        var urnComponents = components;
        if (matches) {
            var scheme = options.scheme || urnComponents.scheme || "urn";
            var nid = matches[1].toLowerCase();
            var nss = matches[2];
            var urnScheme = scheme + ":" + (options.nid || nid);
            var schemeHandler = SCHEMES[urnScheme];
            urnComponents.nid = nid;
            urnComponents.nss = nss;
            urnComponents.path = undefined;
            if (schemeHandler) {
                urnComponents = schemeHandler.parse(urnComponents, options);
            }
        } else {
            urnComponents.error = urnComponents.error || "URN can not be parsed.";
        }
        return urnComponents;
    },
    serialize: function serialize$$1(urnComponents, options) {
        var scheme = options.scheme || urnComponents.scheme || "urn";
        var nid = urnComponents.nid;
        var urnScheme = scheme + ":" + (options.nid || nid);
        var schemeHandler = SCHEMES[urnScheme];
        if (schemeHandler) {
            urnComponents = schemeHandler.serialize(urnComponents, options);
        }
        var uriComponents = urnComponents;
        var nss = urnComponents.nss;
        uriComponents.path = (nid || options.nid) + ":" + nss;
        return uriComponents;
    }
};

var UUID = /^[0-9A-Fa-f]{8}(?:\-[0-9A-Fa-f]{4}){3}\-[0-9A-Fa-f]{12}$/;
//RFC 4122
var handler$4 = {
    scheme: "urn:uuid",
    parse: function parse(urnComponents, options) {
        var uuidComponents = urnComponents;
        uuidComponents.uuid = uuidComponents.nss;
        uuidComponents.nss = undefined;
        if (!options.tolerant && (!uuidComponents.uuid || !uuidComponents.uuid.match(UUID))) {
            uuidComponents.error = uuidComponents.error || "UUID is not valid.";
        }
        return uuidComponents;
    },
    serialize: function serialize(uuidComponents, options) {
        var urnComponents = uuidComponents;
        //normalize UUID
        urnComponents.nss = (uuidComponents.uuid || "").toLowerCase();
        return urnComponents;
    }
};

SCHEMES[handler.scheme] = handler;
SCHEMES[handler$1.scheme] = handler$1;
SCHEMES[handler$2.scheme] = handler$2;
SCHEMES[handler$3.scheme] = handler$3;
SCHEMES[handler$4.scheme] = handler$4;

exports.SCHEMES = SCHEMES;
exports.pctEncChar = pctEncChar;
exports.pctDecChars = pctDecChars;
exports.parse = parse;
exports.removeDotSegments = removeDotSegments;
exports.serialize = serialize;
exports.resolveComponents = resolveComponents;
exports.resolve = resolve;
exports.normalize = normalize;
exports.equal = equal;
exports.escapeComponent = escapeComponent;
exports.unescapeComponent = unescapeComponent;

Object.defineProperty(exports, '__esModule', { value: true });

})));


},{}],27:[function(require,module,exports){
"use strict";

var _interopRequireWildcard = require("@babel/runtime/helpers/interopRequireWildcard");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.v1 = exports["default"] = void 0;

var v1 = _interopRequireWildcard(require("./v1/index"));

exports.v1 = v1;

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
var _default = v1;
exports["default"] = _default;

},{"./v1/index":31,"@babel/runtime/helpers/interopRequireWildcard":12}],28:[function(require,module,exports){
"use strict";

var _interopRequireWildcard = require("@babel/runtime/helpers/interopRequireWildcard");

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = exports.WRITE = exports.READ = exports.Driver = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _session = _interopRequireDefault(require("./session"));

var _pool = _interopRequireDefault(require("./internal/pool"));

var _connection = _interopRequireDefault(require("./internal/connection"));

var _error = require("./error");

var _connectionProviders = require("./internal/connection-providers");

var _bookmark = _interopRequireDefault(require("./internal/bookmark"));

var _connectivityVerifier = _interopRequireDefault(require("./internal/connectivity-verifier"));

var _poolConfig = _interopRequireWildcard(require("./internal/pool-config"));

var _logger = _interopRequireDefault(require("./internal/logger"));

var _connectionErrorHandler = _interopRequireDefault(require("./internal/connection-error-handler"));

var _constants = require("./internal/constants");

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
var DEFAULT_MAX_CONNECTION_LIFETIME = 60 * 60 * 1000; // 1 hour

/**
 * Constant that represents read session access mode.
 * Should be used like this: `driver.session(neo4j.session.READ)`.
 * @type {string}
 */

var READ = _constants.ACCESS_MODE_READ;
/**
 * Constant that represents write session access mode.
 * Should be used like this: `driver.session(neo4j.session.WRITE)`.
 * @type {string}
 */

exports.READ = READ;
var WRITE = _constants.ACCESS_MODE_WRITE;
exports.WRITE = WRITE;
var idGenerator = 0;
/**
 * A driver maintains one or more {@link Session}s with a remote
 * Neo4j instance. Through the {@link Session}s you can send statements
 * and retrieve results from the database.
 *
 * Drivers are reasonably expensive to create - you should strive to keep one
 * driver instance around per Neo4j Instance you connect to.
 *
 * @access public
 */

var Driver =
/*#__PURE__*/
function () {
  /**
   * You should not be calling this directly, instead use {@link driver}.
   * @constructor
   * @param {ServerAddress} address
   * @param {string} userAgent
   * @param {object} authToken
   * @param {object} config
   * @protected
   */
  function Driver(address, userAgent) {
    var authToken = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
    var config = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : {};
    (0, _classCallCheck2["default"])(this, Driver);
    sanitizeConfig(config);
    this._id = idGenerator++;
    this._address = address;
    this._userAgent = userAgent;
    this._openConnections = {};
    this._authToken = authToken;
    this._config = config;
    this._log = _logger["default"].create(config);
    this._pool = new _pool["default"]({
      create: this._createConnection.bind(this),
      destroy: this._destroyConnection.bind(this),
      validate: this._validateConnection.bind(this),
      installIdleObserver: this._installIdleObserverOnConnection.bind(this),
      removeIdleObserver: this._removeIdleObserverOnConnection.bind(this),
      config: _poolConfig["default"].fromDriverConfig(config),
      log: this._log
    });
    /**
     * Reference to the connection provider. Initialized lazily by {@link _getOrCreateConnectionProvider}.
     * @type {ConnectionProvider}
     * @protected
     */

    this._connectionProvider = null;
    this._onCompleted = null;

    this._afterConstruction();
  }
  /**
   * @protected
   */


  (0, _createClass2["default"])(Driver, [{
    key: "_afterConstruction",
    value: function _afterConstruction() {
      this._log.info("Direct driver ".concat(this._id, " created for server address ").concat(this._address));
    }
    /**
     * Get the installed connectivity verification callback.
     * @return {null|function}
     * @deprecated driver can be used directly once instantiated, use of this callback is not required.
     */

  }, {
    key: "_createConnection",

    /**
     * Create a new connection and initialize it.
     * @return {Promise<Connection>} promise resolved with a new connection or rejected when failed to connect.
     * @access private
     */
    value: function _createConnection(address, release) {
      var _this = this;

      var connection = _connection["default"].create(address, this._config, this._createConnectionErrorHandler(), this._log);

      connection._release = function () {
        return release(address, connection);
      };

      this._openConnections[connection.id] = connection;
      return connection.connect(this._userAgent, this._authToken)["catch"](function (error) {
        if (_this.onError) {
          // notify Driver.onError callback about connection initialization errors
          _this.onError(error);
        } // let's destroy this connection


        _this._destroyConnection(connection); // propagate the error because connection failed to connect / initialize


        throw error;
      });
    }
    /**
     * Check that a connection is usable
     * @return {boolean} true if the connection is open
     * @access private
     **/

  }, {
    key: "_validateConnection",
    value: function _validateConnection(conn) {
      if (!conn.isOpen()) {
        return false;
      }

      var maxConnectionLifetime = this._config.maxConnectionLifetime;
      var lifetime = Date.now() - conn.creationTimestamp;
      return lifetime <= maxConnectionLifetime;
    }
  }, {
    key: "_installIdleObserverOnConnection",
    value: function _installIdleObserverOnConnection(conn, observer) {
      conn._queueObserver(observer);
    }
  }, {
    key: "_removeIdleObserverOnConnection",
    value: function _removeIdleObserverOnConnection(conn) {
      conn._updateCurrentObserver();
    }
    /**
     * Dispose of a connection.
     * @return {Connection} the connection to dispose.
     * @access private
     */

  }, {
    key: "_destroyConnection",
    value: function _destroyConnection(conn) {
      delete this._openConnections[conn.id];
      conn.close();
    }
    /**
     * Acquire a session to communicate with the database. The session will
     * borrow connections from the underlying connection pool as required and
     * should be considered lightweight and disposable.
     *
     * This comes with some responsibility - make sure you always call
     * {@link close} when you are done using a session, and likewise,
     * make sure you don't close your session before you are done using it. Once
     * it is closed, the underlying connection will be released to the connection
     * pool and made available for others to use.
     *
     * @param {string} [mode=WRITE] the access mode of this session, allowed values are {@link READ} and {@link WRITE}.
     * @param {string|string[]} [bookmarkOrBookmarks=null] the initial reference or references to some previous
     * transactions. Value is optional and absence indicates that that the bookmarks do not exist or are unknown.
     * @return {Session} new session.
     */

  }, {
    key: "session",
    value: function session(mode, bookmarkOrBookmarks) {
      var sessionMode = Driver._validateSessionMode(mode);

      var connectionProvider = this._getOrCreateConnectionProvider();

      var bookmark = bookmarkOrBookmarks ? new _bookmark["default"](bookmarkOrBookmarks) : _bookmark["default"].empty();
      return new _session["default"](sessionMode, connectionProvider, bookmark, this._config);
    }
  }, {
    key: "_createConnectionProvider",
    // Extension point
    value: function _createConnectionProvider(address, connectionPool, driverOnErrorCallback) {
      return new _connectionProviders.DirectConnectionProvider(address, connectionPool, driverOnErrorCallback);
    } // Extension point

  }, {
    key: "_createConnectionErrorHandler",
    value: function _createConnectionErrorHandler() {
      return new _connectionErrorHandler["default"](_error.SERVICE_UNAVAILABLE);
    }
  }, {
    key: "_getOrCreateConnectionProvider",
    value: function _getOrCreateConnectionProvider() {
      if (!this._connectionProvider) {
        var driverOnErrorCallback = this._driverOnErrorCallback.bind(this);

        this._connectionProvider = this._createConnectionProvider(this._address, this._pool, driverOnErrorCallback);
      }

      return this._connectionProvider;
    }
  }, {
    key: "_driverOnErrorCallback",
    value: function _driverOnErrorCallback(error) {
      var userDefinedOnErrorCallback = this.onError;

      if (userDefinedOnErrorCallback && error.code === _error.SERVICE_UNAVAILABLE) {
        userDefinedOnErrorCallback(error);
      } else {// we don't need to tell the driver about this error
      }
    }
    /**
     * Close all open sessions and other associated resources. You should
     * make sure to use this when you are done with this driver instance.
     * @return undefined
     */

  }, {
    key: "close",
    value: function close() {
      this._log.info("Driver ".concat(this._id, " closing"));

      try {
        // purge all idle connections in the connection pool
        this._pool.purgeAll();
      } finally {
        // then close all connections driver has ever created
        // it is needed to close connections that are active right now and are acquired from the pool
        for (var connectionId in this._openConnections) {
          if (this._openConnections.hasOwnProperty(connectionId)) {
            this._openConnections[connectionId].close();
          }
        }
      }
    }
  }, {
    key: "onCompleted",
    get: function get() {
      return this._onCompleted;
    }
    /**
     * Install a connectivity verification callback.
     * @param {null|function} callback the new function to be notified about successful connection.
     * @deprecated driver can be used directly once instantiated, use of this callback is not required.
     */
    ,
    set: function set(callback) {
      this._onCompleted = callback;

      if (this._onCompleted) {
        var connectionProvider = this._getOrCreateConnectionProvider();

        var connectivityVerifier = new _connectivityVerifier["default"](connectionProvider, this._onCompleted);
        connectivityVerifier.verify();
      }
    }
  }], [{
    key: "_validateSessionMode",
    value: function _validateSessionMode(rawMode) {
      var mode = rawMode || WRITE;

      if (mode !== _constants.ACCESS_MODE_READ && mode !== _constants.ACCESS_MODE_WRITE) {
        throw (0, _error.newError)('Illegal session mode ' + mode);
      }

      return mode;
    }
  }]);
  return Driver;
}();
/**
 * @private
 */


exports.Driver = Driver;

function sanitizeConfig(config) {
  config.maxConnectionLifetime = sanitizeIntValue(config.maxConnectionLifetime, DEFAULT_MAX_CONNECTION_LIFETIME);
  config.maxConnectionPoolSize = sanitizeIntValue(config.maxConnectionPoolSize, _poolConfig.DEFAULT_MAX_SIZE);
  config.connectionAcquisitionTimeout = sanitizeIntValue(config.connectionAcquisitionTimeout, _poolConfig.DEFAULT_ACQUISITION_TIMEOUT);
}

function sanitizeIntValue(rawValue, defaultWhenAbsent) {
  var sanitizedValue = parseInt(rawValue, 10);

  if (sanitizedValue > 0 || sanitizedValue === 0) {
    return sanitizedValue;
  } else if (sanitizedValue < 0) {
    return Number.MAX_SAFE_INTEGER;
  } else {
    return defaultWhenAbsent;
  }
}

var _default = Driver;
exports["default"] = _default;

},{"./error":29,"./internal/bookmark":36,"./internal/connection":49,"./internal/connection-error-handler":46,"./internal/connection-providers":48,"./internal/connectivity-verifier":50,"./internal/constants":51,"./internal/logger":59,"./internal/pool":63,"./internal/pool-config":62,"./session":85,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/interopRequireWildcard":12}],29:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.newError = newError;
exports.PROTOCOL_ERROR = exports.SESSION_EXPIRED = exports.SERVICE_UNAVAILABLE = exports.Neo4jError = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _possibleConstructorReturn2 = _interopRequireDefault(require("@babel/runtime/helpers/possibleConstructorReturn"));

var _getPrototypeOf2 = _interopRequireDefault(require("@babel/runtime/helpers/getPrototypeOf"));

var _inherits2 = _interopRequireDefault(require("@babel/runtime/helpers/inherits"));

var _wrapNativeSuper2 = _interopRequireDefault(require("@babel/runtime/helpers/wrapNativeSuper"));

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
// A common place for constructing error objects, to keep them
// uniform across the driver surface.

/**
 * Error code representing complete loss of service. Used by {@link Neo4jError#code}.
 * @type {string}
 */
var SERVICE_UNAVAILABLE = 'ServiceUnavailable';
/**
 * Error code representing transient loss of service. Used by {@link Neo4jError#code}.
 * @type {string}
 */

exports.SERVICE_UNAVAILABLE = SERVICE_UNAVAILABLE;
var SESSION_EXPIRED = 'SessionExpired';
/**
 * Error code representing serialization/deserialization issue in the Bolt protocol. Used by {@link Neo4jError#code}.
 * @type {string}
 */

exports.SESSION_EXPIRED = SESSION_EXPIRED;
var PROTOCOL_ERROR = 'ProtocolError';
exports.PROTOCOL_ERROR = PROTOCOL_ERROR;

function newError(message) {
  var code = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'N/A';
  // TODO: Idea is that we can check the code here and throw sub-classes
  // of Neo4jError as appropriate
  return new Neo4jError(message, code);
}
/**
 * Class for all errors thrown/returned by the driver.
 */


var Neo4jError =
/*#__PURE__*/
function (_Error) {
  (0, _inherits2["default"])(Neo4jError, _Error);

  /**
   * @constructor
   * @param {string} message - The error message.
   * @param {string} code - Optional error code. Will be populated when error originates in the database.
   */
  function Neo4jError(message) {
    var _this;

    var code = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'N/A';
    (0, _classCallCheck2["default"])(this, Neo4jError);
    _this = (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(Neo4jError).call(this, message));
    _this.message = message;
    _this.code = code;
    _this.name = 'Neo4jError';
    return _this;
  }

  return Neo4jError;
}((0, _wrapNativeSuper2["default"])(Error));

exports.Neo4jError = Neo4jError;

},{"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/getPrototypeOf":9,"@babel/runtime/helpers/inherits":10,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/possibleConstructorReturn":18,"@babel/runtime/helpers/wrapNativeSuper":24}],30:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.PathSegment = exports.Path = exports.UnboundRelationship = exports.Relationship = exports.Node = void 0;

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

/**
 * Class for Node Type.
 */
var Node =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {Integer} identity - Unique identity
   * @param {Array<string>} labels - Array for all labels
   * @param {Object} properties - Map with node properties
   */
  function Node(identity, labels, properties) {
    (0, _classCallCheck2["default"])(this, Node);
    this.identity = identity;
    this.labels = labels;
    this.properties = properties;
  }

  (0, _createClass2["default"])(Node, [{
    key: "toString",
    value: function toString() {
      var s = '(' + this.identity;

      for (var i = 0; i < this.labels.length; i++) {
        s += ':' + this.labels[i];
      }

      var keys = Object.keys(this.properties);

      if (keys.length > 0) {
        s += ' {';

        for (var _i = 0; _i < keys.length; _i++) {
          if (_i > 0) s += ',';
          s += keys[_i] + ':' + JSON.stringify(this.properties[keys[_i]]);
        }

        s += '}';
      }

      s += ')';
      return s;
    }
  }]);
  return Node;
}();
/**
 * Class for Relationship Type.
 */


exports.Node = Node;

var Relationship =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {Integer} identity - Unique identity
   * @param {Integer} start - Identity of start Node
   * @param {Integer} end - Identity of end Node
   * @param {string} type - Relationship type
   * @param {Object} properties - Map with relationship properties
   */
  function Relationship(identity, start, end, type, properties) {
    (0, _classCallCheck2["default"])(this, Relationship);
    this.identity = identity;
    this.start = start;
    this.end = end;
    this.type = type;
    this.properties = properties;
  }

  (0, _createClass2["default"])(Relationship, [{
    key: "toString",
    value: function toString() {
      var s = '(' + this.start + ')-[:' + this.type;
      var keys = Object.keys(this.properties);

      if (keys.length > 0) {
        s += ' {';

        for (var i = 0; i < keys.length; i++) {
          if (i > 0) s += ',';
          s += keys[i] + ':' + JSON.stringify(this.properties[keys[i]]);
        }

        s += '}';
      }

      s += ']->(' + this.end + ')';
      return s;
    }
  }]);
  return Relationship;
}();
/**
 * Class for UnboundRelationship Type.
 * @access private
 */


exports.Relationship = Relationship;

var UnboundRelationship =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {Integer} identity - Unique identity
   * @param {string} type - Relationship type
   * @param {Object} properties - Map with relationship properties
   */
  function UnboundRelationship(identity, type, properties) {
    (0, _classCallCheck2["default"])(this, UnboundRelationship);
    this.identity = identity;
    this.type = type;
    this.properties = properties;
  }
  /**
   * Bind relationship
   * @param {Integer} start - Identity of start node
   * @param {Integer} end - Identity of end node
   * @return {Relationship} - Created relationship
   */


  (0, _createClass2["default"])(UnboundRelationship, [{
    key: "bind",
    value: function bind(start, end) {
      return new Relationship(this.identity, start, end, this.type, this.properties);
    }
  }, {
    key: "toString",
    value: function toString() {
      var s = '-[:' + this.type;
      var keys = Object.keys(this.properties);

      if (keys.length > 0) {
        s += ' {';

        for (var i = 0; i < keys.length; i++) {
          if (i > 0) s += ',';
          s += keys[i] + ':' + JSON.stringify(this.properties[keys[i]]);
        }

        s += '}';
      }

      s += ']->';
      return s;
    }
  }]);
  return UnboundRelationship;
}();
/**
 * Class for PathSegment Type.
 */


exports.UnboundRelationship = UnboundRelationship;

var PathSegment =
/**
 * @constructor
 * @param {Node} start - start node
 * @param {Relationship} rel - relationship that connects start and end node
 * @param {Node} end - end node
 */
function PathSegment(start, rel, end) {
  (0, _classCallCheck2["default"])(this, PathSegment);
  this.start = start;
  this.relationship = rel;
  this.end = end;
};
/**
 * Class for Path Type.
 */


exports.PathSegment = PathSegment;

var Path =
/**
 * @constructor
 * @param {Node} start  - start node
 * @param {Node} end - end node
 * @param {Array<PathSegment>} segments - Array of Segments
 */
function Path(start, end, segments) {
  (0, _classCallCheck2["default"])(this, Path);
  this.start = start;
  this.end = end;
  this.segments = segments;
  this.length = segments.length;
};

exports.Path = Path;

},{"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11}],31:[function(require,module,exports){
(function (global){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

var _interopRequireWildcard = require("@babel/runtime/helpers/interopRequireWildcard");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.driver = driver;
Object.defineProperty(exports, "int", {
  enumerable: true,
  get: function get() {
    return _integer["int"];
  }
});
Object.defineProperty(exports, "isInt", {
  enumerable: true,
  get: function get() {
    return _integer.isInt;
  }
});
Object.defineProperty(exports, "Neo4jError", {
  enumerable: true,
  get: function get() {
    return _error.Neo4jError;
  }
});
Object.defineProperty(exports, "isPoint", {
  enumerable: true,
  get: function get() {
    return _spatialTypes.isPoint;
  }
});
Object.defineProperty(exports, "isDate", {
  enumerable: true,
  get: function get() {
    return _temporalTypes.isDate;
  }
});
Object.defineProperty(exports, "isDateTime", {
  enumerable: true,
  get: function get() {
    return _temporalTypes.isDateTime;
  }
});
Object.defineProperty(exports, "isDuration", {
  enumerable: true,
  get: function get() {
    return _temporalTypes.isDuration;
  }
});
Object.defineProperty(exports, "isLocalDateTime", {
  enumerable: true,
  get: function get() {
    return _temporalTypes.isLocalDateTime;
  }
});
Object.defineProperty(exports, "isLocalTime", {
  enumerable: true,
  get: function get() {
    return _temporalTypes.isLocalTime;
  }
});
Object.defineProperty(exports, "isTime", {
  enumerable: true,
  get: function get() {
    return _temporalTypes.isTime;
  }
});
exports["default"] = exports.temporal = exports.spatial = exports.error = exports.session = exports.types = exports.logging = exports.auth = exports.integer = void 0;

var _integer = _interopRequireWildcard(require("./integer"));

var _graphTypes = require("./graph-types");

var _error = require("./error");

var _result = _interopRequireDefault(require("./result"));

var _resultSummary = _interopRequireDefault(require("./result-summary"));

var _record = _interopRequireDefault(require("./record"));

var _driver = require("./driver");

var _routingDriver = _interopRequireDefault(require("./routing-driver"));

var _version = _interopRequireDefault(require("../version"));

var _util = require("./internal/util");

var _urlUtil = _interopRequireDefault(require("./internal/url-util"));

var _httpDriver = _interopRequireDefault(require("./internal/http/http-driver"));

var _spatialTypes = require("./spatial-types");

var _temporalTypes = require("./temporal-types");

var _serverAddress = _interopRequireDefault(require("./internal/server-address"));

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
 * @property {function(username: string, password: string, realm: ?string)} basic the function to create a
 * basic authentication token.
 * @property {function(base64EncodedTicket: string)} kerberos the function to create a Kerberos authentication token.
 * Accepts a single string argument - base64 encoded Kerberos ticket.
 * @property {function(principal: string, credentials: string, realm: string, scheme: string, parameters: ?object)} custom
 * the function to create a custom authentication token.
 */
var auth = {
  basic: function basic(username, password) {
    var realm = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : undefined;

    if (realm) {
      return {
        scheme: 'basic',
        principal: username,
        credentials: password,
        realm: realm
      };
    } else {
      return {
        scheme: 'basic',
        principal: username,
        credentials: password
      };
    }
  },
  kerberos: function kerberos(base64EncodedTicket) {
    return {
      scheme: 'kerberos',
      principal: '',
      // This empty string is required for backwards compatibility.
      credentials: base64EncodedTicket
    };
  },
  custom: function custom(principal, credentials, realm, scheme) {
    var parameters = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : undefined;

    if (parameters) {
      return {
        scheme: scheme,
        principal: principal,
        credentials: credentials,
        realm: realm,
        parameters: parameters
      };
    } else {
      return {
        scheme: scheme,
        principal: principal,
        credentials: credentials,
        realm: realm
      };
    }
  }
};
exports.auth = auth;
var USER_AGENT = 'neo4j-javascript/' + _version["default"];
/**
 * Object containing predefined logging configurations. These are expected to be used as values of the driver config's `logging` property.
 * @property {function(level: ?string): object} console the function to create a logging config that prints all messages to `console.log` with
 * timestamp, level and message. It takes an optional `level` parameter which represents the maximum log level to be logged. Default value is 'info'.
 */

var logging = {
  console: function (_console) {
    function console(_x) {
      return _console.apply(this, arguments);
    }

    console.toString = function () {
      return _console.toString();
    };

    return console;
  }(function (level) {
    return {
      level: level,
      logger: function logger(level, message) {
        return console.log("".concat(global.Date.now(), " ").concat(level.toUpperCase(), " ").concat(message));
      }
    };
  })
  /**
   * Construct a new Neo4j Driver. This is your main entry point for this
   * library.
   *
   * ## Configuration
   *
   * This function optionally takes a configuration argument. Available configuration
   * options are as follows:
   *
   *     {
   *       // Encryption level: ENCRYPTION_ON or ENCRYPTION_OFF.
   *       encrypted: ENCRYPTION_ON|ENCRYPTION_OFF
   *
   *       // Trust strategy to use if encryption is enabled. There is no mode to disable
   *       // trust other than disabling encryption altogether. The reason for
   *       // this is that if you don't know who you are talking to, it is easy for an
   *       // attacker to hijack your encrypted connection, rendering encryption pointless.
   *       //
   *       // TRUST_ALL_CERTIFICATES is the default choice for NodeJS deployments. It only requires
   *       // new host to provide a certificate and does no verification of the provided certificate.
   *       //
   *       // TRUST_ON_FIRST_USE is available for modern NodeJS deployments, and works
   *       // similarly to how `ssl` works - the first time we connect to a new host,
   *       // we remember the certificate they use. If the certificate ever changes, we
   *       // assume it is an attempt to hijack the connection and require manual intervention.
   *       // This means that by default, connections "just work" while still giving you
   *       // good encrypted protection.
   *       //
   *       // TRUST_CUSTOM_CA_SIGNED_CERTIFICATES is the classic approach to trust verification -
   *       // whenever we establish an encrypted connection, we ensure the host is using
   *       // an encryption certificate that is in, or is signed by, a certificate listed
   *       // as trusted. In the web bundle, this list of trusted certificates is maintained
   *       // by the web browser. In NodeJS, you configure the list with the next config option.
   *       //
   *       // TRUST_SYSTEM_CA_SIGNED_CERTIFICATES means that you trust whatever certificates
   *       // are in the default certificate chain of th
   *       trust: "TRUST_ALL_CERTIFICATES" | "TRUST_ON_FIRST_USE" | "TRUST_SIGNED_CERTIFICATES" |
   *       "TRUST_CUSTOM_CA_SIGNED_CERTIFICATES" | "TRUST_SYSTEM_CA_SIGNED_CERTIFICATES",
   *
   *       // List of one or more paths to trusted encryption certificates. This only
   *       // works in the NodeJS bundle, and only matters if you use "TRUST_CUSTOM_CA_SIGNED_CERTIFICATES".
   *       // The certificate files should be in regular X.509 PEM format.
   *       // For instance, ['./trusted.pem']
   *       trustedCertificates: [],
   *
   *       // Path to a file where the driver saves hosts it has seen in the past, this is
   *       // very similar to the ssl tool's known_hosts file. Each time we connect to a
   *       // new host, a hash of their certificate is stored along with the domain name and
   *       // port, and this is then used to verify the host certificate does not change.
   *       // This setting has no effect unless TRUST_ON_FIRST_USE is enabled.
   *       knownHosts:"~/.neo4j/known_hosts",
   *
   *       // The max number of connections that are allowed idle in the pool at any time.
   *       // Connection will be destroyed if this threshold is exceeded.
   *       // **Deprecated:** please use `maxConnectionPoolSize` instead.
   *       connectionPoolSize: 100,
   *
   *       // The maximum total number of connections allowed to be managed by the connection pool, per host.
   *       // This includes both in-use and idle connections. No maximum connection pool size is imposed
   *       // by default.
   *       maxConnectionPoolSize: 100,
   *
   *       // The maximum allowed lifetime for a pooled connection in milliseconds. Pooled connections older than this
   *       // threshold will be closed and removed from the pool. Such discarding happens during connection acquisition
   *       // so that new session is never backed by an old connection. Setting this option to a low value will cause
   *       // a high connection churn and might result in a performance hit. It is recommended to set maximum lifetime
   *       // to a slightly smaller value than the one configured in network equipment (load balancer, proxy, firewall,
   *       // etc. can also limit maximum connection lifetime). No maximum lifetime limit is imposed by default. Zero
   *       // and negative values result in lifetime not being checked.
   *       maxConnectionLifetime: 60 * 60 * 1000, // 1 hour
   *
   *       // The maximum amount of time to wait to acquire a connection from the pool (to either create a new
   *       // connection or borrow an existing one.
   *       connectionAcquisitionTimeout: 60000, // 1 minute
   *
   *       // Specify the maximum time in milliseconds transactions are allowed to retry via
   *       // `Session#readTransaction()` and `Session#writeTransaction()` functions.
   *       // These functions will retry the given unit of work on `ServiceUnavailable`, `SessionExpired` and transient
   *       // errors with exponential backoff using initial delay of 1 second.
   *       // Default value is 30000 which is 30 seconds.
   *       maxTransactionRetryTime: 30000, // 30 seconds
   *
   *       // Provide an alternative load balancing strategy for the routing driver to use.
   *       // Driver uses "least_connected" by default.
   *       // **Note:** We are experimenting with different strategies. This could be removed in the next minor
   *       // version.
   *       loadBalancingStrategy: "least_connected" | "round_robin",
   *
   *       // Specify socket connection timeout in milliseconds. Numeric values are expected. Negative and zero values
   *       // result in no timeout being applied. Connection establishment will be then bound by the timeout configured
   *       // on the operating system level. Default value is 5000, which is 5 seconds.
   *       connectionTimeout: 5000, // 5 seconds
   *
   *       // Make this driver always return native JavaScript numbers for integer values, instead of the
   *       // dedicated {@link Integer} class. Values that do not fit in native number bit range will be represented as
   *       // `Number.NEGATIVE_INFINITY` or `Number.POSITIVE_INFINITY`.
   *       // **Warning:** ResultSummary It is not always safe to enable this setting when JavaScript applications are not the only ones
   *       // interacting with the database. Stored numbers might in such case be not representable by native
   *       // {@link Number} type and thus driver will return lossy values. This might also happen when data was
   *       // initially imported using neo4j import tool and contained numbers larger than
   *       // `Number.MAX_SAFE_INTEGER`. Driver will then return positive infinity, which is lossy.
   *       // Default value for this option is `false` because native JavaScript numbers might result
   *       // in loss of precision in the general case.
   *       disableLosslessIntegers: false,
   *
   *       // Specify the logging configuration for the driver. Object should have two properties `level` and `logger`.
   *       //
   *       // Property `level` represents the logging level which should be one of: 'error', 'warn', 'info' or 'debug'. This property is optional and
   *       // its default value is 'info'. Levels have priorities: 'error': 0, 'warn': 1, 'info': 2, 'debug': 3. Enabling a certain level also enables all
   *       // levels with lower priority. For example: 'error', 'warn' and 'info' will be logged when 'info' level is configured.
   *       //
   *       // Property `logger` represents the logging function which will be invoked for every log call with an acceptable level. The function should
   *       // take two string arguments `level` and `message`. The function should not execute any blocking or long-running operations
   *       // because it is often executed on a hot path.
   *       //
   *       // No logging is done by default. See `neo4j.logging` object that contains predefined logging implementations.
   *       logging: {
   *         level: 'info',
   *         logger: (level, message) => console.log(level + ' ' + message)
   *       },
   *
   *       // Specify a custom server address resolver function used by the routing driver to resolve the initial address used to create the driver.
   *       // Such resolution happens:
   *       //  * during the very first rediscovery when driver is created
   *       //  * when all the known routers from the current routing table have failed and driver needs to fallback to the initial address
   *       //
   *       // In NodeJS environment driver defaults to performing a DNS resolution of the initial address using 'dns' module.
   *       // In browser environment driver uses the initial address as-is.
   *       // Value should be a function that takes a single string argument - the initial address. It should return an array of new addresses.
   *       // Address is a string of shape '<host>:<port>'. Provided function can return either a Promise resolved with an array of addresses
   *       // or array of addresses directly.
   *       resolver: function(address) {
   *         return ['127.0.0.1:8888', 'fallback.db.com:7687'];
   *       },
   *     }
   *
   * @param {string} url The URL for the Neo4j database, for instance "bolt://localhost"
   * @param {Map<String,String>} authToken Authentication credentials. See {@link auth} for helpers.
   * @param {Object} config Configuration object. See the configuration section above for details.
   * @returns {Driver}
   */

};
exports.logging = logging;

function driver(url, authToken) {
  var config = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
  (0, _util.assertString)(url, 'Bolt URL');

  var parsedUrl = _urlUtil["default"].parseDatabaseUrl(url);

  if (['bolt+routing', 'neo4j'].indexOf(parsedUrl.scheme) !== -1) {
    return new _routingDriver["default"](_serverAddress["default"].fromUrl(parsedUrl.hostAndPort), parsedUrl.query, USER_AGENT, authToken, config);
  } else if (parsedUrl.scheme === 'bolt') {
    if (!(0, _util.isEmptyObjectOrNull)(parsedUrl.query)) {
      throw new Error("Parameters are not supported with scheme 'bolt'. Given URL: '".concat(url, "'"));
    }

    return new _driver.Driver(_serverAddress["default"].fromUrl(parsedUrl.hostAndPort), USER_AGENT, authToken, config);
  } else if (parsedUrl.scheme === 'http' || parsedUrl.scheme === 'https') {
    return new _httpDriver["default"](parsedUrl, USER_AGENT, authToken, config);
  } else {
    throw new Error("Unknown scheme: ".concat(parsedUrl.scheme));
  }
}
/**
 * Object containing constructors for all neo4j types.
 */


var types = {
  Node: _graphTypes.Node,
  Relationship: _graphTypes.Relationship,
  UnboundRelationship: _graphTypes.UnboundRelationship,
  PathSegment: _graphTypes.PathSegment,
  Path: _graphTypes.Path,
  Result: _result["default"],
  ResultSummary: _resultSummary["default"],
  Record: _record["default"],
  Point: _spatialTypes.Point,
  Date: _temporalTypes.Date,
  DateTime: _temporalTypes.DateTime,
  Duration: _temporalTypes.Duration,
  LocalDateTime: _temporalTypes.LocalDateTime,
  LocalTime: _temporalTypes.LocalTime,
  Time: _temporalTypes.Time,
  Integer: _integer["default"]
  /**
   * Object containing string constants representing session access modes.
   */

};
exports.types = types;
var session = {
  READ: _driver.READ,
  WRITE: _driver.WRITE
  /**
   * Object containing string constants representing predefined {@link Neo4jError} codes.
   */

};
exports.session = session;
var error = {
  SERVICE_UNAVAILABLE: _error.SERVICE_UNAVAILABLE,
  SESSION_EXPIRED: _error.SESSION_EXPIRED,
  PROTOCOL_ERROR: _error.PROTOCOL_ERROR
  /**
   * Object containing functions to work with {@link Integer} objects.
   */

};
exports.error = error;
var integer = {
  toNumber: _integer.toNumber,
  toString: _integer.toString,
  inSafeRange: _integer.inSafeRange
  /**
   * Object containing functions to work with spatial types, like {@link Point}.
   */

};
exports.integer = integer;
var spatial = {
  isPoint: _spatialTypes.isPoint
  /**
   * Object containing functions to work with temporal types, like {@link Time} or {@link Duration}.
   */

};
exports.spatial = spatial;
var temporal = {
  isDuration: _temporalTypes.isDuration,
  isLocalTime: _temporalTypes.isLocalTime,
  isTime: _temporalTypes.isTime,
  isDate: _temporalTypes.isDate,
  isLocalDateTime: _temporalTypes.isLocalDateTime,
  isDateTime: _temporalTypes.isDateTime
  /**
   * @private
   */

};
exports.temporal = temporal;
var forExport = {
  driver: driver,
  "int": _integer["int"],
  isInt: _integer.isInt,
  isPoint: _spatialTypes.isPoint,
  isDuration: _temporalTypes.isDuration,
  isLocalTime: _temporalTypes.isLocalTime,
  isTime: _temporalTypes.isTime,
  isDate: _temporalTypes.isDate,
  isLocalDateTime: _temporalTypes.isLocalDateTime,
  isDateTime: _temporalTypes.isDateTime,
  integer: integer,
  Neo4jError: _error.Neo4jError,
  auth: auth,
  logging: logging,
  types: types,
  session: session,
  error: error,
  spatial: spatial,
  temporal: temporal
};
var _default = forExport;
exports["default"] = _default;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"../version":89,"./driver":28,"./error":29,"./graph-types":30,"./integer":32,"./internal/http/http-driver":52,"./internal/server-address":73,"./internal/url-util":79,"./internal/util":80,"./record":81,"./result":83,"./result-summary":82,"./routing-driver":84,"./spatial-types":86,"./temporal-types":87,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/interopRequireWildcard":12}],32:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = exports.toString = exports.toNumber = exports.inSafeRange = exports.isInt = exports["int"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _error = require("./error");

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
// 64-bit Integer library, originally from Long.js by dcodeIO
// https://github.com/dcodeIO/Long.js
// License Apache 2

/**
 * Constructs a 64 bit two's-complement integer, given its low and high 32 bit values as *signed* integers.
 * See exported functions for more convenient ways of operating integers.
 * Use `int()` function to create new integers, `isInt()` to check if given object is integer,
 * `inSafeRange()` to check if it is safe to convert given value to native number,
 * `toNumber()` and `toString()` to convert given integer to number or string respectively.
 * @access public
 * @exports Integer
 * @class A Integer class for representing a 64 bit two's-complement integer value.
 * @param {number} low The low (signed) 32 bits of the long
 * @param {number} high The high (signed) 32 bits of the long
 * @constructor
 */
var Integer =
/*#__PURE__*/
function () {
  function Integer(low, high) {
    (0, _classCallCheck2["default"])(this, Integer);

    /**
     * The low 32 bits as a signed value.
     * @type {number}
     * @expose
     */
    this.low = low | 0;
    /**
     * The high 32 bits as a signed value.
     * @type {number}
     * @expose
     */

    this.high = high | 0;
  } // The internal representation of an Integer is the two given signed, 32-bit values.
  // We use 32-bit pieces because these are the size of integers on which
  // JavaScript performs bit-operations.  For operations like addition and
  // multiplication, we split each number into 16 bit pieces, which can easily be
  // multiplied within JavaScript's floating-point representation without overflow
  // or change in sign.
  //
  // In the algorithms below, we frequently reduce the negative case to the
  // positive case by negating the input(s) and then post-processing the result.
  // Note that we must ALWAYS check specially whether those values are MIN_VALUE
  // (-2^63) because -MIN_VALUE == MIN_VALUE (since 2^63 cannot be represented as
  // a positive number, it overflows back into a negative).  Not handling this
  // case would often result in infinite recursion.
  //
  // Common constant values ZERO, ONE, NEG_ONE, etc. are defined below the from*
  // methods on which they depend.


  (0, _createClass2["default"])(Integer, [{
    key: "inSafeRange",
    value: function inSafeRange() {
      return this.greaterThanOrEqual(Integer.MIN_SAFE_VALUE) && this.lessThanOrEqual(Integer.MAX_SAFE_VALUE);
    }
    /**
     * Converts the Integer to an exact javascript Number, assuming it is a 32 bit integer.
     * @returns {number}
     * @expose
     */

  }, {
    key: "toInt",
    value: function toInt() {
      return this.low;
    }
    /**
     * Converts the Integer to a the nearest floating-point representation of this value (double, 53 bit mantissa).
     * @returns {number}
     * @expose
     */

  }, {
    key: "toNumber",
    value: function toNumber() {
      return this.high * TWO_PWR_32_DBL + (this.low >>> 0);
    }
    /**
     * Converts the Integer to native number or -Infinity/+Infinity when it does not fit.
     * @return {number}
     * @package
     */

  }, {
    key: "toNumberOrInfinity",
    value: function toNumberOrInfinity() {
      if (this.lessThan(Integer.MIN_SAFE_VALUE)) {
        return Number.NEGATIVE_INFINITY;
      } else if (this.greaterThan(Integer.MAX_SAFE_VALUE)) {
        return Number.POSITIVE_INFINITY;
      } else {
        return this.toNumber();
      }
    }
    /**
     * Converts the Integer to a string written in the specified radix.
     * @param {number=} radix Radix (2-36), defaults to 10
     * @returns {string}
     * @override
     * @throws {RangeError} If `radix` is out of range
     * @expose
     */

  }, {
    key: "toString",
    value: function toString(radix) {
      radix = radix || 10;

      if (radix < 2 || radix > 36) {
        throw RangeError('radix out of range: ' + radix);
      }

      if (this.isZero()) {
        return '0';
      }

      var rem;

      if (this.isNegative()) {
        if (this.equals(Integer.MIN_VALUE)) {
          // We need to change the Integer value before it can be negated, so we remove
          // the bottom-most digit in this base and then recurse to do the rest.
          var radixInteger = Integer.fromNumber(radix);
          var div = this.div(radixInteger);
          rem = div.multiply(radixInteger).subtract(this);
          return div.toString(radix) + rem.toInt().toString(radix);
        } else {
          return '-' + this.negate().toString(radix);
        }
      } // Do several (6) digits each time through the loop, so as to
      // minimize the calls to the very expensive emulated div.


      var radixToPower = Integer.fromNumber(Math.pow(radix, 6));
      rem = this;
      var result = '';

      while (true) {
        var remDiv = rem.div(radixToPower);
        var intval = rem.subtract(remDiv.multiply(radixToPower)).toInt() >>> 0;
        var digits = intval.toString(radix);
        rem = remDiv;

        if (rem.isZero()) {
          return digits + result;
        } else {
          while (digits.length < 6) {
            digits = '0' + digits;
          }

          result = '' + digits + result;
        }
      }
    }
    /**
     * Gets the high 32 bits as a signed integer.
     * @returns {number} Signed high bits
     * @expose
     */

  }, {
    key: "getHighBits",
    value: function getHighBits() {
      return this.high;
    }
    /**
     * Gets the low 32 bits as a signed integer.
     * @returns {number} Signed low bits
     * @expose
     */

  }, {
    key: "getLowBits",
    value: function getLowBits() {
      return this.low;
    }
    /**
     * Gets the number of bits needed to represent the absolute value of this Integer.
     * @returns {number}
     * @expose
     */

  }, {
    key: "getNumBitsAbs",
    value: function getNumBitsAbs() {
      if (this.isNegative()) {
        return this.equals(Integer.MIN_VALUE) ? 64 : this.negate().getNumBitsAbs();
      }

      var val = this.high !== 0 ? this.high : this.low;

      for (var bit = 31; bit > 0; bit--) {
        if ((val & 1 << bit) !== 0) {
          break;
        }
      }

      return this.high !== 0 ? bit + 33 : bit + 1;
    }
    /**
     * Tests if this Integer's value equals zero.
     * @returns {boolean}
     * @expose
     */

  }, {
    key: "isZero",
    value: function isZero() {
      return this.high === 0 && this.low === 0;
    }
    /**
     * Tests if this Integer's value is negative.
     * @returns {boolean}
     * @expose
     */

  }, {
    key: "isNegative",
    value: function isNegative() {
      return this.high < 0;
    }
    /**
     * Tests if this Integer's value is positive.
     * @returns {boolean}
     * @expose
     */

  }, {
    key: "isPositive",
    value: function isPositive() {
      return this.high >= 0;
    }
    /**
     * Tests if this Integer's value is odd.
     * @returns {boolean}
     * @expose
     */

  }, {
    key: "isOdd",
    value: function isOdd() {
      return (this.low & 1) === 1;
    }
    /**
     * Tests if this Integer's value is even.
     * @returns {boolean}
     * @expose
     */

  }, {
    key: "isEven",
    value: function isEven() {
      return (this.low & 1) === 0;
    }
    /**
     * Tests if this Integer's value equals the specified's.
     * @param {!Integer|number|string} other Other value
     * @returns {boolean}
     * @expose
     */

  }, {
    key: "equals",
    value: function equals(other) {
      if (!Integer.isInteger(other)) {
        other = Integer.fromValue(other);
      }

      return this.high === other.high && this.low === other.low;
    }
    /**
     * Tests if this Integer's value differs from the specified's.
     * @param {!Integer|number|string} other Other value
     * @returns {boolean}
     * @expose
     */

  }, {
    key: "notEquals",
    value: function notEquals(other) {
      return !this.equals(
      /* validates */
      other);
    }
    /**
     * Tests if this Integer's value is less than the specified's.
     * @param {!Integer|number|string} other Other value
     * @returns {boolean}
     * @expose
     */

  }, {
    key: "lessThan",
    value: function lessThan(other) {
      return this.compare(
      /* validates */
      other) < 0;
    }
    /**
     * Tests if this Integer's value is less than or equal the specified's.
     * @param {!Integer|number|string} other Other value
     * @returns {boolean}
     * @expose
     */

  }, {
    key: "lessThanOrEqual",
    value: function lessThanOrEqual(other) {
      return this.compare(
      /* validates */
      other) <= 0;
    }
    /**
     * Tests if this Integer's value is greater than the specified's.
     * @param {!Integer|number|string} other Other value
     * @returns {boolean}
     * @expose
     */

  }, {
    key: "greaterThan",
    value: function greaterThan(other) {
      return this.compare(
      /* validates */
      other) > 0;
    }
    /**
     * Tests if this Integer's value is greater than or equal the specified's.
     * @param {!Integer|number|string} other Other value
     * @returns {boolean}
     * @expose
     */

  }, {
    key: "greaterThanOrEqual",
    value: function greaterThanOrEqual(other) {
      return this.compare(
      /* validates */
      other) >= 0;
    }
    /**
     * Compares this Integer's value with the specified's.
     * @param {!Integer|number|string} other Other value
     * @returns {number} 0 if they are the same, 1 if the this is greater and -1
     *  if the given one is greater
     * @expose
     */

  }, {
    key: "compare",
    value: function compare(other) {
      if (!Integer.isInteger(other)) {
        other = Integer.fromValue(other);
      }

      if (this.equals(other)) {
        return 0;
      }

      var thisNeg = this.isNegative();
      var otherNeg = other.isNegative();

      if (thisNeg && !otherNeg) {
        return -1;
      }

      if (!thisNeg && otherNeg) {
        return 1;
      } // At this point the sign bits are the same


      return this.subtract(other).isNegative() ? -1 : 1;
    }
    /**
     * Negates this Integer's value.
     * @returns {!Integer} Negated Integer
     * @expose
     */

  }, {
    key: "negate",
    value: function negate() {
      if (this.equals(Integer.MIN_VALUE)) {
        return Integer.MIN_VALUE;
      }

      return this.not().add(Integer.ONE);
    }
    /**
     * Returns the sum of this and the specified Integer.
     * @param {!Integer|number|string} addend Addend
     * @returns {!Integer} Sum
     * @expose
     */

  }, {
    key: "add",
    value: function add(addend) {
      if (!Integer.isInteger(addend)) {
        addend = Integer.fromValue(addend);
      } // Divide each number into 4 chunks of 16 bits, and then sum the chunks.


      var a48 = this.high >>> 16;
      var a32 = this.high & 0xffff;
      var a16 = this.low >>> 16;
      var a00 = this.low & 0xffff;
      var b48 = addend.high >>> 16;
      var b32 = addend.high & 0xffff;
      var b16 = addend.low >>> 16;
      var b00 = addend.low & 0xffff;
      var c48 = 0;
      var c32 = 0;
      var c16 = 0;
      var c00 = 0;
      c00 += a00 + b00;
      c16 += c00 >>> 16;
      c00 &= 0xffff;
      c16 += a16 + b16;
      c32 += c16 >>> 16;
      c16 &= 0xffff;
      c32 += a32 + b32;
      c48 += c32 >>> 16;
      c32 &= 0xffff;
      c48 += a48 + b48;
      c48 &= 0xffff;
      return Integer.fromBits(c16 << 16 | c00, c48 << 16 | c32);
    }
    /**
     * Returns the difference of this and the specified Integer.
     * @param {!Integer|number|string} subtrahend Subtrahend
     * @returns {!Integer} Difference
     * @expose
     */

  }, {
    key: "subtract",
    value: function subtract(subtrahend) {
      if (!Integer.isInteger(subtrahend)) {
        subtrahend = Integer.fromValue(subtrahend);
      }

      return this.add(subtrahend.negate());
    }
    /**
     * Returns the product of this and the specified Integer.
     * @param {!Integer|number|string} multiplier Multiplier
     * @returns {!Integer} Product
     * @expose
     */

  }, {
    key: "multiply",
    value: function multiply(multiplier) {
      if (this.isZero()) {
        return Integer.ZERO;
      }

      if (!Integer.isInteger(multiplier)) {
        multiplier = Integer.fromValue(multiplier);
      }

      if (multiplier.isZero()) {
        return Integer.ZERO;
      }

      if (this.equals(Integer.MIN_VALUE)) {
        return multiplier.isOdd() ? Integer.MIN_VALUE : Integer.ZERO;
      }

      if (multiplier.equals(Integer.MIN_VALUE)) {
        return this.isOdd() ? Integer.MIN_VALUE : Integer.ZERO;
      }

      if (this.isNegative()) {
        if (multiplier.isNegative()) {
          return this.negate().multiply(multiplier.negate());
        } else {
          return this.negate().multiply(multiplier).negate();
        }
      } else if (multiplier.isNegative()) {
        return this.multiply(multiplier.negate()).negate();
      } // If both longs are small, use float multiplication


      if (this.lessThan(TWO_PWR_24) && multiplier.lessThan(TWO_PWR_24)) {
        return Integer.fromNumber(this.toNumber() * multiplier.toNumber());
      } // Divide each long into 4 chunks of 16 bits, and then add up 4x4 products.
      // We can skip products that would overflow.


      var a48 = this.high >>> 16;
      var a32 = this.high & 0xffff;
      var a16 = this.low >>> 16;
      var a00 = this.low & 0xffff;
      var b48 = multiplier.high >>> 16;
      var b32 = multiplier.high & 0xffff;
      var b16 = multiplier.low >>> 16;
      var b00 = multiplier.low & 0xffff;
      var c48 = 0;
      var c32 = 0;
      var c16 = 0;
      var c00 = 0;
      c00 += a00 * b00;
      c16 += c00 >>> 16;
      c00 &= 0xffff;
      c16 += a16 * b00;
      c32 += c16 >>> 16;
      c16 &= 0xffff;
      c16 += a00 * b16;
      c32 += c16 >>> 16;
      c16 &= 0xffff;
      c32 += a32 * b00;
      c48 += c32 >>> 16;
      c32 &= 0xffff;
      c32 += a16 * b16;
      c48 += c32 >>> 16;
      c32 &= 0xffff;
      c32 += a00 * b32;
      c48 += c32 >>> 16;
      c32 &= 0xffff;
      c48 += a48 * b00 + a32 * b16 + a16 * b32 + a00 * b48;
      c48 &= 0xffff;
      return Integer.fromBits(c16 << 16 | c00, c48 << 16 | c32);
    }
    /**
     * Returns this Integer divided by the specified.
     * @param {!Integer|number|string} divisor Divisor
     * @returns {!Integer} Quotient
     * @expose
     */

  }, {
    key: "div",
    value: function div(divisor) {
      if (!Integer.isInteger(divisor)) {
        divisor = Integer.fromValue(divisor);
      }

      if (divisor.isZero()) {
        throw (0, _error.newError)('division by zero');
      }

      if (this.isZero()) {
        return Integer.ZERO;
      }

      var approx, rem, res;

      if (this.equals(Integer.MIN_VALUE)) {
        if (divisor.equals(Integer.ONE) || divisor.equals(Integer.NEG_ONE)) {
          return Integer.MIN_VALUE;
        } else if (divisor.equals(Integer.MIN_VALUE)) {
          return Integer.ONE;
        } else {
          // At this point, we have |other| >= 2, so |this/other| < |MIN_VALUE|.
          var halfThis = this.shiftRight(1);
          approx = halfThis.div(divisor).shiftLeft(1);

          if (approx.equals(Integer.ZERO)) {
            return divisor.isNegative() ? Integer.ONE : Integer.NEG_ONE;
          } else {
            rem = this.subtract(divisor.multiply(approx));
            res = approx.add(rem.div(divisor));
            return res;
          }
        }
      } else if (divisor.equals(Integer.MIN_VALUE)) {
        return Integer.ZERO;
      }

      if (this.isNegative()) {
        if (divisor.isNegative()) {
          return this.negate().div(divisor.negate());
        }

        return this.negate().div(divisor).negate();
      } else if (divisor.isNegative()) {
        return this.div(divisor.negate()).negate();
      } // Repeat the following until the remainder is less than other:  find a
      // floating-point that approximates remainder / other *from below*, add this
      // into the result, and subtract it from the remainder.  It is critical that
      // the approximate value is less than or equal to the real value so that the
      // remainder never becomes negative.


      res = Integer.ZERO;
      rem = this;

      while (rem.greaterThanOrEqual(divisor)) {
        // Approximate the result of division. This may be a little greater or
        // smaller than the actual value.
        approx = Math.max(1, Math.floor(rem.toNumber() / divisor.toNumber())); // We will tweak the approximate result by changing it in the 48-th digit or
        // the smallest non-fractional digit, whichever is larger.

        var log2 = Math.ceil(Math.log(approx) / Math.LN2);
        var delta = log2 <= 48 ? 1 : Math.pow(2, log2 - 48); // Decrease the approximation until it is smaller than the remainder.  Note
        // that if it is too large, the product overflows and is negative.

        var approxRes = Integer.fromNumber(approx);
        var approxRem = approxRes.multiply(divisor);

        while (approxRem.isNegative() || approxRem.greaterThan(rem)) {
          approx -= delta;
          approxRes = Integer.fromNumber(approx);
          approxRem = approxRes.multiply(divisor);
        } // We know the answer can't be zero... and actually, zero would cause
        // infinite recursion since we would make no progress.


        if (approxRes.isZero()) {
          approxRes = Integer.ONE;
        }

        res = res.add(approxRes);
        rem = rem.subtract(approxRem);
      }

      return res;
    }
    /**
     * Returns this Integer modulo the specified.
     * @param {!Integer|number|string} divisor Divisor
     * @returns {!Integer} Remainder
     * @expose
     */

  }, {
    key: "modulo",
    value: function modulo(divisor) {
      if (!Integer.isInteger(divisor)) {
        divisor = Integer.fromValue(divisor);
      }

      return this.subtract(this.div(divisor).multiply(divisor));
    }
    /**
     * Returns the bitwise NOT of this Integer.
     * @returns {!Integer}
     * @expose
     */

  }, {
    key: "not",
    value: function not() {
      return Integer.fromBits(~this.low, ~this.high);
    }
    /**
     * Returns the bitwise AND of this Integer and the specified.
     * @param {!Integer|number|string} other Other Integer
     * @returns {!Integer}
     * @expose
     */

  }, {
    key: "and",
    value: function and(other) {
      if (!Integer.isInteger(other)) {
        other = Integer.fromValue(other);
      }

      return Integer.fromBits(this.low & other.low, this.high & other.high);
    }
    /**
     * Returns the bitwise OR of this Integer and the specified.
     * @param {!Integer|number|string} other Other Integer
     * @returns {!Integer}
     * @expose
     */

  }, {
    key: "or",
    value: function or(other) {
      if (!Integer.isInteger(other)) {
        other = Integer.fromValue(other);
      }

      return Integer.fromBits(this.low | other.low, this.high | other.high);
    }
    /**
     * Returns the bitwise XOR of this Integer and the given one.
     * @param {!Integer|number|string} other Other Integer
     * @returns {!Integer}
     * @expose
     */

  }, {
    key: "xor",
    value: function xor(other) {
      if (!Integer.isInteger(other)) {
        other = Integer.fromValue(other);
      }

      return Integer.fromBits(this.low ^ other.low, this.high ^ other.high);
    }
    /**
     * Returns this Integer with bits shifted to the left by the given amount.
     * @param {number|!Integer} numBits Number of bits
     * @returns {!Integer} Shifted Integer
     * @expose
     */

  }, {
    key: "shiftLeft",
    value: function shiftLeft(numBits) {
      if (Integer.isInteger(numBits)) {
        numBits = numBits.toInt();
      }

      if ((numBits &= 63) === 0) {
        return this;
      } else if (numBits < 32) {
        return Integer.fromBits(this.low << numBits, this.high << numBits | this.low >>> 32 - numBits);
      } else {
        return Integer.fromBits(0, this.low << numBits - 32);
      }
    }
    /**
     * Returns this Integer with bits arithmetically shifted to the right by the given amount.
     * @param {number|!Integer} numBits Number of bits
     * @returns {!Integer} Shifted Integer
     * @expose
     */

  }, {
    key: "shiftRight",
    value: function shiftRight(numBits) {
      if (Integer.isInteger(numBits)) {
        numBits = numBits.toInt();
      }

      if ((numBits &= 63) === 0) {
        return this;
      } else if (numBits < 32) {
        return Integer.fromBits(this.low >>> numBits | this.high << 32 - numBits, this.high >> numBits);
      } else {
        return Integer.fromBits(this.high >> numBits - 32, this.high >= 0 ? 0 : -1);
      }
    }
  }]);
  return Integer;
}();
/**
 * An indicator used to reliably determine if an object is a Integer or not.
 * @type {boolean}
 * @const
 * @expose
 * @private
 */


Integer.__isInteger__ = true;
Object.defineProperty(Integer.prototype, '__isInteger__', {
  value: true,
  enumerable: false,
  configurable: false
});
/**
 * Tests if the specified object is a Integer.
 * @access private
 * @param {*} obj Object
 * @returns {boolean}
 * @expose
 */

Integer.isInteger = function (obj) {
  return (obj && obj['__isInteger__']) === true;
};
/**
 * A cache of the Integer representations of small integer values.
 * @type {!Object}
 * @inner
 * @private
 */


var INT_CACHE = {};
/**
 * Returns a Integer representing the given 32 bit integer value.
 * @access private
 * @param {number} value The 32 bit integer in question
 * @returns {!Integer} The corresponding Integer value
 * @expose
 */

Integer.fromInt = function (value) {
  var obj, cachedObj;
  value = value | 0;

  if (value >= -128 && value < 128) {
    cachedObj = INT_CACHE[value];

    if (cachedObj) {
      return cachedObj;
    }
  }

  obj = new Integer(value, value < 0 ? -1 : 0, false);

  if (value >= -128 && value < 128) {
    INT_CACHE[value] = obj;
  }

  return obj;
};
/**
 * Returns a Integer representing the given value, provided that it is a finite number. Otherwise, zero is returned.
 * @access private
 * @param {number} value The number in question
 * @returns {!Integer} The corresponding Integer value
 * @expose
 */


Integer.fromNumber = function (value) {
  if (isNaN(value) || !isFinite(value)) {
    return Integer.ZERO;
  }

  if (value <= -TWO_PWR_63_DBL) {
    return Integer.MIN_VALUE;
  }

  if (value + 1 >= TWO_PWR_63_DBL) {
    return Integer.MAX_VALUE;
  }

  if (value < 0) {
    return Integer.fromNumber(-value).negate();
  }

  return new Integer(value % TWO_PWR_32_DBL | 0, value / TWO_PWR_32_DBL | 0);
};
/**
 * Returns a Integer representing the 64 bit integer that comes by concatenating the given low and high bits. Each is
 *  assumed to use 32 bits.
 * @access private
 * @param {number} lowBits The low 32 bits
 * @param {number} highBits The high 32 bits
 * @returns {!Integer} The corresponding Integer value
 * @expose
 */


Integer.fromBits = function (lowBits, highBits) {
  return new Integer(lowBits, highBits);
};
/**
 * Returns a Integer representation of the given string, written using the specified radix.
 * @access private
 * @param {string} str The textual representation of the Integer
 * @param {number=} radix The radix in which the text is written (2-36), defaults to 10
 * @returns {!Integer} The corresponding Integer value
 * @expose
 */


Integer.fromString = function (str, radix) {
  if (str.length === 0) {
    throw (0, _error.newError)('number format error: empty string');
  }

  if (str === 'NaN' || str === 'Infinity' || str === '+Infinity' || str === '-Infinity') {
    return Integer.ZERO;
  }

  radix = radix || 10;

  if (radix < 2 || radix > 36) {
    throw (0, _error.newError)('radix out of range: ' + radix);
  }

  var p;

  if ((p = str.indexOf('-')) > 0) {
    throw (0, _error.newError)('number format error: interior "-" character: ' + str);
  } else if (p === 0) {
    return Integer.fromString(str.substring(1), radix).negate();
  } // Do several (8) digits each time through the loop, so as to
  // minimize the calls to the very expensive emulated div.


  var radixToPower = Integer.fromNumber(Math.pow(radix, 8));
  var result = Integer.ZERO;

  for (var i = 0; i < str.length; i += 8) {
    var size = Math.min(8, str.length - i);
    var value = parseInt(str.substring(i, i + size), radix);

    if (size < 8) {
      var power = Integer.fromNumber(Math.pow(radix, size));
      result = result.multiply(power).add(Integer.fromNumber(value));
    } else {
      result = result.multiply(radixToPower);
      result = result.add(Integer.fromNumber(value));
    }
  }

  return result;
};
/**
 * Converts the specified value to a Integer.
 * @access private
 * @param {!Integer|number|string|!{low: number, high: number}} val Value
 * @returns {!Integer}
 * @expose
 */


Integer.fromValue = function (val) {
  if (val
  /* is compatible */
  instanceof Integer) {
    return val;
  }

  if (typeof val === 'number') {
    return Integer.fromNumber(val);
  }

  if (typeof val === 'string') {
    return Integer.fromString(val);
  } // Throws for non-objects, converts non-instanceof Integer:


  return new Integer(val.low, val.high);
};
/**
 * Converts the specified value to a number.
 * @access private
 * @param {!Integer|number|string|!{low: number, high: number}} val Value
 * @returns {number}
 * @expose
 */


Integer.toNumber = function (val) {
  return Integer.fromValue(val).toNumber();
};
/**
 * Converts the specified value to a string.
 * @access private
 * @param {!Integer|number|string|!{low: number, high: number}} val Value
 * @param {number} radix optional radix for string conversion, defaults to 10
 * @returns {String}
 * @expose
 */


Integer.toString = function (val, radix) {
  return Integer.fromValue(val).toString(radix);
};
/**
 * Checks if the given value is in the safe range in order to be converted to a native number
 * @access private
 * @param {!Integer|number|string|!{low: number, high: number}} val Value
 * @param {number} radix optional radix for string conversion, defaults to 10
 * @returns {boolean}
 * @expose
 */


Integer.inSafeRange = function (val) {
  return Integer.fromValue(val).inSafeRange();
};
/**
 * @type {number}
 * @const
 * @inner
 * @private
 */


var TWO_PWR_16_DBL = 1 << 16;
/**
 * @type {number}
 * @const
 * @inner
 * @private
 */

var TWO_PWR_24_DBL = 1 << 24;
/**
 * @type {number}
 * @const
 * @inner
 * @private
 */

var TWO_PWR_32_DBL = TWO_PWR_16_DBL * TWO_PWR_16_DBL;
/**
 * @type {number}
 * @const
 * @inner
 * @private
 */

var TWO_PWR_64_DBL = TWO_PWR_32_DBL * TWO_PWR_32_DBL;
/**
 * @type {number}
 * @const
 * @inner
 * @private
 */

var TWO_PWR_63_DBL = TWO_PWR_64_DBL / 2;
/**
 * @type {!Integer}
 * @const
 * @inner
 * @private
 */

var TWO_PWR_24 = Integer.fromInt(TWO_PWR_24_DBL);
/**
 * Signed zero.
 * @type {!Integer}
 * @expose
 */

Integer.ZERO = Integer.fromInt(0);
/**
 * Signed one.
 * @type {!Integer}
 * @expose
 */

Integer.ONE = Integer.fromInt(1);
/**
 * Signed negative one.
 * @type {!Integer}
 * @expose
 */

Integer.NEG_ONE = Integer.fromInt(-1);
/**
 * Maximum signed value.
 * @type {!Integer}
 * @expose
 */

Integer.MAX_VALUE = Integer.fromBits(0xffffffff | 0, 0x7fffffff | 0, false);
/**
 * Minimum signed value.
 * @type {!Integer}
 * @expose
 */

Integer.MIN_VALUE = Integer.fromBits(0, 0x80000000 | 0, false);
/**
 * Minimum safe value.
 * @type {!Integer}
 * @expose
 */

Integer.MIN_SAFE_VALUE = Integer.fromBits(0x1 | 0, 0xffffffffffe00000 | 0);
/**
 * Maximum safe value.
 * @type {!Integer}
 * @expose
 */

Integer.MAX_SAFE_VALUE = Integer.fromBits(0xffffffff | 0, 0x1fffff | 0);
/**
 * Cast value to Integer type.
 * @access public
 * @param {Mixed} value - The value to use.
 * @return {Integer} - An object of type Integer.
 */

var _int = Integer.fromValue;
/**
 * Check if a variable is of Integer type.
 * @access public
 * @param {Mixed} value - The variable to check.
 * @return {Boolean} - Is it of the Integer type?
 */

exports["int"] = _int;
var isInt = Integer.isInteger;
/**
 * Check if a variable can be safely converted to a number
 * @access public
 * @param {Mixed} value - The variable to check
 * @return {Boolean} - true if it is safe to call toNumber on variable otherwise false
 */

exports.isInt = isInt;
var inSafeRange = Integer.inSafeRange;
/**
 * Converts a variable to a number
 * @access public
 * @param {Mixed} value - The variable to convert
 * @return {number} - the variable as a number
 */

exports.inSafeRange = inSafeRange;
var toNumber = Integer.toNumber;
/**
 * Converts the integer to a string representation
 * @access public
 * @param {Mixed} value - The variable to convert
 * @param {number} radix - radix to use in string conversion, defaults to 10
 * @return {String} - returns a string representation of the integer
 */

exports.toNumber = toNumber;
var toString = Integer.toString;
exports.toString = toString;
var _default = Integer;
exports["default"] = _default;

},{"./error":29,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11}],33:[function(require,module,exports){
"use strict";

var _interopRequireWildcard = require("@babel/runtime/helpers/interopRequireWildcard");

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _requestMessage = _interopRequireDefault(require("./request-message"));

var v1 = _interopRequireWildcard(require("./packstream-v1"));

var _error = require("../error");

var _bookmark = _interopRequireDefault(require("./bookmark"));

var _txConfig = _interopRequireDefault(require("./tx-config"));

var _constants = require("./constants");

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
function () {
  /**
   * @constructor
   * @param {Connection} connection the connection.
   * @param {Chunker} chunker the chunker.
   * @param {boolean} disableLosslessIntegers if this connection should convert all received integers to native JS numbers.
   */
  function BoltProtocol(connection, chunker, disableLosslessIntegers) {
    (0, _classCallCheck2["default"])(this, BoltProtocol);
    this._connection = connection;
    this._packer = this._createPacker(chunker);
    this._unpacker = this._createUnpacker(disableLosslessIntegers);
  }
  /**
   * Get the packer.
   * @return {Packer} the protocol's packer.
   */


  (0, _createClass2["default"])(BoltProtocol, [{
    key: "packer",
    value: function packer() {
      return this._packer;
    }
    /**
     * Get the unpacker.
     * @return {Unpacker} the protocol's unpacker.
     */

  }, {
    key: "unpacker",
    value: function unpacker() {
      return this._unpacker;
    }
    /**
     * Transform metadata received in SUCCESS message before it is passed to the handler.
     * @param {object} metadata the received metadata.
     * @return {object} transformed metadata.
     */

  }, {
    key: "transformMetadata",
    value: function transformMetadata(metadata) {
      return metadata;
    }
    /**
     * Perform initialization and authentication of the underlying connection.
     * @param {string} clientName the client name.
     * @param {object} authToken the authentication token.
     * @param {StreamObserver} observer the response observer.
     */

  }, {
    key: "initialize",
    value: function initialize(clientName, authToken, observer) {
      var message = _requestMessage["default"].init(clientName, authToken);

      this._connection.write(message, observer, true);
    }
  }, {
    key: "prepareToClose",
    value: function prepareToClose(observer) {} // no need to notify the database in this protocol version

    /**
     * Begin an explicit transaction.
     * @param {Bookmark} bookmark the bookmark.
     * @param {TxConfig} txConfig the configuration.
     * @param {string} mode the access mode.
     * @param {StreamObserver} observer the response observer.
     */

  }, {
    key: "beginTransaction",
    value: function beginTransaction(bookmark, txConfig, mode, observer) {
      assertTxConfigIsEmpty(txConfig, this._connection, observer);

      var runMessage = _requestMessage["default"].run('BEGIN', bookmark.asBeginTransactionParameters());

      var pullAllMessage = _requestMessage["default"].pullAll();

      this._connection.write(runMessage, observer, false);

      this._connection.write(pullAllMessage, observer, false);
    }
    /**
     * Commit the explicit transaction.
     * @param {StreamObserver} observer the response observer.
     */

  }, {
    key: "commitTransaction",
    value: function commitTransaction(observer) {
      // WRITE access mode is used as a place holder here, it has
      // no effect on behaviour for Bolt V1 & V2
      this.run('COMMIT', {}, _bookmark["default"].empty(), _txConfig["default"].empty(), _constants.ACCESS_MODE_WRITE, observer);
    }
    /**
     * Rollback the explicit transaction.
     * @param {StreamObserver} observer the response observer.
     */

  }, {
    key: "rollbackTransaction",
    value: function rollbackTransaction(observer) {
      // WRITE access mode is used as a place holder here, it has
      // no effect on behaviour for Bolt V1 & V2
      this.run('ROLLBACK', {}, _bookmark["default"].empty(), _txConfig["default"].empty(), _constants.ACCESS_MODE_WRITE, observer);
    }
    /**
     * Send a Cypher statement through the underlying connection.
     * @param {string} statement the cypher statement.
     * @param {object} parameters the statement parameters.
     * @param {Bookmark} bookmark the bookmark.
     * @param {TxConfig} txConfig the auto-commit transaction configuration.
     * @param {string} mode the access mode.
     * @param {StreamObserver} observer the response observer.
     */

  }, {
    key: "run",
    value: function run(statement, parameters, bookmark, txConfig, mode, observer) {
      // bookmark and mode are ignored in this versioon of the protocol
      assertTxConfigIsEmpty(txConfig, this._connection, observer);

      var runMessage = _requestMessage["default"].run(statement, parameters);

      var pullAllMessage = _requestMessage["default"].pullAll();

      this._connection.write(runMessage, observer, false);

      this._connection.write(pullAllMessage, observer, true);
    }
    /**
     * Send a RESET through the underlying connection.
     * @param {StreamObserver} observer the response observer.
     */

  }, {
    key: "reset",
    value: function reset(observer) {
      var message = _requestMessage["default"].reset();

      this._connection.write(message, observer, true);
    }
  }, {
    key: "_createPacker",
    value: function _createPacker(chunker) {
      return new v1.Packer(chunker);
    }
  }, {
    key: "_createUnpacker",
    value: function _createUnpacker(disableLosslessIntegers) {
      return new v1.Unpacker(disableLosslessIntegers);
    }
  }]);
  return BoltProtocol;
}();
/**
 * @param {TxConfig} txConfig the auto-commit transaction configuration.
 * @param {Connection} connection the connection.
 * @param {StreamObserver} observer the response observer.
 */


exports["default"] = BoltProtocol;

function assertTxConfigIsEmpty(txConfig, connection, observer) {
  if (!txConfig.isEmpty()) {
    var error = (0, _error.newError)('Driver is connected to the database that does not support transaction configuration. ' + 'Please upgrade to neo4j 3.5.0 or later in order to use this functionality'); // unsupported API was used, consider this a fatal error for the current connection

    connection._handleFatalError(error);

    observer.onError(error);
    throw error;
  }
}

},{"../error":29,"./bookmark":36,"./constants":51,"./packstream-v1":60,"./request-message":66,"./tx-config":78,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/interopRequireWildcard":12}],34:[function(require,module,exports){
"use strict";

var _interopRequireWildcard = require("@babel/runtime/helpers/interopRequireWildcard");

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

var _boltProtocolV = _interopRequireDefault(require("./bolt-protocol-v1"));

var v2 = _interopRequireWildcard(require("./packstream-v2"));

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
    key: "_createPacker",
    value: function _createPacker(chunker) {
      return new v2.Packer(chunker);
    }
  }, {
    key: "_createUnpacker",
    value: function _createUnpacker(disableLosslessIntegers) {
      return new v2.Unpacker(disableLosslessIntegers);
    }
  }]);
  return BoltProtocol;
}(_boltProtocolV["default"]);

exports["default"] = BoltProtocol;

},{"./bolt-protocol-v1":33,"./packstream-v2":61,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/getPrototypeOf":9,"@babel/runtime/helpers/inherits":10,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/interopRequireWildcard":12,"@babel/runtime/helpers/possibleConstructorReturn":18}],35:[function(require,module,exports){
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

},{"./bolt-protocol-v2":34,"./request-message":66,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/getPrototypeOf":9,"@babel/runtime/helpers/inherits":10,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/possibleConstructorReturn":18}],36:[function(require,module,exports){
"use strict";

var _interopRequireWildcard = require("@babel/runtime/helpers/interopRequireWildcard");

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _defineProperty2 = _interopRequireDefault(require("@babel/runtime/helpers/defineProperty"));

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var util = _interopRequireWildcard(require("./util"));

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
var BOOKMARK_KEY = 'bookmark';
var BOOKMARKS_KEY = 'bookmarks';
var BOOKMARK_PREFIX = 'neo4j:bookmark:v1:tx';
var UNKNOWN_BOOKMARK_VALUE = -1;

var Bookmark =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {string|string[]} values single bookmark as string or multiple bookmarks as a string array.
   */
  function Bookmark(values) {
    (0, _classCallCheck2["default"])(this, Bookmark);
    this._values = asStringArray(values);
    this._maxValue = maxBookmark(this._values);
  }

  (0, _createClass2["default"])(Bookmark, [{
    key: "isEmpty",

    /**
     * Check if the given bookmark is meaningful and can be send to the database.
     * @return {boolean} returns `true` bookmark has a value, `false` otherwise.
     */
    value: function isEmpty() {
      return this._maxValue === null;
    }
    /**
     * Get maximum value of this bookmark as string.
     * @return {string|null} the maximum value or `null` if it is not defined.
     */

  }, {
    key: "maxBookmarkAsString",
    value: function maxBookmarkAsString() {
      return this._maxValue;
    }
    /**
     * Get all bookmark values as an array.
     * @return {string[]} all values.
     */

  }, {
    key: "values",
    value: function values() {
      return this._values;
    }
    /**
     * Get this bookmark as an object for begin transaction call.
     * @return {object} the value of this bookmark as object.
     */

  }, {
    key: "asBeginTransactionParameters",
    value: function asBeginTransactionParameters() {
      var _ref;

      if (this.isEmpty()) {
        return {};
      } // Driver sends {bookmark: "max", bookmarks: ["one", "two", "max"]} instead of simple
      // {bookmarks: ["one", "two", "max"]} for backwards compatibility reasons. Old servers can only accept single
      // bookmark that is why driver has to parse and compare given list of bookmarks. This functionality will
      // eventually be removed.


      return _ref = {}, (0, _defineProperty2["default"])(_ref, BOOKMARK_KEY, this._maxValue), (0, _defineProperty2["default"])(_ref, BOOKMARKS_KEY, this._values), _ref;
    }
  }], [{
    key: "empty",
    value: function empty() {
      return EMPTY_BOOKMARK;
    }
  }]);
  return Bookmark;
}();

exports["default"] = Bookmark;
var EMPTY_BOOKMARK = new Bookmark(null);
/**
 * Converts given value to an array.
 * @param {string|string[]} [value=undefined] argument to convert.
 * @return {string[]} value converted to an array.
 */

function asStringArray(value) {
  if (!value) {
    return [];
  }

  if (util.isString(value)) {
    return [value];
  }

  if (Array.isArray(value)) {
    var result = [];

    for (var i = 0; i < value.length; i++) {
      var element = value[i]; // if it is undefined or null, ignore it

      if (element !== undefined && element !== null) {
        if (!util.isString(element)) {
          throw new TypeError("Bookmark should be a string, given: '".concat(element, "'"));
        }

        result.push(element);
      }
    }

    return result;
  }

  throw new TypeError("Bookmark should either be a string or a string array, given: '".concat(value, "'"));
}
/**
 * Find latest bookmark in the given array of bookmarks.
 * @param {string[]} bookmarks array of bookmarks.
 * @return {string|null} latest bookmark value.
 */


function maxBookmark(bookmarks) {
  if (!bookmarks || bookmarks.length === 0) {
    return null;
  }

  var maxBookmark = bookmarks[0];
  var maxValue = bookmarkValue(maxBookmark);

  for (var i = 1; i < bookmarks.length; i++) {
    var bookmark = bookmarks[i];
    var value = bookmarkValue(bookmark);

    if (value > maxValue) {
      maxBookmark = bookmark;
      maxValue = value;
    }
  }

  return maxBookmark;
}
/**
 * Calculate numeric value for the given bookmark.
 * @param {string} bookmark argument to get numeric value for.
 * @return {number} value of the bookmark.
 */


function bookmarkValue(bookmark) {
  if (bookmark && bookmark.indexOf(BOOKMARK_PREFIX) === 0) {
    var result = parseInt(bookmark.substring(BOOKMARK_PREFIX.length));
    return result || UNKNOWN_BOOKMARK_VALUE;
  }

  return UNKNOWN_BOOKMARK_VALUE;
}

},{"./util":80,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/defineProperty":7,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/interopRequireWildcard":12}],37:[function(require,module,exports){
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

var _baseBuf = _interopRequireDefault(require("../buf/base-buf"));

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
var HeapBuffer =
/*#__PURE__*/
function (_BaseBuffer) {
  (0, _inherits2["default"])(HeapBuffer, _BaseBuffer);

  function HeapBuffer(arg) {
    var _this;

    (0, _classCallCheck2["default"])(this, HeapBuffer);
    var buffer = arg instanceof ArrayBuffer ? arg : new ArrayBuffer(arg);
    _this = (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(HeapBuffer).call(this, buffer.byteLength));
    _this._buffer = buffer;
    _this._view = new DataView(_this._buffer);
    return _this;
  }

  (0, _createClass2["default"])(HeapBuffer, [{
    key: "putUInt8",
    value: function putUInt8(position, val) {
      this._view.setUint8(position, val);
    }
  }, {
    key: "getUInt8",
    value: function getUInt8(position) {
      return this._view.getUint8(position);
    }
  }, {
    key: "putInt8",
    value: function putInt8(position, val) {
      this._view.setInt8(position, val);
    }
  }, {
    key: "getInt8",
    value: function getInt8(position) {
      return this._view.getInt8(position);
    }
  }, {
    key: "getFloat64",
    value: function getFloat64(position) {
      return this._view.getFloat64(position);
    }
  }, {
    key: "putFloat64",
    value: function putFloat64(position, val) {
      this._view.setFloat64(position, val);
    }
  }, {
    key: "getSlice",
    value: function getSlice(start, length) {
      if (this._buffer.slice) {
        return new HeapBuffer(this._buffer.slice(start, start + length));
      } else {
        // Some platforms (eg. phantomjs) don't support slice, so fall back to a copy
        // We do this rather than return a SliceBuffer, because sliceBuffer cannot
        // be passed to native network write ops etc - we need ArrayBuffer for that
        var copy = new HeapBuffer(length);

        for (var i = 0; i < length; i++) {
          copy.putUInt8(i, this.getUInt8(i + start));
        }

        return copy;
      }
    }
    /**
     * Specific to HeapBuffer, this gets a DataView from the
     * current position and of the specified length.
     */

  }, {
    key: "readView",
    value: function readView(length) {
      return new DataView(this._buffer, this._updatePos(length), length);
    }
  }]);
  return HeapBuffer;
}(_baseBuf["default"]);

exports["default"] = HeapBuffer;

},{"../buf/base-buf":42,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/getPrototypeOf":9,"@babel/runtime/helpers/inherits":10,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/possibleConstructorReturn":18}],38:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _browserBuf = _interopRequireDefault(require("./browser-buf"));

var _error = require("../../error");

var _util = require("../util");

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
 * Create a new WebSocketChannel to be used in web browsers.
 * @access private
 */
var WebSocketChannel =
/*#__PURE__*/
function () {
  /**
   * Create new instance
   * @param {ChannelConfig} config - configuration for this channel.
   * @param {function(): string} protocolSupplier - function that detects protocol of the web page. Should only be used in tests.
   */
  function WebSocketChannel(config) {
    var protocolSupplier = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : detectWebPageProtocol;
    (0, _classCallCheck2["default"])(this, WebSocketChannel);
    this._open = true;
    this._pending = [];
    this._error = null;
    this._handleConnectionError = this._handleConnectionError.bind(this);
    this._config = config;

    var _determineWebSocketSc = determineWebSocketScheme(config, protocolSupplier),
        scheme = _determineWebSocketSc.scheme,
        error = _determineWebSocketSc.error;

    if (error) {
      this._error = error;
      return;
    }

    this._ws = createWebSocket(scheme, config.address);
    this._ws.binaryType = 'arraybuffer';
    var self = this; // All connection errors are not sent to the error handler
    // we must also check for dirty close calls

    this._ws.onclose = function (e) {
      if (!e.wasClean) {
        self._handleConnectionError();
      }
    };

    this._ws.onopen = function () {
      // Connected! Cancel the connection timeout
      self._clearConnectionTimeout(); // Drain all pending messages


      var pending = self._pending;
      self._pending = null;

      for (var i = 0; i < pending.length; i++) {
        self.write(pending[i]);
      }
    };

    this._ws.onmessage = function (event) {
      if (self.onmessage) {
        var b = new _browserBuf["default"](event.data);
        self.onmessage(b);
      }
    };

    this._ws.onerror = this._handleConnectionError;
    this._connectionTimeoutFired = false;
    this._connectionTimeoutId = this._setupConnectionTimeout();
  }

  (0, _createClass2["default"])(WebSocketChannel, [{
    key: "_handleConnectionError",
    value: function _handleConnectionError() {
      if (this._connectionTimeoutFired) {
        // timeout fired - not connected within configured time
        this._error = (0, _error.newError)("Failed to establish connection in ".concat(this._config.connectionTimeout, "ms"), this._config.connectionErrorCode);

        if (this.onerror) {
          this.onerror(this._error);
        }

        return;
      } // onerror triggers on websocket close as well.. don't get me started.


      if (this._open) {
        // http://stackoverflow.com/questions/25779831/how-to-catch-websocket-connection-to-ws-xxxnn-failed-connection-closed-be
        this._error = (0, _error.newError)('WebSocket connection failure. Due to security ' + 'constraints in your web browser, the reason for the failure is not available ' + 'to this Neo4j Driver. Please use your browsers development console to determine ' + 'the root cause of the failure. Common reasons include the database being ' + 'unavailable, using the wrong connection URL or temporary network problems. ' + 'If you have enabled encryption, ensure your browser is configured to trust the ' + 'certificate Neo4j is configured to use. WebSocket `readyState` is: ' + this._ws.readyState, this._config.connectionErrorCode);

        if (this.onerror) {
          this.onerror(this._error);
        }
      }
    }
    /**
     * Write the passed in buffer to connection
     * @param {HeapBuffer} buffer - Buffer to write
     */

  }, {
    key: "write",
    value: function write(buffer) {
      // If there is a pending queue, push this on that queue. This means
      // we are not yet connected, so we queue things locally.
      if (this._pending !== null) {
        this._pending.push(buffer);
      } else if (buffer instanceof _browserBuf["default"]) {
        this._ws.send(buffer._buffer);
      } else {
        throw (0, _error.newError)("Don't know how to send buffer: " + buffer);
      }
    }
    /**
     * Close the connection
     * @param {function} cb - Function to call on close.
     */

  }, {
    key: "close",
    value: function close() {
      var cb = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : function () {
        return null;
      };
      this._open = false;

      this._clearConnectionTimeout();

      this._ws.close();

      this._ws.onclose = cb;
    }
    /**
     * Set connection timeout on the given WebSocket, if configured.
     * @return {number} the timeout id or null.
     * @private
     */

  }, {
    key: "_setupConnectionTimeout",
    value: function _setupConnectionTimeout() {
      var _this = this;

      var timeout = this._config.connectionTimeout;

      if (timeout) {
        var webSocket = this._ws;
        return setTimeout(function () {
          if (webSocket.readyState !== WebSocket.OPEN) {
            _this._connectionTimeoutFired = true;
            webSocket.close();
          }
        }, timeout);
      }

      return null;
    }
    /**
     * Remove active connection timeout, if any.
     * @private
     */

  }, {
    key: "_clearConnectionTimeout",
    value: function _clearConnectionTimeout() {
      var timeoutId = this._connectionTimeoutId;

      if (timeoutId || timeoutId === 0) {
        this._connectionTimeoutFired = false;
        this._connectionTimeoutId = null;
        clearTimeout(timeoutId);
      }
    }
  }]);
  return WebSocketChannel;
}();

exports["default"] = WebSocketChannel;

function createWebSocket(scheme, address) {
  var url = scheme + '://' + address.asHostPort();

  try {
    return new WebSocket(url);
  } catch (error) {
    if (isIPv6AddressIssueOnWindows(error, address)) {
      // WebSocket in IE and Edge browsers on Windows do not support regular IPv6 address syntax because they contain ':'.
      // It's an invalid character for UNC (https://en.wikipedia.org/wiki/IPv6_address#Literal_IPv6_addresses_in_UNC_path_names)
      // and Windows requires IPv6 to be changes in the following way:
      //   1) replace all ':' with '-'
      //   2) replace '%' with 's' for link-local address
      //   3) append '.ipv6-literal.net' suffix
      // only then resulting string can be considered a valid IPv6 address. Yes, this is extremely weird!
      // For more details see:
      //   https://social.msdn.microsoft.com/Forums/ie/en-US/06cca73b-63c2-4bf9-899b-b229c50449ff/whether-ie10-websocket-support-ipv6?forum=iewebdevelopment
      //   https://www.itdojo.com/ipv6-addresses-and-unc-path-names-overcoming-illegal/
      // Creation of WebSocket with unconverted address results in SyntaxError without message or stacktrace.
      // That is why here we "catch" SyntaxError and rewrite IPv6 address if needed.
      var windowsFriendlyUrl = asWindowsFriendlyIPv6Address(scheme, address);
      return new WebSocket(windowsFriendlyUrl);
    } else {
      throw error;
    }
  }
}

function isIPv6AddressIssueOnWindows(error, address) {
  return error.name === 'SyntaxError' && isIPv6Address(address.asHostPort());
}

function isIPv6Address(hostAndPort) {
  return hostAndPort.charAt(0) === '[' && hostAndPort.indexOf(']') !== -1;
}

function asWindowsFriendlyIPv6Address(scheme, address) {
  // replace all ':' with '-'
  var hostWithoutColons = address.host().replace(new RegExp(':', 'g'), '-'); // replace '%' with 's' for link-local IPv6 address like 'fe80::1%lo0'

  var hostWithoutPercent = hostWithoutColons.replace('%', 's'); // append magic '.ipv6-literal.net' suffix

  var ipv6Host = hostWithoutPercent + '.ipv6-literal.net';
  return "".concat(scheme, "://").concat(ipv6Host, ":").concat(address.port());
}
/**
 * @param {ChannelConfig} config - configuration for the channel.
 * @param {function(): string} protocolSupplier - function that detects protocol of the web page.
 * @return {{scheme: string|null, error: Neo4jError|null}} object containing either scheme or error.
 */


function determineWebSocketScheme(config, protocolSupplier) {
  var encryptionOn = isEncryptionExplicitlyTurnedOn(config);
  var encryptionOff = isEncryptionExplicitlyTurnedOff(config);
  var trust = config.trust;
  var secureProtocol = isProtocolSecure(protocolSupplier);
  verifyEncryptionSettings(encryptionOn, encryptionOff, secureProtocol);

  if (encryptionOff) {
    // encryption explicitly turned off in the config
    return {
      scheme: 'ws',
      error: null
    };
  }

  if (secureProtocol) {
    // driver is used in a secure https web page, use 'wss'
    return {
      scheme: 'wss',
      error: null
    };
  }

  if (encryptionOn) {
    // encryption explicitly requested in the config
    if (!trust || trust === 'TRUST_CUSTOM_CA_SIGNED_CERTIFICATES') {
      // trust strategy not specified or the only supported strategy is specified
      return {
        scheme: 'wss',
        error: null
      };
    } else {
      var error = (0, _error.newError)('The browser version of this driver only supports one trust ' + "strategy, 'TRUST_CUSTOM_CA_SIGNED_CERTIFICATES'. " + trust + ' is not supported. Please ' + 'either use TRUST_CUSTOM_CA_SIGNED_CERTIFICATES or disable encryption by setting ' + '`encrypted:"' + _util.ENCRYPTION_OFF + '"` in the driver configuration.');
      return {
        scheme: null,
        error: error
      };
    }
  } // default to unencrypted web socket


  return {
    scheme: 'ws',
    error: null
  };
}
/**
 * @param {ChannelConfig} config - configuration for the channel.
 * @return {boolean} `true` if encryption enabled in the config, `false` otherwise.
 */


function isEncryptionExplicitlyTurnedOn(config) {
  return config.encrypted === true || config.encrypted === _util.ENCRYPTION_ON;
}
/**
 * @param {ChannelConfig} config - configuration for the channel.
 * @return {boolean} `true` if encryption disabled in the config, `false` otherwise.
 */


function isEncryptionExplicitlyTurnedOff(config) {
  return config.encrypted === false || config.encrypted === _util.ENCRYPTION_OFF;
}
/**
 * @param {function(): string} protocolSupplier - function that detects protocol of the web page.
 * @return {boolean} `true` if protocol returned by the given function is secure, `false` otherwise.
 */


function isProtocolSecure(protocolSupplier) {
  var protocol = typeof protocolSupplier === 'function' ? protocolSupplier() : '';
  return protocol && protocol.toLowerCase().indexOf('https') >= 0;
}

function verifyEncryptionSettings(encryptionOn, encryptionOff, secureProtocol) {
  if (encryptionOn && !secureProtocol) {
    // encryption explicitly turned on for a driver used on a HTTP web page
    console.warn('Neo4j driver is configured to use secure WebSocket on a HTTP web page. ' + 'WebSockets might not work in a mixed content environment. ' + 'Please consider configuring driver to not use encryption.');
  } else if (encryptionOff && secureProtocol) {
    // encryption explicitly turned off for a driver used on a HTTPS web page
    console.warn('Neo4j driver is configured to use insecure WebSocket on a HTTPS web page. ' + 'WebSockets might not work in a mixed content environment. ' + 'Please consider configuring driver to use encryption.');
  }
}

function detectWebPageProtocol() {
  return typeof window !== 'undefined' && window.location ? window.location.protocol : null;
}

},{"../../error":29,"../util":80,"./browser-buf":37,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11}],39:[function(require,module,exports){
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

var _baseHostNameResolver = _interopRequireDefault(require("../resolver/base-host-name-resolver"));

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
var BrowserHostNameResolver =
/*#__PURE__*/
function (_BaseHostNameResolver) {
  (0, _inherits2["default"])(BrowserHostNameResolver, _BaseHostNameResolver);

  function BrowserHostNameResolver() {
    (0, _classCallCheck2["default"])(this, BrowserHostNameResolver);
    return (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(BrowserHostNameResolver).apply(this, arguments));
  }

  (0, _createClass2["default"])(BrowserHostNameResolver, [{
    key: "resolve",
    value: function resolve(address) {
      return this._resolveToItself(address);
    }
  }]);
  return BrowserHostNameResolver;
}(_baseHostNameResolver["default"]);

exports["default"] = BrowserHostNameResolver;

},{"../resolver/base-host-name-resolver":67,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/getPrototypeOf":9,"@babel/runtime/helpers/inherits":10,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/possibleConstructorReturn":18}],40:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _browserBuf = _interopRequireDefault(require("../browser/browser-buf"));

var _textEncodingUtf = require("text-encoding-utf-8");

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
var encoder = new _textEncodingUtf.TextEncoder('utf-8');
var decoder = new _textEncodingUtf.TextDecoder('utf-8');

function encode(str) {
  return new _browserBuf["default"](encoder.encode(str).buffer);
}

function decode(buffer, length) {
  if (buffer instanceof _browserBuf["default"]) {
    return decoder.decode(buffer.readView(Math.min(length, buffer.length - buffer.position)));
  } else {
    // Copy the given buffer into a regular buffer and decode that
    var tmpBuf = new _browserBuf["default"](length);

    for (var i = 0; i < length; i++) {
      tmpBuf.writeUInt8(buffer.readUInt8());
    }

    tmpBuf.reset();
    return decoder.decode(tmpBuf.readView(length));
  }
}

var _default = {
  encode: encode,
  decode: decode
};
exports["default"] = _default;

},{"../browser/browser-buf":37,"@babel/runtime/helpers/interopRequireDefault":11,"text-encoding-utf-8":25}],41:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.utf8 = exports.HostNameResolver = exports.Channel = exports.alloc = void 0;

var _browserBuf = _interopRequireDefault(require("./browser-buf"));

var _browserChannel = _interopRequireDefault(require("./browser-channel"));

var _browserHostNameResolver = _interopRequireDefault(require("./browser-host-name-resolver"));

var _browserUtf = _interopRequireDefault(require("./browser-utf8"));

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

This module exports a set of components to be used in browser environment.
They are not compatible with NodeJS environment.
All files import/require APIs from `node/index.js` by default.
Such imports are replaced at build time with `browser/index.js` when building a browser bundle.

NOTE: exports in this module should have exactly the same names/structure as exports in `node/index.js`.

 */
var alloc = function alloc(arg) {
  return new _browserBuf["default"](arg);
};

exports.alloc = alloc;
var Channel = _browserChannel["default"];
exports.Channel = Channel;
var HostNameResolver = _browserHostNameResolver["default"];
exports.HostNameResolver = HostNameResolver;
var utf8 = _browserUtf["default"];
exports.utf8 = utf8;

},{"./browser-buf":37,"./browser-channel":38,"./browser-host-name-resolver":39,"./browser-utf8":40,"@babel/runtime/helpers/interopRequireDefault":11}],42:[function(require,module,exports){
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
 * Common base with default implementation for most buffer methods.
 * Buffers are stateful - they track a current "position", this helps greatly
 * when reading and writing from them incrementally. You can also ignore the
 * stateful read/write methods.
 * readXXX and writeXXX-methods move the inner position of the buffer.
 * putXXX and getXXX-methods do not.
 * @access private
 */
var BaseBuffer =
/*#__PURE__*/
function () {
  /**
   * Create a instance with the injected size.
   * @constructor
   * @param {Integer} size
   */
  function BaseBuffer(size) {
    (0, _classCallCheck2["default"])(this, BaseBuffer);
    this.position = 0;
    this.length = size;
  }

  (0, _createClass2["default"])(BaseBuffer, [{
    key: "getUInt8",
    value: function getUInt8(position) {
      throw new Error('Not implemented');
    }
  }, {
    key: "getInt8",
    value: function getInt8(position) {
      throw new Error('Not implemented');
    }
  }, {
    key: "getFloat64",
    value: function getFloat64(position) {
      throw new Error('Not implemented');
    }
  }, {
    key: "putUInt8",
    value: function putUInt8(position, val) {
      throw new Error('Not implemented');
    }
  }, {
    key: "putInt8",
    value: function putInt8(position, val) {
      throw new Error('Not implemented');
    }
  }, {
    key: "putFloat64",
    value: function putFloat64(position, val) {
      throw new Error('Not implemented');
    }
    /**
     * @param p
     */

  }, {
    key: "getInt16",
    value: function getInt16(p) {
      return this.getInt8(p) << 8 | this.getUInt8(p + 1);
    }
    /**
     * @param p
     */

  }, {
    key: "getUInt16",
    value: function getUInt16(p) {
      return this.getUInt8(p) << 8 | this.getUInt8(p + 1);
    }
    /**
     * @param p
     */

  }, {
    key: "getInt32",
    value: function getInt32(p) {
      return this.getInt8(p) << 24 | this.getUInt8(p + 1) << 16 | this.getUInt8(p + 2) << 8 | this.getUInt8(p + 3);
    }
    /**
     * @param p
     */

  }, {
    key: "getUInt32",
    value: function getUInt32(p) {
      return this.getUInt8(p) << 24 | this.getUInt8(p + 1) << 16 | this.getUInt8(p + 2) << 8 | this.getUInt8(p + 3);
    }
    /**
     * @param p
     */

  }, {
    key: "getInt64",
    value: function getInt64(p) {
      return this.getInt8(p) << 56 | this.getUInt8(p + 1) << 48 | this.getUInt8(p + 2) << 40 | this.getUInt8(p + 3) << 32 | this.getUInt8(p + 4) << 24 | this.getUInt8(p + 5) << 16 | this.getUInt8(p + 6) << 8 | this.getUInt8(p + 7);
    }
    /**
     * Get a slice of this buffer. This method does not copy any data,
     * but simply provides a slice view of this buffer
     * @param start
     * @param length
     */

  }, {
    key: "getSlice",
    value: function getSlice(start, length) {
      return new SliceBuffer(start, length, this);
    }
    /**
     * @param p
     * @param val
     */

  }, {
    key: "putInt16",
    value: function putInt16(p, val) {
      this.putInt8(p, val >> 8);
      this.putUInt8(p + 1, val & 0xff);
    }
    /**
     * @param p
     * @param val
     */

  }, {
    key: "putUInt16",
    value: function putUInt16(p, val) {
      this.putUInt8(p, val >> 8 & 0xff);
      this.putUInt8(p + 1, val & 0xff);
    }
    /**
     * @param p
     * @param val
     */

  }, {
    key: "putInt32",
    value: function putInt32(p, val) {
      this.putInt8(p, val >> 24);
      this.putUInt8(p + 1, val >> 16 & 0xff);
      this.putUInt8(p + 2, val >> 8 & 0xff);
      this.putUInt8(p + 3, val & 0xff);
    }
    /**
     * @param p
     * @param val
     */

  }, {
    key: "putUInt32",
    value: function putUInt32(p, val) {
      this.putUInt8(p, val >> 24 & 0xff);
      this.putUInt8(p + 1, val >> 16 & 0xff);
      this.putUInt8(p + 2, val >> 8 & 0xff);
      this.putUInt8(p + 3, val & 0xff);
    }
    /**
     * @param p
     * @param val
     */

  }, {
    key: "putInt64",
    value: function putInt64(p, val) {
      this.putInt8(p, val >> 48);
      this.putUInt8(p + 1, val >> 42 & 0xff);
      this.putUInt8(p + 2, val >> 36 & 0xff);
      this.putUInt8(p + 3, val >> 30 & 0xff);
      this.putUInt8(p + 4, val >> 24 & 0xff);
      this.putUInt8(p + 5, val >> 16 & 0xff);
      this.putUInt8(p + 6, val >> 8 & 0xff);
      this.putUInt8(p + 7, val & 0xff);
    }
    /**
     * @param position
     * @param other
     */

  }, {
    key: "putBytes",
    value: function putBytes(position, other) {
      for (var i = 0, end = other.remaining(); i < end; i++) {
        this.putUInt8(position + i, other.readUInt8());
      }
    }
    /**
     * Read from state position.
     */

  }, {
    key: "readUInt8",
    value: function readUInt8() {
      return this.getUInt8(this._updatePos(1));
    }
    /**
     * Read from state position.
     */

  }, {
    key: "readInt8",
    value: function readInt8() {
      return this.getInt8(this._updatePos(1));
    }
    /**
     * Read from state position.
     */

  }, {
    key: "readUInt16",
    value: function readUInt16() {
      return this.getUInt16(this._updatePos(2));
    }
    /**
     * Read from state position.
     */

  }, {
    key: "readUInt32",
    value: function readUInt32() {
      return this.getUInt32(this._updatePos(4));
    }
    /**
     * Read from state position.
     */

  }, {
    key: "readInt16",
    value: function readInt16() {
      return this.getInt16(this._updatePos(2));
    }
    /**
     * Read from state position.
     */

  }, {
    key: "readInt32",
    value: function readInt32() {
      return this.getInt32(this._updatePos(4));
    }
    /**
     * Read from state position.
     */

  }, {
    key: "readInt64",
    value: function readInt64() {
      return this.getInt32(this._updatePos(8));
    }
    /**
     * Read from state position.
     */

  }, {
    key: "readFloat64",
    value: function readFloat64() {
      return this.getFloat64(this._updatePos(8));
    }
    /**
     * Write to state position.
     * @param val
     */

  }, {
    key: "writeUInt8",
    value: function writeUInt8(val) {
      this.putUInt8(this._updatePos(1), val);
    }
    /**
     * Write to state position.
     * @param val
     */

  }, {
    key: "writeInt8",
    value: function writeInt8(val) {
      this.putInt8(this._updatePos(1), val);
    }
    /**
     * Write to state position.
     * @param val
     */

  }, {
    key: "writeInt16",
    value: function writeInt16(val) {
      this.putInt16(this._updatePos(2), val);
    }
    /**
     * Write to state position.
     * @param val
     */

  }, {
    key: "writeInt32",
    value: function writeInt32(val) {
      this.putInt32(this._updatePos(4), val);
    }
    /**
     * Write to state position.
     * @param val
     */

  }, {
    key: "writeUInt32",
    value: function writeUInt32(val) {
      this.putUInt32(this._updatePos(4), val);
    }
    /**
     * Write to state position.
     * @param val
     */

  }, {
    key: "writeInt64",
    value: function writeInt64(val) {
      this.putInt64(this._updatePos(8), val);
    }
    /**
     * Write to state position.
     * @param val
     */

  }, {
    key: "writeFloat64",
    value: function writeFloat64(val) {
      this.putFloat64(this._updatePos(8), val);
    }
    /**
     * Write to state position.
     * @param val
     */

  }, {
    key: "writeBytes",
    value: function writeBytes(val) {
      this.putBytes(this._updatePos(val.remaining()), val);
    }
    /**
     * Get a slice of this buffer. This method does not copy any data,
     * but simply provides a slice view of this buffer
     * @param length
     */

  }, {
    key: "readSlice",
    value: function readSlice(length) {
      return this.getSlice(this._updatePos(length), length);
    }
  }, {
    key: "_updatePos",
    value: function _updatePos(length) {
      var p = this.position;
      this.position += length;
      return p;
    }
    /**
     * Get remaining
     */

  }, {
    key: "remaining",
    value: function remaining() {
      return this.length - this.position;
    }
    /**
     * Has remaining
     */

  }, {
    key: "hasRemaining",
    value: function hasRemaining() {
      return this.remaining() > 0;
    }
    /**
     * Reset position state
     */

  }, {
    key: "reset",
    value: function reset() {
      this.position = 0;
    }
    /**
     * Get string representation of buffer and it's state.
     * @return {string} Buffer as a string
     */

  }, {
    key: "toString",
    value: function toString() {
      return this.constructor.name + '( position=' + this.position + ' )\n  ' + this.toHex();
    }
    /**
     * Get string representation of buffer.
     * @return {string} Buffer as a string
     */

  }, {
    key: "toHex",
    value: function toHex() {
      var out = '';

      for (var i = 0; i < this.length; i++) {
        var hexByte = this.getUInt8(i).toString(16);

        if (hexByte.length === 1) {
          hexByte = '0' + hexByte;
        }

        out += hexByte;

        if (i !== this.length - 1) {
          out += ' ';
        }
      }

      return out;
    }
  }]);
  return BaseBuffer;
}();
/**
 * Represents a view as slice of another buffer.
 * @access private
 */


exports["default"] = BaseBuffer;

var SliceBuffer =
/*#__PURE__*/
function (_BaseBuffer) {
  (0, _inherits2["default"])(SliceBuffer, _BaseBuffer);

  function SliceBuffer(start, length, inner) {
    var _this;

    (0, _classCallCheck2["default"])(this, SliceBuffer);
    _this = (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(SliceBuffer).call(this, length));
    _this._start = start;
    _this._inner = inner;
    return _this;
  }

  (0, _createClass2["default"])(SliceBuffer, [{
    key: "putUInt8",
    value: function putUInt8(position, val) {
      this._inner.putUInt8(this._start + position, val);
    }
  }, {
    key: "getUInt8",
    value: function getUInt8(position) {
      return this._inner.getUInt8(this._start + position);
    }
  }, {
    key: "putInt8",
    value: function putInt8(position, val) {
      this._inner.putInt8(this._start + position, val);
    }
  }, {
    key: "putFloat64",
    value: function putFloat64(position, val) {
      this._inner.putFloat64(this._start + position, val);
    }
  }, {
    key: "getInt8",
    value: function getInt8(position) {
      return this._inner.getInt8(this._start + position);
    }
  }, {
    key: "getFloat64",
    value: function getFloat64(position) {
      return this._inner.getFloat64(this._start + position);
    }
  }]);
  return SliceBuffer;
}(BaseBuffer);

},{"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/getPrototypeOf":9,"@babel/runtime/helpers/inherits":10,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/possibleConstructorReturn":18}],43:[function(require,module,exports){
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

var _baseBuf = _interopRequireDefault(require("./base-buf"));

var _node = require('../browser');

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
 * Buffer that combines multiple buffers, exposing them as one single buffer.
 */
var CombinedBuffer =
/*#__PURE__*/
function (_BaseBuffer) {
  (0, _inherits2["default"])(CombinedBuffer, _BaseBuffer);

  function CombinedBuffer(buffers) {
    var _this;

    (0, _classCallCheck2["default"])(this, CombinedBuffer);
    var length = 0;

    for (var i = 0; i < buffers.length; i++) {
      length += buffers[i].length;
    }

    _this = (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(CombinedBuffer).call(this, length));
    _this._buffers = buffers;
    return _this;
  }

  (0, _createClass2["default"])(CombinedBuffer, [{
    key: "getUInt8",
    value: function getUInt8(position) {
      // Surely there's a faster way to do this.. some sort of lookup table thing?
      for (var i = 0; i < this._buffers.length; i++) {
        var buffer = this._buffers[i]; // If the position is not in the current buffer, skip the current buffer

        if (position >= buffer.length) {
          position -= buffer.length;
        } else {
          return buffer.getUInt8(position);
        }
      }
    }
  }, {
    key: "getInt8",
    value: function getInt8(position) {
      // Surely there's a faster way to do this.. some sort of lookup table thing?
      for (var i = 0; i < this._buffers.length; i++) {
        var buffer = this._buffers[i]; // If the position is not in the current buffer, skip the current buffer

        if (position >= buffer.length) {
          position -= buffer.length;
        } else {
          return buffer.getInt8(position);
        }
      }
    }
  }, {
    key: "getFloat64",
    value: function getFloat64(position) {
      // At some point, a more efficient impl. For now, we copy the 8 bytes
      // we want to read and depend on the platform impl of IEEE 754.
      var b = (0, _node.alloc)(8);

      for (var i = 0; i < 8; i++) {
        b.putUInt8(i, this.getUInt8(position + i));
      }

      return b.getFloat64(0);
    }
  }]);
  return CombinedBuffer;
}(_baseBuf["default"]);

exports["default"] = CombinedBuffer;

},{"../browser":41,"./base-buf":42,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/getPrototypeOf":9,"@babel/runtime/helpers/inherits":10,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/possibleConstructorReturn":18}],44:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _error = require("../error");

var _util = require("./util");

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
var DEFAULT_CONNECTION_TIMEOUT_MILLIS = 5000; // 5 seconds by default

var ALLOWED_VALUES_ENCRYPTED = [null, undefined, true, false, _util.ENCRYPTION_ON, _util.ENCRYPTION_OFF];
var ALLOWED_VALUES_TRUST = [null, undefined, 'TRUST_ALL_CERTIFICATES', 'TRUST_ON_FIRST_USE', 'TRUST_SIGNED_CERTIFICATES', 'TRUST_CUSTOM_CA_SIGNED_CERTIFICATES', 'TRUST_SYSTEM_CA_SIGNED_CERTIFICATES'];

var ChannelConfig =
/**
 * @constructor
 * @param {ServerAddress} address the address for the channel to connect to.
 * @param {object} driverConfig the driver config provided by the user when driver is created.
 * @param {string} connectionErrorCode the default error code to use on connection errors.
 */
function ChannelConfig(address, driverConfig, connectionErrorCode) {
  (0, _classCallCheck2["default"])(this, ChannelConfig);
  this.address = address;
  this.encrypted = extractEncrypted(driverConfig);
  this.trust = extractTrust(driverConfig);
  this.trustedCertificates = extractTrustedCertificates(driverConfig);
  this.knownHostsPath = extractKnownHostsPath(driverConfig);
  this.connectionErrorCode = connectionErrorCode || _error.SERVICE_UNAVAILABLE;
  this.connectionTimeout = extractConnectionTimeout(driverConfig);
};

exports["default"] = ChannelConfig;

function extractEncrypted(driverConfig) {
  var value = driverConfig.encrypted;

  if (ALLOWED_VALUES_ENCRYPTED.indexOf(value) === -1) {
    throw (0, _error.newError)("Illegal value of the encrypted setting ".concat(value, ". Expected one of ").concat(ALLOWED_VALUES_ENCRYPTED));
  }

  return value;
}

function extractTrust(driverConfig) {
  var value = driverConfig.trust;

  if (ALLOWED_VALUES_TRUST.indexOf(value) === -1) {
    throw (0, _error.newError)("Illegal value of the trust setting ".concat(value, ". Expected one of ").concat(ALLOWED_VALUES_TRUST));
  }

  return value;
}

function extractTrustedCertificates(driverConfig) {
  return driverConfig.trustedCertificates || [];
}

function extractKnownHostsPath(driverConfig) {
  return driverConfig.knownHosts || null;
}

function extractConnectionTimeout(driverConfig) {
  var configuredTimeout = parseInt(driverConfig.connectionTimeout, 10);

  if (configuredTimeout === 0) {
    // timeout explicitly configured to 0
    return null;
  } else if (configuredTimeout && configuredTimeout < 0) {
    // timeout explicitly configured to a negative value
    return null;
  } else if (!configuredTimeout) {
    // timeout not configured, use default value
    return DEFAULT_CONNECTION_TIMEOUT_MILLIS;
  } else {
    // timeout configured, use the provided value
    return configuredTimeout;
  }
}

},{"../error":29,"./util":80,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/interopRequireDefault":11}],45:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.Dechunker = exports.Chunker = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _possibleConstructorReturn2 = _interopRequireDefault(require("@babel/runtime/helpers/possibleConstructorReturn"));

var _getPrototypeOf2 = _interopRequireDefault(require("@babel/runtime/helpers/getPrototypeOf"));

var _inherits2 = _interopRequireDefault(require("@babel/runtime/helpers/inherits"));

var _baseBuf = _interopRequireDefault(require("./buf/base-buf"));

var _node = require('./browser');

var _combinedBuf = _interopRequireDefault(require("./buf/combined-buf"));

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
var _CHUNK_HEADER_SIZE = 2;
var _MESSAGE_BOUNDARY = 0x00;
var _DEFAULT_BUFFER_SIZE = 1400; // http://stackoverflow.com/questions/2613734/maximum-packet-size-for-a-tcp-connection

/**
 * Looks like a writable buffer, chunks output transparently into a channel below.
 * @access private
 */

var Chunker =
/*#__PURE__*/
function (_BaseBuffer) {
  (0, _inherits2["default"])(Chunker, _BaseBuffer);

  function Chunker(channel, bufferSize) {
    var _this;

    (0, _classCallCheck2["default"])(this, Chunker);
    _this = (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(Chunker).call(this, 0));
    _this._bufferSize = bufferSize || _DEFAULT_BUFFER_SIZE;
    _this._ch = channel;
    _this._buffer = (0, _node.alloc)(_this._bufferSize);
    _this._currentChunkStart = 0;
    _this._chunkOpen = false;
    return _this;
  }

  (0, _createClass2["default"])(Chunker, [{
    key: "putUInt8",
    value: function putUInt8(position, val) {
      this._ensure(1);

      this._buffer.writeUInt8(val);
    }
  }, {
    key: "putInt8",
    value: function putInt8(position, val) {
      this._ensure(1);

      this._buffer.writeInt8(val);
    }
  }, {
    key: "putFloat64",
    value: function putFloat64(position, val) {
      this._ensure(8);

      this._buffer.writeFloat64(val);
    }
  }, {
    key: "putBytes",
    value: function putBytes(position, data) {
      // TODO: If data is larger than our chunk size or so, we're very likely better off just passing this buffer on
      // rather than doing the copy here TODO: *however* note that we need some way to find out when the data has been
      // written (and thus the buffer can be re-used) if we take that approach
      while (data.remaining() > 0) {
        // Ensure there is an open chunk, and that it has at least one byte of space left
        this._ensure(1);

        if (this._buffer.remaining() > data.remaining()) {
          this._buffer.writeBytes(data);
        } else {
          this._buffer.writeBytes(data.readSlice(this._buffer.remaining()));
        }
      }

      return this;
    }
  }, {
    key: "flush",
    value: function flush() {
      if (this._buffer.position > 0) {
        this._closeChunkIfOpen(); // Local copy and clear the buffer field. This ensures that the buffer is not re-released if the flush call fails


        var out = this._buffer;
        this._buffer = null;

        this._ch.write(out.getSlice(0, out.position)); // Alloc a new output buffer. We assume we're using NodeJS's buffer pooling under the hood here!


        this._buffer = (0, _node.alloc)(this._bufferSize);
        this._chunkOpen = false;
      }

      return this;
    }
    /**
     * Bolt messages are encoded in one or more chunks, and the boundary between two messages
     * is encoded as a 0-length chunk, `00 00`. This inserts such a message boundary, closing
     * any currently open chunk as needed
     */

  }, {
    key: "messageBoundary",
    value: function messageBoundary() {
      this._closeChunkIfOpen();

      if (this._buffer.remaining() < _CHUNK_HEADER_SIZE) {
        this.flush();
      } // Write message boundary


      this._buffer.writeInt16(_MESSAGE_BOUNDARY);
    }
    /** Ensure at least the given size is available for writing */

  }, {
    key: "_ensure",
    value: function _ensure(size) {
      var toWriteSize = this._chunkOpen ? size : size + _CHUNK_HEADER_SIZE;

      if (this._buffer.remaining() < toWriteSize) {
        this.flush();
      }

      if (!this._chunkOpen) {
        this._currentChunkStart = this._buffer.position;
        this._buffer.position = this._buffer.position + _CHUNK_HEADER_SIZE;
        this._chunkOpen = true;
      }
    }
  }, {
    key: "_closeChunkIfOpen",
    value: function _closeChunkIfOpen() {
      if (this._chunkOpen) {
        var chunkSize = this._buffer.position - (this._currentChunkStart + _CHUNK_HEADER_SIZE);

        this._buffer.putUInt16(this._currentChunkStart, chunkSize);

        this._chunkOpen = false;
      }
    }
  }]);
  return Chunker;
}(_baseBuf["default"]);
/**
 * Combines chunks until a complete message is gathered up, and then forwards that
 * message to an 'onmessage' listener.
 * @access private
 */


exports.Chunker = Chunker;

var Dechunker =
/*#__PURE__*/
function () {
  function Dechunker() {
    (0, _classCallCheck2["default"])(this, Dechunker);
    this._currentMessage = [];
    this._partialChunkHeader = 0;
    this._state = this.AWAITING_CHUNK;
  }

  (0, _createClass2["default"])(Dechunker, [{
    key: "AWAITING_CHUNK",
    value: function AWAITING_CHUNK(buf) {
      if (buf.remaining() >= 2) {
        // Whole header available, read that
        return this._onHeader(buf.readUInt16());
      } else {
        // Only one byte available, read that and wait for the second byte
        this._partialChunkHeader = buf.readUInt8() << 8;
        return this.IN_HEADER;
      }
    }
  }, {
    key: "IN_HEADER",
    value: function IN_HEADER(buf) {
      // First header byte read, now we read the next one
      return this._onHeader((this._partialChunkHeader | buf.readUInt8()) & 0xffff);
    }
  }, {
    key: "IN_CHUNK",
    value: function IN_CHUNK(buf) {
      if (this._chunkSize <= buf.remaining()) {
        // Current packet is larger than current chunk, or same size:
        this._currentMessage.push(buf.readSlice(this._chunkSize));

        return this.AWAITING_CHUNK;
      } else {
        // Current packet is smaller than the chunk we're reading, split the current chunk itself up
        this._chunkSize -= buf.remaining();

        this._currentMessage.push(buf.readSlice(buf.remaining()));

        return this.IN_CHUNK;
      }
    }
  }, {
    key: "CLOSED",
    value: function CLOSED(buf) {} // no-op

    /** Called when a complete chunk header has been received */

  }, {
    key: "_onHeader",
    value: function _onHeader(header) {
      if (header === 0) {
        // Message boundary
        var message;

        if (this._currentMessage.length === 1) {
          message = this._currentMessage[0];
        } else {
          message = new _combinedBuf["default"](this._currentMessage);
        }

        this._currentMessage = [];
        this.onmessage(message);
        return this.AWAITING_CHUNK;
      } else {
        this._chunkSize = header;
        return this.IN_CHUNK;
      }
    }
  }, {
    key: "write",
    value: function write(buf) {
      while (buf.hasRemaining()) {
        this._state = this._state(buf);
      }
    }
  }]);
  return Dechunker;
}();

exports.Dechunker = Dechunker;

},{"./browser":41,"./buf/base-buf":42,"./buf/combined-buf":43,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/getPrototypeOf":9,"@babel/runtime/helpers/inherits":10,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/possibleConstructorReturn":18}],46:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _error = require("../error");

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
var ConnectionErrorHandler =
/*#__PURE__*/
function () {
  function ConnectionErrorHandler(errorCode, handleUnavailability, handleWriteFailure) {
    (0, _classCallCheck2["default"])(this, ConnectionErrorHandler);
    this._errorCode = errorCode;
    this._handleUnavailability = handleUnavailability || noOpHandler;
    this._handleWriteFailure = handleWriteFailure || noOpHandler;
  }
  /**
   * Error code to use for network errors.
   * @return {string} the error code.
   */


  (0, _createClass2["default"])(ConnectionErrorHandler, [{
    key: "errorCode",
    value: function errorCode() {
      return this._errorCode;
    }
    /**
     * Handle and transform the error.
     * @param {Neo4jError} error the original error.
     * @param {ServerAddress} address the address of the connection where the error happened.
     * @return {Neo4jError} new error that should be propagated to the user.
     */

  }, {
    key: "handleAndTransformError",
    value: function handleAndTransformError(error, address) {
      if (isAvailabilityError(error)) {
        return this._handleUnavailability(error, address);
      }

      if (isFailureToWrite(error)) {
        return this._handleWriteFailure(error, address);
      }

      return error;
    }
  }]);
  return ConnectionErrorHandler;
}();

exports["default"] = ConnectionErrorHandler;

function isAvailabilityError(error) {
  if (error) {
    return error.code === _error.SESSION_EXPIRED || error.code === _error.SERVICE_UNAVAILABLE || error.code === 'Neo.TransientError.General.DatabaseUnavailable';
  }

  return false;
}

function isFailureToWrite(error) {
  if (error) {
    return error.code === 'Neo.ClientError.Cluster.NotALeader' || error.code === 'Neo.ClientError.General.ForbiddenOnReadOnlyDatabase';
  }

  return false;
}

function noOpHandler(error) {
  return error;
}

},{"../error":29,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11}],47:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.EMPTY_CONNECTION_HOLDER = exports["default"] = void 0;

var _possibleConstructorReturn2 = _interopRequireDefault(require("@babel/runtime/helpers/possibleConstructorReturn"));

var _getPrototypeOf2 = _interopRequireDefault(require("@babel/runtime/helpers/getPrototypeOf"));

var _inherits2 = _interopRequireDefault(require("@babel/runtime/helpers/inherits"));

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _error = require("../error");

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
 * Utility to lazily initialize connections and return them back to the pool when unused.
 */
var ConnectionHolder =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {string} mode - the access mode for new connection holder.
   * @param {ConnectionProvider} connectionProvider - the connection provider to acquire connections from.
   */
  function ConnectionHolder(mode, connectionProvider) {
    (0, _classCallCheck2["default"])(this, ConnectionHolder);
    this._mode = mode;
    this._connectionProvider = connectionProvider;
    this._referenceCount = 0;
    this._connectionPromise = Promise.resolve(null);
  }
  /**
   * Returns the assigned access mode.
   * @returns {string} access mode
   */


  (0, _createClass2["default"])(ConnectionHolder, [{
    key: "mode",
    value: function mode() {
      return this._mode;
    }
    /**
     * Make this holder initialize new connection if none exists already.
     * @return {undefined}
     */

  }, {
    key: "initializeConnection",
    value: function initializeConnection() {
      if (this._referenceCount === 0) {
        this._connectionPromise = this._connectionProvider.acquireConnection(this._mode);
      }

      this._referenceCount++;
    }
    /**
     * Get the current connection promise.
     * @param {StreamObserver} streamObserver an observer for this connection.
     * @return {Promise<Connection>} promise resolved with the current connection.
     */

  }, {
    key: "getConnection",
    value: function getConnection(streamObserver) {
      return this._connectionPromise.then(function (connection) {
        streamObserver.resolveConnection(connection);
        return connection;
      });
    }
    /**
     * Notify this holder that single party does not require current connection any more.
     * @return {Promise<Connection>} promise resolved with the current connection, never a rejected promise.
     */

  }, {
    key: "releaseConnection",
    value: function releaseConnection() {
      if (this._referenceCount === 0) {
        return this._connectionPromise;
      }

      this._referenceCount--;

      if (this._referenceCount === 0) {
        return this._releaseConnection();
      }

      return this._connectionPromise;
    }
    /**
     * Closes this holder and releases current connection (if any) despite any existing users.
     * @return {Promise<Connection>} promise resolved when current connection is released to the pool.
     */

  }, {
    key: "close",
    value: function close() {
      if (this._referenceCount === 0) {
        return this._connectionPromise;
      }

      this._referenceCount = 0;
      return this._releaseConnection();
    }
    /**
     * Return the current pooled connection instance to the connection pool.
     * We don't pool Session instances, to avoid users using the Session after they've called close.
     * The `Session` object is just a thin wrapper around Connection anyway, so it makes little difference.
     * @return {Promise} - promise resolved then connection is returned to the pool.
     * @private
     */

  }, {
    key: "_releaseConnection",
    value: function _releaseConnection() {
      this._connectionPromise = this._connectionPromise.then(function (connection) {
        if (connection) {
          return connection.resetAndFlush()["catch"](ignoreError).then(function () {
            return connection._release();
          });
        } else {
          return Promise.resolve();
        }
      })["catch"](ignoreError);
      return this._connectionPromise;
    }
  }]);
  return ConnectionHolder;
}();

exports["default"] = ConnectionHolder;

var EmptyConnectionHolder =
/*#__PURE__*/
function (_ConnectionHolder) {
  (0, _inherits2["default"])(EmptyConnectionHolder, _ConnectionHolder);

  function EmptyConnectionHolder() {
    (0, _classCallCheck2["default"])(this, EmptyConnectionHolder);
    return (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(EmptyConnectionHolder).apply(this, arguments));
  }

  (0, _createClass2["default"])(EmptyConnectionHolder, [{
    key: "initializeConnection",
    value: function initializeConnection() {// nothing to initialize
    }
  }, {
    key: "getConnection",
    value: function getConnection(streamObserver) {
      return Promise.reject((0, _error.newError)('This connection holder does not serve connections'));
    }
  }, {
    key: "releaseConnection",
    value: function releaseConnection() {
      return Promise.resolve();
    }
  }, {
    key: "close",
    value: function close() {
      return Promise.resolve();
    }
  }]);
  return EmptyConnectionHolder;
}(ConnectionHolder);

function ignoreError() {}
/**
 * Connection holder that does not manage any connections.
 * @type {ConnectionHolder}
 */


var EMPTY_CONNECTION_HOLDER = new EmptyConnectionHolder();
exports.EMPTY_CONNECTION_HOLDER = EMPTY_CONNECTION_HOLDER;

},{"../error":29,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/getPrototypeOf":9,"@babel/runtime/helpers/inherits":10,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/possibleConstructorReturn":18}],48:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.SingleConnectionProvider = exports.LoadBalancer = exports.DirectConnectionProvider = void 0;

var _possibleConstructorReturn2 = _interopRequireDefault(require("@babel/runtime/helpers/possibleConstructorReturn"));

var _getPrototypeOf2 = _interopRequireDefault(require("@babel/runtime/helpers/getPrototypeOf"));

var _inherits2 = _interopRequireDefault(require("@babel/runtime/helpers/inherits"));

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _error = require("../error");

var _driver = require("../driver");

var _session = _interopRequireDefault(require("../session"));

var _routingTable = _interopRequireDefault(require("./routing-table"));

var _rediscovery = _interopRequireDefault(require("./rediscovery"));

var _routingUtil = _interopRequireDefault(require("./routing-util"));

var _node = require('./browser');

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
var UNAUTHORIZED_ERROR_CODE = 'Neo.ClientError.Security.Unauthorized';

var ConnectionProvider =
/*#__PURE__*/
function () {
  function ConnectionProvider() {
    (0, _classCallCheck2["default"])(this, ConnectionProvider);
  }

  (0, _createClass2["default"])(ConnectionProvider, [{
    key: "acquireConnection",
    value: function acquireConnection(mode) {
      throw new Error('Abstract function');
    }
  }, {
    key: "_withAdditionalOnErrorCallback",
    value: function _withAdditionalOnErrorCallback(connectionPromise, driverOnErrorCallback) {
      // install error handler from the driver on the connection promise; this callback is installed separately
      // so that it does not handle errors, instead it is just an additional error reporting facility.
      connectionPromise["catch"](function (error) {
        driverOnErrorCallback(error);
      }); // return the original connection promise

      return connectionPromise;
    }
  }]);
  return ConnectionProvider;
}();

var DirectConnectionProvider =
/*#__PURE__*/
function (_ConnectionProvider) {
  (0, _inherits2["default"])(DirectConnectionProvider, _ConnectionProvider);

  function DirectConnectionProvider(address, connectionPool, driverOnErrorCallback) {
    var _this;

    (0, _classCallCheck2["default"])(this, DirectConnectionProvider);
    _this = (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(DirectConnectionProvider).call(this));
    _this._address = address;
    _this._connectionPool = connectionPool;
    _this._driverOnErrorCallback = driverOnErrorCallback;
    return _this;
  }

  (0, _createClass2["default"])(DirectConnectionProvider, [{
    key: "acquireConnection",
    value: function acquireConnection(mode) {
      var connectionPromise = this._connectionPool.acquire(this._address);

      return this._withAdditionalOnErrorCallback(connectionPromise, this._driverOnErrorCallback);
    }
  }]);
  return DirectConnectionProvider;
}(ConnectionProvider);

exports.DirectConnectionProvider = DirectConnectionProvider;

var LoadBalancer =
/*#__PURE__*/
function (_ConnectionProvider2) {
  (0, _inherits2["default"])(LoadBalancer, _ConnectionProvider2);

  function LoadBalancer(address, routingContext, connectionPool, loadBalancingStrategy, hostNameResolver, driverOnErrorCallback, log) {
    var _this2;

    (0, _classCallCheck2["default"])(this, LoadBalancer);
    _this2 = (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(LoadBalancer).call(this));
    _this2._seedRouter = address;
    _this2._routingTable = new _routingTable["default"]();
    _this2._rediscovery = new _rediscovery["default"](new _routingUtil["default"](routingContext));
    _this2._connectionPool = connectionPool;
    _this2._driverOnErrorCallback = driverOnErrorCallback;
    _this2._loadBalancingStrategy = loadBalancingStrategy;
    _this2._hostNameResolver = hostNameResolver;
    _this2._dnsResolver = new _node.HostNameResolver();
    _this2._log = log;
    _this2._useSeedRouter = true;
    return _this2;
  }

  (0, _createClass2["default"])(LoadBalancer, [{
    key: "acquireConnection",
    value: function acquireConnection(accessMode) {
      var _this3 = this;

      var connectionPromise = this._freshRoutingTable(accessMode).then(function (routingTable) {
        if (accessMode === _driver.READ) {
          var address = _this3._loadBalancingStrategy.selectReader(routingTable.readers);

          return _this3._acquireConnectionToServer(address, 'read');
        } else if (accessMode === _driver.WRITE) {
          var _address = _this3._loadBalancingStrategy.selectWriter(routingTable.writers);

          return _this3._acquireConnectionToServer(_address, 'write');
        } else {
          throw (0, _error.newError)('Illegal mode ' + accessMode);
        }
      });

      return this._withAdditionalOnErrorCallback(connectionPromise, this._driverOnErrorCallback);
    }
  }, {
    key: "forget",
    value: function forget(address) {
      this._routingTable.forget(address);

      this._connectionPool.purge(address);
    }
  }, {
    key: "forgetWriter",
    value: function forgetWriter(address) {
      this._routingTable.forgetWriter(address);
    }
  }, {
    key: "_acquireConnectionToServer",
    value: function _acquireConnectionToServer(address, serverName) {
      if (!address) {
        return Promise.reject((0, _error.newError)("Failed to obtain connection towards ".concat(serverName, " server. Known routing table is: ").concat(this._routingTable), _error.SESSION_EXPIRED));
      }

      return this._connectionPool.acquire(address);
    }
  }, {
    key: "_freshRoutingTable",
    value: function _freshRoutingTable(accessMode) {
      var currentRoutingTable = this._routingTable;

      if (!currentRoutingTable.isStaleFor(accessMode)) {
        return Promise.resolve(currentRoutingTable);
      }

      this._log.info("Routing table is stale for ".concat(accessMode, ": ").concat(currentRoutingTable));

      return this._refreshRoutingTable(currentRoutingTable);
    }
  }, {
    key: "_refreshRoutingTable",
    value: function _refreshRoutingTable(currentRoutingTable) {
      var knownRouters = currentRoutingTable.routers;

      if (this._useSeedRouter) {
        return this._fetchRoutingTableFromSeedRouterFallbackToKnownRouters(knownRouters, currentRoutingTable);
      }

      return this._fetchRoutingTableFromKnownRoutersFallbackToSeedRouter(knownRouters, currentRoutingTable);
    }
  }, {
    key: "_fetchRoutingTableFromSeedRouterFallbackToKnownRouters",
    value: function _fetchRoutingTableFromSeedRouterFallbackToKnownRouters(knownRouters, currentRoutingTable) {
      var _this4 = this;

      // we start with seed router, no routers were probed before
      var seenRouters = [];
      return this._fetchRoutingTableUsingSeedRouter(seenRouters, this._seedRouter).then(function (newRoutingTable) {
        if (newRoutingTable) {
          _this4._useSeedRouter = false;
          return newRoutingTable;
        } // seed router did not return a valid routing table - try to use other known routers


        return _this4._fetchRoutingTableUsingKnownRouters(knownRouters, currentRoutingTable);
      }).then(function (newRoutingTable) {
        _this4._applyRoutingTableIfPossible(newRoutingTable);

        return newRoutingTable;
      });
    }
  }, {
    key: "_fetchRoutingTableFromKnownRoutersFallbackToSeedRouter",
    value: function _fetchRoutingTableFromKnownRoutersFallbackToSeedRouter(knownRouters, currentRoutingTable) {
      var _this5 = this;

      return this._fetchRoutingTableUsingKnownRouters(knownRouters, currentRoutingTable).then(function (newRoutingTable) {
        if (newRoutingTable) {
          return newRoutingTable;
        } // none of the known routers returned a valid routing table - try to use seed router address for rediscovery


        return _this5._fetchRoutingTableUsingSeedRouter(knownRouters, _this5._seedRouter);
      }).then(function (newRoutingTable) {
        _this5._applyRoutingTableIfPossible(newRoutingTable);

        return newRoutingTable;
      });
    }
  }, {
    key: "_fetchRoutingTableUsingKnownRouters",
    value: function _fetchRoutingTableUsingKnownRouters(knownRouters, currentRoutingTable) {
      return this._fetchRoutingTable(knownRouters, currentRoutingTable).then(function (newRoutingTable) {
        if (newRoutingTable) {
          // one of the known routers returned a valid routing table - use it
          return newRoutingTable;
        } // returned routing table was undefined, this means a connection error happened and the last known
        // router did not return a valid routing table, so we need to forget it


        var lastRouterIndex = knownRouters.length - 1;

        LoadBalancer._forgetRouter(currentRoutingTable, knownRouters, lastRouterIndex);

        return null;
      });
    }
  }, {
    key: "_fetchRoutingTableUsingSeedRouter",
    value: function _fetchRoutingTableUsingSeedRouter(seenRouters, seedRouter) {
      var _this6 = this;

      var resolvedAddresses = this._resolveSeedRouter(seedRouter);

      return resolvedAddresses.then(function (resolvedRouterAddresses) {
        // filter out all addresses that we've already tried
        var newAddresses = resolvedRouterAddresses.filter(function (address) {
          return seenRouters.indexOf(address) < 0;
        });
        return _this6._fetchRoutingTable(newAddresses, null);
      });
    }
  }, {
    key: "_resolveSeedRouter",
    value: function _resolveSeedRouter(seedRouter) {
      var _this7 = this;

      var customResolution = this._hostNameResolver.resolve(seedRouter);

      var dnsResolutions = customResolution.then(function (resolvedAddresses) {
        return Promise.all(resolvedAddresses.map(function (address) {
          return _this7._dnsResolver.resolve(address);
        }));
      });
      return dnsResolutions.then(function (results) {
        return [].concat.apply([], results);
      });
    }
  }, {
    key: "_fetchRoutingTable",
    value: function _fetchRoutingTable(routerAddresses, routingTable) {
      var _this8 = this;

      return routerAddresses.reduce(function (refreshedTablePromise, currentRouter, currentIndex) {
        return refreshedTablePromise.then(function (newRoutingTable) {
          if (newRoutingTable) {
            // valid routing table was fetched - just return it, try next router otherwise
            return newRoutingTable;
          } else {
            // returned routing table was undefined, this means a connection error happened and we need to forget the
            // previous router and try the next one
            var previousRouterIndex = currentIndex - 1;

            LoadBalancer._forgetRouter(routingTable, routerAddresses, previousRouterIndex);
          } // try next router


          return _this8._createSessionForRediscovery(currentRouter).then(function (session) {
            if (session) {
              return _this8._rediscovery.lookupRoutingTableOnRouter(session, currentRouter)["catch"](function (error) {
                _this8._log.warn("unable to fetch routing table because of an error ".concat(error));

                return null;
              });
            } else {
              // unable to acquire connection and create session towards the current router
              // return null to signal that the next router should be tried
              return null;
            }
          });
        });
      }, Promise.resolve(null));
    }
  }, {
    key: "_createSessionForRediscovery",
    value: function _createSessionForRediscovery(routerAddress) {
      return this._connectionPool.acquire(routerAddress).then(function (connection) {
        var connectionProvider = new SingleConnectionProvider(connection);
        return new _session["default"](_driver.READ, connectionProvider);
      })["catch"](function (error) {
        // unable to acquire connection towards the given router
        if (error && error.code === UNAUTHORIZED_ERROR_CODE) {
          // auth error is a sign of a configuration issue, rediscovery should not proceed
          throw error;
        }

        return null;
      });
    }
  }, {
    key: "_applyRoutingTableIfPossible",
    value: function _applyRoutingTableIfPossible(newRoutingTable) {
      if (!newRoutingTable) {
        // none of routing servers returned valid routing table, throw exception
        throw (0, _error.newError)("Could not perform discovery. No routing servers available. Known routing table: ".concat(this._routingTable), _error.SERVICE_UNAVAILABLE);
      }

      if (newRoutingTable.writers.length === 0) {
        // use seed router next time. this is important when cluster is partitioned. it tries to make sure driver
        // does not always get routing table without writers because it talks exclusively to a minority partition
        this._useSeedRouter = true;
      }

      this._updateRoutingTable(newRoutingTable);
    }
  }, {
    key: "_updateRoutingTable",
    value: function _updateRoutingTable(newRoutingTable) {
      // close old connections to servers not present in the new routing table
      this._connectionPool.keepAll(newRoutingTable.allServers()); // make this driver instance aware of the new table


      this._routingTable = newRoutingTable;

      this._log.info("Updated routing table ".concat(newRoutingTable));
    }
  }], [{
    key: "_forgetRouter",
    value: function _forgetRouter(routingTable, routersArray, routerIndex) {
      var address = routersArray[routerIndex];

      if (routingTable && address) {
        routingTable.forgetRouter(address);
      }
    }
  }]);
  return LoadBalancer;
}(ConnectionProvider);

exports.LoadBalancer = LoadBalancer;

var SingleConnectionProvider =
/*#__PURE__*/
function (_ConnectionProvider3) {
  (0, _inherits2["default"])(SingleConnectionProvider, _ConnectionProvider3);

  function SingleConnectionProvider(connection) {
    var _this9;

    (0, _classCallCheck2["default"])(this, SingleConnectionProvider);
    _this9 = (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(SingleConnectionProvider).call(this));
    _this9._connection = connection;
    return _this9;
  }

  (0, _createClass2["default"])(SingleConnectionProvider, [{
    key: "acquireConnection",
    value: function acquireConnection(mode) {
      var connection = this._connection;
      this._connection = null;
      return Promise.resolve(connection);
    }
  }]);
  return SingleConnectionProvider;
}(ConnectionProvider);

exports.SingleConnectionProvider = SingleConnectionProvider;

},{"../driver":28,"../error":29,"../session":85,"./browser":41,"./rediscovery":65,"./routing-table":71,"./routing-util":72,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/getPrototypeOf":9,"@babel/runtime/helpers/inherits":10,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/possibleConstructorReturn":18}],49:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _node = require('./browser');

var _chunking = require("./chunking");

var _error = require("./../error");

var _channelConfig = _interopRequireDefault(require("./channel-config"));

var _serverVersion = require("./server-version");

var _protocolHandshaker = _interopRequireDefault(require("./protocol-handshaker"));

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
// Signature bytes for each response message type
var SUCCESS = 0x70; // 0111 0000 // SUCCESS <metadata>

var RECORD = 0x71; // 0111 0001 // RECORD <value>

var IGNORED = 0x7e; // 0111 1110 // IGNORED <metadata>

var FAILURE = 0x7f; // 0111 1111 // FAILURE <metadata>

function NO_OP() {}

var NO_OP_OBSERVER = {
  onNext: NO_OP,
  onCompleted: NO_OP,
  onError: NO_OP
};
var idGenerator = 0;

var Connection =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {Channel} channel - channel with a 'write' function and a 'onmessage' callback property.
   * @param {ConnectionErrorHandler} errorHandler the error handler.
   * @param {ServerAddress} address - the server address to connect to.
   * @param {Logger} log - the configured logger.
   * @param {boolean} disableLosslessIntegers if this connection should convert all received integers to native JS numbers.
   */
  function Connection(channel, errorHandler, address, log) {
    var disableLosslessIntegers = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : false;
    (0, _classCallCheck2["default"])(this, Connection);
    this.id = idGenerator++;
    this.address = address;
    this.server = {
      address: address.asHostPort()
    };
    this.creationTimestamp = Date.now();
    this._errorHandler = errorHandler;
    this._disableLosslessIntegers = disableLosslessIntegers;
    this._pendingObservers = [];
    this._currentObserver = undefined;
    this._ch = channel;
    this._dechunker = new _chunking.Dechunker();
    this._chunker = new _chunking.Chunker(channel);
    this._log = log; // connection from the database, returned in response for HELLO message and might not be available

    this._dbConnectionId = null; // bolt protocol is initially not initialized

    this._protocol = null; // error extracted from a FAILURE message

    this._currentFailure = null; // Set to true on fatal errors, to get this out of connection pool.

    this._isBroken = false;

    if (this._log.isDebugEnabled()) {
      this._log.debug("".concat(this, " created towards ").concat(address));
    }
  }
  /**
   * Crete new connection to the provided address. Returned connection is not connected.
   * @param {ServerAddress} address - the Bolt endpoint to connect to.
   * @param {object} config - this driver configuration.
   * @param {ConnectionErrorHandler} errorHandler - the error handler for connection errors.
   * @param {Logger} log - configured logger.
   * @return {Connection} - new connection.
   */


  (0, _createClass2["default"])(Connection, [{
    key: "connect",

    /**
     * Connect to the target address, negotiate Bolt protocol and send initialization message.
     * @param {string} userAgent the user agent for this driver.
     * @param {object} authToken the object containing auth information.
     * @return {Promise<Connection>} promise resolved with the current connection if connection is successful. Rejected promise otherwise.
     */
    value: function connect(userAgent, authToken) {
      var _this = this;

      return this._negotiateProtocol().then(function () {
        return _this._initialize(userAgent, authToken);
      });
    }
    /**
     * Execute Bolt protocol handshake to initialize the protocol version.
     * @return {Promise<Connection>} promise resolved with the current connection if handshake is successful. Rejected promise otherwise.
     */

  }, {
    key: "_negotiateProtocol",
    value: function _negotiateProtocol() {
      var _this2 = this;

      var protocolHandshaker = new _protocolHandshaker["default"](this, this._ch, this._chunker, this._disableLosslessIntegers, this._log);
      return new Promise(function (resolve, reject) {
        var handshakeErrorHandler = function handshakeErrorHandler(error) {
          _this2._handleFatalError(error);

          reject(error);
        };

        _this2._ch.onerror = handshakeErrorHandler.bind(_this2);

        if (_this2._ch._error) {
          // channel is already broken
          handshakeErrorHandler(_this2._ch._error);
        }

        _this2._ch.onmessage = function (buffer) {
          try {
            // read the response buffer and initialize the protocol
            _this2._protocol = protocolHandshaker.createNegotiatedProtocol(buffer); // reset the error handler to just handle errors and forget about the handshake promise

            _this2._ch.onerror = _this2._handleFatalError.bind(_this2); // Ok, protocol running. Simply forward all messages to the dechunker

            _this2._ch.onmessage = function (buf) {
              return _this2._dechunker.write(buf);
            }; // setup dechunker to dechunk messages and forward them to the message handler


            _this2._dechunker.onmessage = function (buf) {
              _this2._handleMessage(_this2._protocol.unpacker().unpack(buf));
            }; // forward all pending bytes to the dechunker


            if (buffer.hasRemaining()) {
              _this2._dechunker.write(buffer.readSlice(buffer.remaining()));
            }

            resolve(_this2);
          } catch (e) {
            _this2._handleFatalError(e);

            reject(e);
          }
        };

        protocolHandshaker.writeHandshakeRequest();
      });
    }
    /**
     * Perform protocol-specific initialization which includes authentication.
     * @param {string} userAgent the user agent for this driver.
     * @param {object} authToken the object containing auth information.
     * @return {Promise<Connection>} promise resolved with the current connection if initialization is successful. Rejected promise otherwise.
     */

  }, {
    key: "_initialize",
    value: function _initialize(userAgent, authToken) {
      var _this3 = this;

      return new Promise(function (resolve, reject) {
        var observer = new InitializationObserver(_this3, resolve, reject);

        _this3._protocol.initialize(userAgent, authToken, observer);
      });
    }
    /**
     * Get the Bolt protocol for the connection.
     * @return {BoltProtocol} the protocol.
     */

  }, {
    key: "protocol",
    value: function protocol() {
      return this._protocol;
    }
    /**
     * Write a message to the network channel.
     * @param {RequestMessage} message the message to write.
     * @param {StreamObserver} observer the response observer.
     * @param {boolean} flush `true` if flush should happen after the message is written to the buffer.
     */

  }, {
    key: "write",
    value: function write(message, observer, flush) {
      var _this4 = this;

      var queued = this._queueObserver(observer);

      if (queued) {
        if (this._log.isDebugEnabled()) {
          this._log.debug("".concat(this, " C: ").concat(message));
        }

        this._protocol.packer().packStruct(message.signature, message.fields.map(function (field) {
          return _this4._packable(field);
        }), function (err) {
          return _this4._handleFatalError(err);
        });

        this._chunker.messageBoundary();

        if (flush) {
          this._chunker.flush();
        }
      }
    }
    /**
     * "Fatal" means the connection is dead. Only call this if something
     * happens that cannot be recovered from. This will lead to all subscribers
     * failing, and the connection getting ejected from the session pool.
     *
     * @param error an error object, forwarded to all current and future subscribers
     */

  }, {
    key: "_handleFatalError",
    value: function _handleFatalError(error) {
      this._isBroken = true;
      this._error = this._errorHandler.handleAndTransformError(error, this.address);

      if (this._log.isErrorEnabled()) {
        this._log.error("".concat(this, " experienced a fatal error ").concat(JSON.stringify(this._error)));
      }

      if (this._currentObserver && this._currentObserver.onError) {
        this._currentObserver.onError(this._error);
      }

      while (this._pendingObservers.length > 0) {
        var observer = this._pendingObservers.shift();

        if (observer && observer.onError) {
          observer.onError(this._error);
        }
      }
    }
  }, {
    key: "_handleMessage",
    value: function _handleMessage(msg) {
      if (this._isBroken) {
        // ignore all incoming messages when this connection is broken. all previously pending observers failed
        // with the fatal error. all future observers will fail with same fatal error.
        return;
      }

      var payload = msg.fields[0];

      switch (msg.signature) {
        case RECORD:
          if (this._log.isDebugEnabled()) {
            this._log.debug("".concat(this, " S: RECORD ").concat(JSON.stringify(msg)));
          }

          this._currentObserver.onNext(payload);

          break;

        case SUCCESS:
          if (this._log.isDebugEnabled()) {
            this._log.debug("".concat(this, " S: SUCCESS ").concat(JSON.stringify(msg)));
          }

          try {
            var metadata = this._protocol.transformMetadata(payload);

            this._currentObserver.onCompleted(metadata);
          } finally {
            this._updateCurrentObserver();
          }

          break;

        case FAILURE:
          if (this._log.isDebugEnabled()) {
            this._log.debug("".concat(this, " S: FAILURE ").concat(JSON.stringify(msg)));
          }

          try {
            var error = (0, _error.newError)(payload.message, payload.code);
            this._currentFailure = this._errorHandler.handleAndTransformError(error, this.address);

            this._currentObserver.onError(this._currentFailure);
          } finally {
            this._updateCurrentObserver(); // Things are now broken. Pending observers will get FAILURE messages routed until we are done handling this failure.


            this._resetOnFailure();
          }

          break;

        case IGNORED:
          if (this._log.isDebugEnabled()) {
            this._log.debug("".concat(this, " S: IGNORED ").concat(JSON.stringify(msg)));
          }

          try {
            if (this._currentFailure && this._currentObserver.onError) {
              this._currentObserver.onError(this._currentFailure);
            } else if (this._currentObserver.onError) {
              this._currentObserver.onError((0, _error.newError)('Ignored either because of an error or RESET'));
            }
          } finally {
            this._updateCurrentObserver();
          }

          break;

        default:
          this._handleFatalError((0, _error.newError)('Unknown Bolt protocol message: ' + msg));

      }
    }
    /**
     * Send a RESET-message to the database. Message is immediately flushed to the network.
     * @return {Promise<void>} promise resolved when SUCCESS-message response arrives, or failed when other response messages arrives.
     */

  }, {
    key: "resetAndFlush",
    value: function resetAndFlush() {
      var _this5 = this;

      return new Promise(function (resolve, reject) {
        _this5._protocol.reset({
          onNext: function onNext(record) {
            var neo4jError = _this5._handleProtocolError('Received RECORD as a response for RESET: ' + JSON.stringify(record));

            reject(neo4jError);
          },
          onError: function onError(error) {
            if (_this5._isBroken) {
              // handling a fatal error, no need to raise a protocol violation
              reject(error);
            } else {
              var neo4jError = _this5._handleProtocolError('Received FAILURE as a response for RESET: ' + error);

              reject(neo4jError);
            }
          },
          onCompleted: function onCompleted() {
            resolve();
          }
        });
      });
    }
  }, {
    key: "_resetOnFailure",
    value: function _resetOnFailure() {
      var _this6 = this;

      this._protocol.reset({
        onNext: function onNext(record) {
          _this6._handleProtocolError('Received RECORD as a response for RESET: ' + JSON.stringify(record));
        },
        // clear the current failure when response for RESET is received
        onError: function onError() {
          _this6._currentFailure = null;
        },
        onCompleted: function onCompleted() {
          _this6._currentFailure = null;
        }
      });
    }
  }, {
    key: "_queueObserver",
    value: function _queueObserver(observer) {
      if (this._isBroken) {
        if (observer && observer.onError) {
          observer.onError(this._error);
        }

        return false;
      }

      observer = observer || NO_OP_OBSERVER;
      observer.onCompleted = observer.onCompleted || NO_OP;
      observer.onError = observer.onError || NO_OP;
      observer.onNext = observer.onNext || NO_OP;

      if (this._currentObserver === undefined) {
        this._currentObserver = observer;
      } else {
        this._pendingObservers.push(observer);
      }

      return true;
    }
    /*
     * Pop next pending observer form the list of observers and make it current observer.
     * @protected
     */

  }, {
    key: "_updateCurrentObserver",
    value: function _updateCurrentObserver() {
      this._currentObserver = this._pendingObservers.shift();
    }
    /** Check if this connection is in working condition */

  }, {
    key: "isOpen",
    value: function isOpen() {
      return !this._isBroken && this._ch._open;
    }
    /**
     * Call close on the channel.
     * @param {function} cb - Function to call on close.
     */

  }, {
    key: "close",
    value: function close() {
      var _this7 = this;

      var cb = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : function () {
        return null;
      };

      if (this._log.isDebugEnabled()) {
        this._log.debug("".concat(this, " closing"));
      }

      if (this._protocol && this.isOpen()) {
        // protocol has been initialized and this connection is healthy
        // notify the database about the upcoming close of the connection
        this._protocol.prepareToClose(NO_OP_OBSERVER);
      }

      this._ch.close(function () {
        if (_this7._log.isDebugEnabled()) {
          _this7._log.debug("".concat(_this7, " closed"));
        }

        cb();
      });
    }
  }, {
    key: "toString",
    value: function toString() {
      var dbConnectionId = this._dbConnectionId || '';
      return "Connection [".concat(this.id, "][").concat(dbConnectionId, "]");
    }
  }, {
    key: "_packable",
    value: function _packable(value) {
      var _this8 = this;

      return this._protocol.packer().packable(value, function (err) {
        return _this8._handleFatalError(err);
      });
    }
  }, {
    key: "_handleProtocolError",
    value: function _handleProtocolError(message) {
      this._currentFailure = null;

      this._updateCurrentObserver();

      var error = (0, _error.newError)(message, _error.PROTOCOL_ERROR);

      this._handleFatalError(error);

      return error;
    }
  }], [{
    key: "create",
    value: function create(address, config, errorHandler, log) {
      var channelConfig = new _channelConfig["default"](address, config, errorHandler.errorCode());
      return new Connection(new _node.Channel(channelConfig), errorHandler, address, log, config.disableLosslessIntegers);
    }
  }]);
  return Connection;
}();

exports["default"] = Connection;

var InitializationObserver =
/*#__PURE__*/
function () {
  function InitializationObserver(connection, onSuccess, onError) {
    (0, _classCallCheck2["default"])(this, InitializationObserver);
    this._connection = connection;
    this._onSuccess = onSuccess;
    this._onError = onError;
  }

  (0, _createClass2["default"])(InitializationObserver, [{
    key: "onNext",
    value: function onNext(record) {
      this.onError((0, _error.newError)('Received RECORD when initializing ' + JSON.stringify(record)));
    }
  }, {
    key: "onError",
    value: function onError(error) {
      this._connection._updateCurrentObserver(); // make sure this exact observer will not be called again


      this._connection._handleFatalError(error); // initialization errors are fatal


      this._onError(error);
    }
  }, {
    key: "onCompleted",
    value: function onCompleted(metadata) {
      if (metadata) {
        // read server version from the response metadata, if it is available
        var serverVersion = metadata.server;

        if (!this._connection.server.version) {
          this._connection.server.version = serverVersion;

          var version = _serverVersion.ServerVersion.fromString(serverVersion);

          if (version.compareTo(_serverVersion.VERSION_3_2_0) < 0) {
            this._connection.protocol().packer().disableByteArrays();
          }
        } // read database connection id from the response metadata, if it is available


        var dbConnectionId = metadata.connection_id;

        if (!this._connection._dbConnectionId) {
          this._connection._dbConnectionId = dbConnectionId;
        }
      }

      this._onSuccess(this._connection);
    }
  }]);
  return InitializationObserver;
}();

},{"./../error":29,"./browser":41,"./channel-config":44,"./chunking":45,"./protocol-handshaker":64,"./server-version":74,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11}],50:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _connectionHolder = _interopRequireDefault(require("./connection-holder"));

var _driver = require("../driver");

var _streamObserver = _interopRequireDefault(require("./stream-observer"));

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
 * Verifies connectivity using the given connection provider.
 */
var ConnectivityVerifier =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {ConnectionProvider} connectionProvider the provider to obtain connections from.
   * @param {function} successCallback a callback to invoke when verification succeeds.
   */
  function ConnectivityVerifier(connectionProvider, successCallback) {
    (0, _classCallCheck2["default"])(this, ConnectivityVerifier);
    this._connectionProvider = connectionProvider;
    this._successCallback = successCallback;
  }

  (0, _createClass2["default"])(ConnectivityVerifier, [{
    key: "verify",
    value: function verify() {
      var _this = this;

      acquireAndReleaseDummyConnection(this._connectionProvider).then(function (serverInfo) {
        if (_this._successCallback) {
          _this._successCallback(serverInfo);
        }
      })["catch"](function (ignoredError) {});
    }
  }]);
  return ConnectivityVerifier;
}();
/**
 * @private
 * @param {ConnectionProvider} connectionProvider the provider to obtain connections from.
 * @return {Promise<object>} promise resolved with server info or rejected with error.
 */


exports["default"] = ConnectivityVerifier;

function acquireAndReleaseDummyConnection(connectionProvider) {
  var connectionHolder = new _connectionHolder["default"](_driver.READ, connectionProvider);
  connectionHolder.initializeConnection();
  var dummyObserver = new _streamObserver["default"]();
  var connectionPromise = connectionHolder.getConnection(dummyObserver);
  return connectionPromise.then(function (connection) {
    // able to establish a connection
    return connectionHolder.close().then(function () {
      return connection.server;
    });
  })["catch"](function (error) {
    // failed to establish a connection
    return connectionHolder.close()["catch"](function (ignoredError) {// ignore connection release error
    }).then(function () {
      return Promise.reject(error);
    });
  });
}

},{"../driver":28,"./connection-holder":47,"./stream-observer":75,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11}],51:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.ACCESS_MODE_WRITE = exports.ACCESS_MODE_READ = void 0;

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
var ACCESS_MODE_READ = 'READ';
exports.ACCESS_MODE_READ = ACCESS_MODE_READ;
var ACCESS_MODE_WRITE = 'WRITE';
exports.ACCESS_MODE_WRITE = ACCESS_MODE_WRITE;

},{}],52:[function(require,module,exports){
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

var _driver = _interopRequireDefault(require("../../driver"));

var _httpSession = _interopRequireDefault(require("./http-session"));

var _httpSessionTracker = _interopRequireDefault(require("./http-session-tracker"));

var _serverAddress = _interopRequireDefault(require("../server-address"));

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
var HttpDriver =
/*#__PURE__*/
function (_Driver) {
  (0, _inherits2["default"])(HttpDriver, _Driver);

  function HttpDriver(url, userAgent, token, config) {
    var _this;

    (0, _classCallCheck2["default"])(this, HttpDriver);
    _this = (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(HttpDriver).call(this, _serverAddress["default"].fromUrl(url.hostAndPort), userAgent, token, config));
    _this._url = url;
    _this._sessionTracker = new _httpSessionTracker["default"]();
    return _this;
  }

  (0, _createClass2["default"])(HttpDriver, [{
    key: "session",
    value: function session() {
      return new _httpSession["default"](this._url, this._authToken, this._config, this._sessionTracker);
    }
  }, {
    key: "close",
    value: function close() {
      return this._sessionTracker.close();
    }
  }]);
  return HttpDriver;
}(_driver["default"]);

exports["default"] = HttpDriver;

},{"../../driver":28,"../server-address":73,"./http-session":56,"./http-session-tracker":55,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/getPrototypeOf":9,"@babel/runtime/helpers/inherits":10,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/possibleConstructorReturn":18}],53:[function(require,module,exports){
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

},{"../../error":29,"../stream-observer":75,"./http-response-converter":54,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11}],54:[function(require,module,exports){
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

},{"../../error":29,"../../graph-types":30,"../../integer":32,"../../spatial-types":86,"../../temporal-types":87,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/construct":5,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/toConsumableArray":22,"@babel/runtime/helpers/typeof":23}],55:[function(require,module,exports){
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

},{"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11}],56:[function(require,module,exports){
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

},{"../../driver":28,"../../error":29,"../../result":83,"../../session":85,"../connection-holder":47,"../util":80,"./http-request-runner":53,"@babel/runtime/helpers/assertThisInitialized":3,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/getPrototypeOf":9,"@babel/runtime/helpers/inherits":10,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/possibleConstructorReturn":18}],57:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = exports.LEAST_CONNECTED_STRATEGY_NAME = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _possibleConstructorReturn2 = _interopRequireDefault(require("@babel/runtime/helpers/possibleConstructorReturn"));

var _getPrototypeOf2 = _interopRequireDefault(require("@babel/runtime/helpers/getPrototypeOf"));

var _inherits2 = _interopRequireDefault(require("@babel/runtime/helpers/inherits"));

var _roundRobinArrayIndex = _interopRequireDefault(require("./round-robin-array-index"));

var _loadBalancingStrategy = _interopRequireDefault(require("./load-balancing-strategy"));

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
var LEAST_CONNECTED_STRATEGY_NAME = 'least_connected';
exports.LEAST_CONNECTED_STRATEGY_NAME = LEAST_CONNECTED_STRATEGY_NAME;

var LeastConnectedLoadBalancingStrategy =
/*#__PURE__*/
function (_LoadBalancingStrateg) {
  (0, _inherits2["default"])(LeastConnectedLoadBalancingStrategy, _LoadBalancingStrateg);

  /**
   * @constructor
   * @param {Pool} connectionPool the connection pool of this driver.
   */
  function LeastConnectedLoadBalancingStrategy(connectionPool) {
    var _this;

    (0, _classCallCheck2["default"])(this, LeastConnectedLoadBalancingStrategy);
    _this = (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(LeastConnectedLoadBalancingStrategy).call(this));
    _this._readersIndex = new _roundRobinArrayIndex["default"]();
    _this._writersIndex = new _roundRobinArrayIndex["default"]();
    _this._connectionPool = connectionPool;
    return _this;
  }
  /**
   * @inheritDoc
   */


  (0, _createClass2["default"])(LeastConnectedLoadBalancingStrategy, [{
    key: "selectReader",
    value: function selectReader(knownReaders) {
      return this._select(knownReaders, this._readersIndex);
    }
    /**
     * @inheritDoc
     */

  }, {
    key: "selectWriter",
    value: function selectWriter(knownWriters) {
      return this._select(knownWriters, this._writersIndex);
    }
  }, {
    key: "_select",
    value: function _select(addresses, roundRobinIndex) {
      var length = addresses.length;

      if (length === 0) {
        return null;
      } // choose start index for iteration in round-robin fashion


      var startIndex = roundRobinIndex.next(length);
      var index = startIndex;
      var leastConnectedAddress = null;
      var leastActiveConnections = Number.MAX_SAFE_INTEGER; // iterate over the array to find least connected address

      do {
        var address = addresses[index];

        var activeConnections = this._connectionPool.activeResourceCount(address);

        if (activeConnections < leastActiveConnections) {
          leastConnectedAddress = address;
          leastActiveConnections = activeConnections;
        } // loop over to the start of the array when end is reached


        if (index === length - 1) {
          index = 0;
        } else {
          index++;
        }
      } while (index !== startIndex);

      return leastConnectedAddress;
    }
  }]);
  return LeastConnectedLoadBalancingStrategy;
}(_loadBalancingStrategy["default"]);

exports["default"] = LeastConnectedLoadBalancingStrategy;

},{"./load-balancing-strategy":58,"./round-robin-array-index":69,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/getPrototypeOf":9,"@babel/runtime/helpers/inherits":10,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/possibleConstructorReturn":18}],58:[function(require,module,exports){
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

/**
 * A facility to select most appropriate reader or writer among the given addresses for request processing.
 */
var LoadBalancingStrategy =
/*#__PURE__*/
function () {
  function LoadBalancingStrategy() {
    (0, _classCallCheck2["default"])(this, LoadBalancingStrategy);
  }

  (0, _createClass2["default"])(LoadBalancingStrategy, [{
    key: "selectReader",

    /**
     * Select next most appropriate reader from the list of given readers.
     * @param {string[]} knownReaders an array of currently known readers to select from.
     * @return {string} most appropriate reader or `null` if given array is empty.
     */
    value: function selectReader(knownReaders) {
      throw new Error('Abstract function');
    }
    /**
     * Select next most appropriate writer from the list of given writers.
     * @param {string[]} knownWriters an array of currently known writers to select from.
     * @return {string} most appropriate writer or `null` if given array is empty.
     */

  }, {
    key: "selectWriter",
    value: function selectWriter(knownWriters) {
      throw new Error('Abstract function');
    }
  }]);
  return LoadBalancingStrategy;
}();

exports["default"] = LoadBalancingStrategy;

},{"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11}],59:[function(require,module,exports){
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

},{"../error":29,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/defineProperty":7,"@babel/runtime/helpers/getPrototypeOf":9,"@babel/runtime/helpers/inherits":10,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/possibleConstructorReturn":18}],60:[function(require,module,exports){
"use strict";

var _interopRequireWildcard = require("@babel/runtime/helpers/interopRequireWildcard");

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.Structure = exports.Unpacker = exports.Packer = void 0;

var _typeof2 = _interopRequireDefault(require("@babel/runtime/helpers/typeof"));

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _node = require('./browser');

var _integer = _interopRequireWildcard(require("../integer"));

var _error = require("./../error");

var _graphTypes = require("../graph-types");

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
var TINY_STRING = 0x80;
var TINY_LIST = 0x90;
var TINY_MAP = 0xa0;
var TINY_STRUCT = 0xb0;
var NULL = 0xc0;
var FLOAT_64 = 0xc1;
var FALSE = 0xc2;
var TRUE = 0xc3;
var INT_8 = 0xc8;
var INT_16 = 0xc9;
var INT_32 = 0xca;
var INT_64 = 0xcb;
var STRING_8 = 0xd0;
var STRING_16 = 0xd1;
var STRING_32 = 0xd2;
var LIST_8 = 0xd4;
var LIST_16 = 0xd5;
var LIST_32 = 0xd6;
var BYTES_8 = 0xcc;
var BYTES_16 = 0xcd;
var BYTES_32 = 0xce;
var MAP_8 = 0xd8;
var MAP_16 = 0xd9;
var MAP_32 = 0xda;
var STRUCT_8 = 0xdc;
var STRUCT_16 = 0xdd;
var NODE = 0x4e;
var NODE_STRUCT_SIZE = 3;
var RELATIONSHIP = 0x52;
var RELATIONSHIP_STRUCT_SIZE = 5;
var UNBOUND_RELATIONSHIP = 0x72;
var UNBOUND_RELATIONSHIP_STRUCT_SIZE = 3;
var PATH = 0x50;
var PATH_STRUCT_SIZE = 3;
/**
 * A Structure have a signature and fields.
 * @access private
 */

var Structure =
/*#__PURE__*/
function () {
  /**
   * Create new instance
   */
  function Structure(signature, fields) {
    (0, _classCallCheck2["default"])(this, Structure);
    this.signature = signature;
    this.fields = fields;
  }

  (0, _createClass2["default"])(Structure, [{
    key: "toString",
    value: function toString() {
      var fieldStr = '';

      for (var i = 0; i < this.fields.length; i++) {
        if (i > 0) {
          fieldStr += ', ';
        }

        fieldStr += this.fields[i];
      }

      return 'Structure(' + this.signature + ', [' + fieldStr + '])';
    }
  }]);
  return Structure;
}();
/**
 * Class to pack
 * @access private
 */


exports.Structure = Structure;

var Packer =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {Chunker} channel the chunker backed by a network channel.
   */
  function Packer(channel) {
    (0, _classCallCheck2["default"])(this, Packer);
    this._ch = channel;
    this._byteArraysSupported = true;
  }
  /**
   * Creates a packable function out of the provided value
   * @param x the value to pack
   * @param onError callback for the case when value cannot be packed
   * @returns Function
   */


  (0, _createClass2["default"])(Packer, [{
    key: "packable",
    value: function packable(x, onError) {
      var _this = this;

      if (x === null) {
        return function () {
          return _this._ch.writeUInt8(NULL);
        };
      } else if (x === true) {
        return function () {
          return _this._ch.writeUInt8(TRUE);
        };
      } else if (x === false) {
        return function () {
          return _this._ch.writeUInt8(FALSE);
        };
      } else if (typeof x === 'number') {
        return function () {
          return _this.packFloat(x);
        };
      } else if (typeof x === 'string') {
        return function () {
          return _this.packString(x, onError);
        };
      } else if ((0, _integer.isInt)(x)) {
        return function () {
          return _this.packInteger(x);
        };
      } else if (x instanceof Int8Array) {
        return function () {
          return _this.packBytes(x, onError);
        };
      } else if (x instanceof Array) {
        return function () {
          _this.packListHeader(x.length, onError);

          for (var _i = 0; _i < x.length; _i++) {
            _this.packable(x[_i] === undefined ? null : x[_i], onError)();
          }
        };
      } else if (isIterable(x)) {
        return this.packableIterable(x, onError);
      } else if (x instanceof _graphTypes.Node) {
        return this._nonPackableValue("It is not allowed to pass nodes in query parameters, given: ".concat(x), onError);
      } else if (x instanceof _graphTypes.Relationship) {
        return this._nonPackableValue("It is not allowed to pass relationships in query parameters, given: ".concat(x), onError);
      } else if (x instanceof _graphTypes.Path) {
        return this._nonPackableValue("It is not allowed to pass paths in query parameters, given: ".concat(x), onError);
      } else if (x instanceof Structure) {
        var packableFields = [];

        for (var i = 0; i < x.fields.length; i++) {
          packableFields[i] = this.packable(x.fields[i], onError);
        }

        return function () {
          return _this.packStruct(x.signature, packableFields);
        };
      } else if ((0, _typeof2["default"])(x) === 'object') {
        return function () {
          var keys = Object.keys(x);
          var count = 0;

          for (var _i2 = 0; _i2 < keys.length; _i2++) {
            if (x[keys[_i2]] !== undefined) {
              count++;
            }
          }

          _this.packMapHeader(count, onError);

          for (var _i3 = 0; _i3 < keys.length; _i3++) {
            var key = keys[_i3];

            if (x[key] !== undefined) {
              _this.packString(key);

              _this.packable(x[key], onError)();
            }
          }
        };
      } else {
        return this._nonPackableValue("Unable to pack the given value: ".concat(x), onError);
      }
    }
  }, {
    key: "packableIterable",
    value: function packableIterable(iterable, onError) {
      try {
        var array = Array.from(iterable);
        return this.packable(array, onError);
      } catch (e) {
        // handle errors from iterable to array conversion
        onError((0, _error.newError)("Cannot pack given iterable, ".concat(e.message, ": ").concat(iterable)));
      }
    }
    /**
     * Packs a struct
     * @param signature the signature of the struct
     * @param packableFields the fields of the struct, make sure you call `packable on all fields`
     */

  }, {
    key: "packStruct",
    value: function packStruct(signature, packableFields, onError) {
      packableFields = packableFields || [];
      this.packStructHeader(packableFields.length, signature, onError);

      for (var i = 0; i < packableFields.length; i++) {
        packableFields[i]();
      }
    }
  }, {
    key: "packInteger",
    value: function packInteger(x) {
      var high = x.high;
      var low = x.low;

      if (x.greaterThanOrEqual(-0x10) && x.lessThan(0x80)) {
        this._ch.writeInt8(low);
      } else if (x.greaterThanOrEqual(-0x80) && x.lessThan(-0x10)) {
        this._ch.writeUInt8(INT_8);

        this._ch.writeInt8(low);
      } else if (x.greaterThanOrEqual(-0x8000) && x.lessThan(0x8000)) {
        this._ch.writeUInt8(INT_16);

        this._ch.writeInt16(low);
      } else if (x.greaterThanOrEqual(-0x80000000) && x.lessThan(0x80000000)) {
        this._ch.writeUInt8(INT_32);

        this._ch.writeInt32(low);
      } else {
        this._ch.writeUInt8(INT_64);

        this._ch.writeInt32(high);

        this._ch.writeInt32(low);
      }
    }
  }, {
    key: "packFloat",
    value: function packFloat(x) {
      this._ch.writeUInt8(FLOAT_64);

      this._ch.writeFloat64(x);
    }
  }, {
    key: "packString",
    value: function packString(x, onError) {
      var bytes = _node.utf8.encode(x);

      var size = bytes.length;

      if (size < 0x10) {
        this._ch.writeUInt8(TINY_STRING | size);

        this._ch.writeBytes(bytes);
      } else if (size < 0x100) {
        this._ch.writeUInt8(STRING_8);

        this._ch.writeUInt8(size);

        this._ch.writeBytes(bytes);
      } else if (size < 0x10000) {
        this._ch.writeUInt8(STRING_16);

        this._ch.writeUInt8(size / 256 >> 0);

        this._ch.writeUInt8(size % 256);

        this._ch.writeBytes(bytes);
      } else if (size < 0x100000000) {
        this._ch.writeUInt8(STRING_32);

        this._ch.writeUInt8((size / 16777216 >> 0) % 256);

        this._ch.writeUInt8((size / 65536 >> 0) % 256);

        this._ch.writeUInt8((size / 256 >> 0) % 256);

        this._ch.writeUInt8(size % 256);

        this._ch.writeBytes(bytes);
      } else {
        onError((0, _error.newError)('UTF-8 strings of size ' + size + ' are not supported'));
      }
    }
  }, {
    key: "packListHeader",
    value: function packListHeader(size, onError) {
      if (size < 0x10) {
        this._ch.writeUInt8(TINY_LIST | size);
      } else if (size < 0x100) {
        this._ch.writeUInt8(LIST_8);

        this._ch.writeUInt8(size);
      } else if (size < 0x10000) {
        this._ch.writeUInt8(LIST_16);

        this._ch.writeUInt8((size / 256 >> 0) % 256);

        this._ch.writeUInt8(size % 256);
      } else if (size < 0x100000000) {
        this._ch.writeUInt8(LIST_32);

        this._ch.writeUInt8((size / 16777216 >> 0) % 256);

        this._ch.writeUInt8((size / 65536 >> 0) % 256);

        this._ch.writeUInt8((size / 256 >> 0) % 256);

        this._ch.writeUInt8(size % 256);
      } else {
        onError((0, _error.newError)('Lists of size ' + size + ' are not supported'));
      }
    }
  }, {
    key: "packBytes",
    value: function packBytes(array, onError) {
      if (this._byteArraysSupported) {
        this.packBytesHeader(array.length, onError);

        for (var i = 0; i < array.length; i++) {
          this._ch.writeInt8(array[i]);
        }
      } else {
        onError((0, _error.newError)('Byte arrays are not supported by the database this driver is connected to'));
      }
    }
  }, {
    key: "packBytesHeader",
    value: function packBytesHeader(size, onError) {
      if (size < 0x100) {
        this._ch.writeUInt8(BYTES_8);

        this._ch.writeUInt8(size);
      } else if (size < 0x10000) {
        this._ch.writeUInt8(BYTES_16);

        this._ch.writeUInt8((size / 256 >> 0) % 256);

        this._ch.writeUInt8(size % 256);
      } else if (size < 0x100000000) {
        this._ch.writeUInt8(BYTES_32);

        this._ch.writeUInt8((size / 16777216 >> 0) % 256);

        this._ch.writeUInt8((size / 65536 >> 0) % 256);

        this._ch.writeUInt8((size / 256 >> 0) % 256);

        this._ch.writeUInt8(size % 256);
      } else {
        onError((0, _error.newError)('Byte arrays of size ' + size + ' are not supported'));
      }
    }
  }, {
    key: "packMapHeader",
    value: function packMapHeader(size, onError) {
      if (size < 0x10) {
        this._ch.writeUInt8(TINY_MAP | size);
      } else if (size < 0x100) {
        this._ch.writeUInt8(MAP_8);

        this._ch.writeUInt8(size);
      } else if (size < 0x10000) {
        this._ch.writeUInt8(MAP_16);

        this._ch.writeUInt8(size / 256 >> 0);

        this._ch.writeUInt8(size % 256);
      } else if (size < 0x100000000) {
        this._ch.writeUInt8(MAP_32);

        this._ch.writeUInt8((size / 16777216 >> 0) % 256);

        this._ch.writeUInt8((size / 65536 >> 0) % 256);

        this._ch.writeUInt8((size / 256 >> 0) % 256);

        this._ch.writeUInt8(size % 256);
      } else {
        onError((0, _error.newError)('Maps of size ' + size + ' are not supported'));
      }
    }
  }, {
    key: "packStructHeader",
    value: function packStructHeader(size, signature, onError) {
      if (size < 0x10) {
        this._ch.writeUInt8(TINY_STRUCT | size);

        this._ch.writeUInt8(signature);
      } else if (size < 0x100) {
        this._ch.writeUInt8(STRUCT_8);

        this._ch.writeUInt8(size);

        this._ch.writeUInt8(signature);
      } else if (size < 0x10000) {
        this._ch.writeUInt8(STRUCT_16);

        this._ch.writeUInt8(size / 256 >> 0);

        this._ch.writeUInt8(size % 256);
      } else {
        onError((0, _error.newError)('Structures of size ' + size + ' are not supported'));
      }
    }
  }, {
    key: "disableByteArrays",
    value: function disableByteArrays() {
      this._byteArraysSupported = false;
    }
  }, {
    key: "_nonPackableValue",
    value: function _nonPackableValue(message, onError) {
      if (onError) {
        onError((0, _error.newError)(message, _error.PROTOCOL_ERROR));
      }

      return function () {
        return undefined;
      };
    }
  }]);
  return Packer;
}();
/**
 * Class to unpack
 * @access private
 */


exports.Packer = Packer;

var Unpacker =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {boolean} disableLosslessIntegers if this unpacker should convert all received integers to native JS numbers.
   */
  function Unpacker() {
    var disableLosslessIntegers = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
    (0, _classCallCheck2["default"])(this, Unpacker);
    this._disableLosslessIntegers = disableLosslessIntegers;
  }

  (0, _createClass2["default"])(Unpacker, [{
    key: "unpack",
    value: function unpack(buffer) {
      var marker = buffer.readUInt8();
      var markerHigh = marker & 0xf0;
      var markerLow = marker & 0x0f;

      if (marker === NULL) {
        return null;
      }

      var _boolean = this._unpackBoolean(marker);

      if (_boolean !== null) {
        return _boolean;
      }

      var numberOrInteger = this._unpackNumberOrInteger(marker, buffer);

      if (numberOrInteger !== null) {
        if (this._disableLosslessIntegers && (0, _integer.isInt)(numberOrInteger)) {
          return numberOrInteger.toNumberOrInfinity();
        }

        return numberOrInteger;
      }

      var string = this._unpackString(marker, markerHigh, markerLow, buffer);

      if (string !== null) {
        return string;
      }

      var list = this._unpackList(marker, markerHigh, markerLow, buffer);

      if (list !== null) {
        return list;
      }

      var byteArray = this._unpackByteArray(marker, buffer);

      if (byteArray !== null) {
        return byteArray;
      }

      var map = this._unpackMap(marker, markerHigh, markerLow, buffer);

      if (map !== null) {
        return map;
      }

      var struct = this._unpackStruct(marker, markerHigh, markerLow, buffer);

      if (struct !== null) {
        return struct;
      }

      throw (0, _error.newError)('Unknown packed value with marker ' + marker.toString(16));
    }
  }, {
    key: "unpackInteger",
    value: function unpackInteger(buffer) {
      var marker = buffer.readUInt8();

      var result = this._unpackInteger(marker, buffer);

      if (result == null) {
        throw (0, _error.newError)('Unable to unpack integer value with marker ' + marker.toString(16));
      }

      return result;
    }
  }, {
    key: "_unpackBoolean",
    value: function _unpackBoolean(marker) {
      if (marker === TRUE) {
        return true;
      } else if (marker === FALSE) {
        return false;
      } else {
        return null;
      }
    }
  }, {
    key: "_unpackNumberOrInteger",
    value: function _unpackNumberOrInteger(marker, buffer) {
      if (marker === FLOAT_64) {
        return buffer.readFloat64();
      } else {
        return this._unpackInteger(marker, buffer);
      }
    }
  }, {
    key: "_unpackInteger",
    value: function _unpackInteger(marker, buffer) {
      if (marker >= 0 && marker < 128) {
        return (0, _integer["int"])(marker);
      } else if (marker >= 240 && marker < 256) {
        return (0, _integer["int"])(marker - 256);
      } else if (marker === INT_8) {
        return (0, _integer["int"])(buffer.readInt8());
      } else if (marker === INT_16) {
        return (0, _integer["int"])(buffer.readInt16());
      } else if (marker === INT_32) {
        var b = buffer.readInt32();
        return (0, _integer["int"])(b);
      } else if (marker === INT_64) {
        var high = buffer.readInt32();
        var low = buffer.readInt32();
        return new _integer["default"](low, high);
      } else {
        return null;
      }
    }
  }, {
    key: "_unpackString",
    value: function _unpackString(marker, markerHigh, markerLow, buffer) {
      if (markerHigh === TINY_STRING) {
        return _node.utf8.decode(buffer, markerLow);
      } else if (marker === STRING_8) {
        return _node.utf8.decode(buffer, buffer.readUInt8());
      } else if (marker === STRING_16) {
        return _node.utf8.decode(buffer, buffer.readUInt16());
      } else if (marker === STRING_32) {
        return _node.utf8.decode(buffer, buffer.readUInt32());
      } else {
        return null;
      }
    }
  }, {
    key: "_unpackList",
    value: function _unpackList(marker, markerHigh, markerLow, buffer) {
      if (markerHigh === TINY_LIST) {
        return this._unpackListWithSize(markerLow, buffer);
      } else if (marker === LIST_8) {
        return this._unpackListWithSize(buffer.readUInt8(), buffer);
      } else if (marker === LIST_16) {
        return this._unpackListWithSize(buffer.readUInt16(), buffer);
      } else if (marker === LIST_32) {
        return this._unpackListWithSize(buffer.readUInt32(), buffer);
      } else {
        return null;
      }
    }
  }, {
    key: "_unpackListWithSize",
    value: function _unpackListWithSize(size, buffer) {
      var value = [];

      for (var i = 0; i < size; i++) {
        value.push(this.unpack(buffer));
      }

      return value;
    }
  }, {
    key: "_unpackByteArray",
    value: function _unpackByteArray(marker, buffer) {
      if (marker === BYTES_8) {
        return this._unpackByteArrayWithSize(buffer.readUInt8(), buffer);
      } else if (marker === BYTES_16) {
        return this._unpackByteArrayWithSize(buffer.readUInt16(), buffer);
      } else if (marker === BYTES_32) {
        return this._unpackByteArrayWithSize(buffer.readUInt32(), buffer);
      } else {
        return null;
      }
    }
  }, {
    key: "_unpackByteArrayWithSize",
    value: function _unpackByteArrayWithSize(size, buffer) {
      var value = new Int8Array(size);

      for (var i = 0; i < size; i++) {
        value[i] = buffer.readInt8();
      }

      return value;
    }
  }, {
    key: "_unpackMap",
    value: function _unpackMap(marker, markerHigh, markerLow, buffer) {
      if (markerHigh === TINY_MAP) {
        return this._unpackMapWithSize(markerLow, buffer);
      } else if (marker === MAP_8) {
        return this._unpackMapWithSize(buffer.readUInt8(), buffer);
      } else if (marker === MAP_16) {
        return this._unpackMapWithSize(buffer.readUInt16(), buffer);
      } else if (marker === MAP_32) {
        return this._unpackMapWithSize(buffer.readUInt32(), buffer);
      } else {
        return null;
      }
    }
  }, {
    key: "_unpackMapWithSize",
    value: function _unpackMapWithSize(size, buffer) {
      var value = {};

      for (var i = 0; i < size; i++) {
        var key = this.unpack(buffer);
        value[key] = this.unpack(buffer);
      }

      return value;
    }
  }, {
    key: "_unpackStruct",
    value: function _unpackStruct(marker, markerHigh, markerLow, buffer) {
      if (markerHigh === TINY_STRUCT) {
        return this._unpackStructWithSize(markerLow, buffer);
      } else if (marker === STRUCT_8) {
        return this._unpackStructWithSize(buffer.readUInt8(), buffer);
      } else if (marker === STRUCT_16) {
        return this._unpackStructWithSize(buffer.readUInt16(), buffer);
      } else {
        return null;
      }
    }
  }, {
    key: "_unpackStructWithSize",
    value: function _unpackStructWithSize(structSize, buffer) {
      var signature = buffer.readUInt8();

      if (signature === NODE) {
        return this._unpackNode(structSize, buffer);
      } else if (signature === RELATIONSHIP) {
        return this._unpackRelationship(structSize, buffer);
      } else if (signature === UNBOUND_RELATIONSHIP) {
        return this._unpackUnboundRelationship(structSize, buffer);
      } else if (signature === PATH) {
        return this._unpackPath(structSize, buffer);
      } else {
        return this._unpackUnknownStruct(signature, structSize, buffer);
      }
    }
  }, {
    key: "_unpackNode",
    value: function _unpackNode(structSize, buffer) {
      this._verifyStructSize('Node', NODE_STRUCT_SIZE, structSize);

      return new _graphTypes.Node(this.unpack(buffer), // Identity
      this.unpack(buffer), // Labels
      this.unpack(buffer) // Properties
      );
    }
  }, {
    key: "_unpackRelationship",
    value: function _unpackRelationship(structSize, buffer) {
      this._verifyStructSize('Relationship', RELATIONSHIP_STRUCT_SIZE, structSize);

      return new _graphTypes.Relationship(this.unpack(buffer), // Identity
      this.unpack(buffer), // Start Node Identity
      this.unpack(buffer), // End Node Identity
      this.unpack(buffer), // Type
      this.unpack(buffer) // Properties
      );
    }
  }, {
    key: "_unpackUnboundRelationship",
    value: function _unpackUnboundRelationship(structSize, buffer) {
      this._verifyStructSize('UnboundRelationship', UNBOUND_RELATIONSHIP_STRUCT_SIZE, structSize);

      return new _graphTypes.UnboundRelationship(this.unpack(buffer), // Identity
      this.unpack(buffer), // Type
      this.unpack(buffer) // Properties
      );
    }
  }, {
    key: "_unpackPath",
    value: function _unpackPath(structSize, buffer) {
      this._verifyStructSize('Path', PATH_STRUCT_SIZE, structSize);

      var nodes = this.unpack(buffer);
      var rels = this.unpack(buffer);
      var sequence = this.unpack(buffer);
      var segments = [];
      var prevNode = nodes[0];

      for (var i = 0; i < sequence.length; i += 2) {
        var nextNode = nodes[sequence[i + 1]];
        var relIndex = sequence[i];
        var rel = void 0;

        if (relIndex > 0) {
          rel = rels[relIndex - 1];

          if (rel instanceof _graphTypes.UnboundRelationship) {
            // To avoid duplication, relationships in a path do not contain
            // information about their start and end nodes, that's instead
            // inferred from the path sequence. This is us inferring (and,
            // for performance reasons remembering) the start/end of a rel.
            rels[relIndex - 1] = rel = rel.bind(prevNode.identity, nextNode.identity);
          }
        } else {
          rel = rels[-relIndex - 1];

          if (rel instanceof _graphTypes.UnboundRelationship) {
            // See above
            rels[-relIndex - 1] = rel = rel.bind(nextNode.identity, prevNode.identity);
          }
        } // Done hydrating one path segment.


        segments.push(new _graphTypes.PathSegment(prevNode, rel, nextNode));
        prevNode = nextNode;
      }

      return new _graphTypes.Path(nodes[0], nodes[nodes.length - 1], segments);
    }
  }, {
    key: "_unpackUnknownStruct",
    value: function _unpackUnknownStruct(signature, structSize, buffer) {
      var result = new Structure(signature, []);

      for (var i = 0; i < structSize; i++) {
        result.fields.push(this.unpack(buffer));
      }

      return result;
    }
  }, {
    key: "_verifyStructSize",
    value: function _verifyStructSize(structName, expectedSize, actualSize) {
      if (expectedSize !== actualSize) {
        throw (0, _error.newError)("Wrong struct size for ".concat(structName, ", expected ").concat(expectedSize, " but was ").concat(actualSize), _error.PROTOCOL_ERROR);
      }
    }
  }]);
  return Unpacker;
}();

exports.Unpacker = Unpacker;

function isIterable(obj) {
  if (obj == null) {
    return false;
  }

  return typeof obj[Symbol.iterator] === 'function';
}

},{"../graph-types":30,"../integer":32,"./../error":29,"./browser":41,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/interopRequireWildcard":12,"@babel/runtime/helpers/typeof":23}],61:[function(require,module,exports){
"use strict";

var _interopRequireWildcard = require("@babel/runtime/helpers/interopRequireWildcard");

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.Unpacker = exports.Packer = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _possibleConstructorReturn2 = _interopRequireDefault(require("@babel/runtime/helpers/possibleConstructorReturn"));

var _getPrototypeOf2 = _interopRequireDefault(require("@babel/runtime/helpers/getPrototypeOf"));

var _get2 = _interopRequireDefault(require("@babel/runtime/helpers/get"));

var _inherits2 = _interopRequireDefault(require("@babel/runtime/helpers/inherits"));

var v1 = _interopRequireWildcard(require("./packstream-v1"));

var _spatialTypes = require("../spatial-types");

var _temporalTypes = require("../temporal-types");

var _integer = require("../integer");

var _temporalUtil = require("../internal/temporal-util");

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
var POINT_2D = 0x58;
var POINT_2D_STRUCT_SIZE = 3;
var POINT_3D = 0x59;
var POINT_3D_STRUCT_SIZE = 4;
var DURATION = 0x45;
var DURATION_STRUCT_SIZE = 4;
var LOCAL_TIME = 0x74;
var LOCAL_TIME_STRUCT_SIZE = 1;
var TIME = 0x54;
var TIME_STRUCT_SIZE = 2;
var DATE = 0x44;
var DATE_STRUCT_SIZE = 1;
var LOCAL_DATE_TIME = 0x64;
var LOCAL_DATE_TIME_STRUCT_SIZE = 2;
var DATE_TIME_WITH_ZONE_OFFSET = 0x46;
var DATE_TIME_WITH_ZONE_OFFSET_STRUCT_SIZE = 3;
var DATE_TIME_WITH_ZONE_ID = 0x66;
var DATE_TIME_WITH_ZONE_ID_STRUCT_SIZE = 3;

var Packer =
/*#__PURE__*/
function (_v1$Packer) {
  (0, _inherits2["default"])(Packer, _v1$Packer);

  function Packer() {
    (0, _classCallCheck2["default"])(this, Packer);
    return (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(Packer).apply(this, arguments));
  }

  (0, _createClass2["default"])(Packer, [{
    key: "disableByteArrays",
    value: function disableByteArrays() {
      throw new Error('Bolt V2 should always support byte arrays');
    }
  }, {
    key: "packable",
    value: function packable(obj, onError) {
      var _this = this;

      if ((0, _spatialTypes.isPoint)(obj)) {
        return function () {
          return packPoint(obj, _this, onError);
        };
      } else if ((0, _temporalTypes.isDuration)(obj)) {
        return function () {
          return packDuration(obj, _this, onError);
        };
      } else if ((0, _temporalTypes.isLocalTime)(obj)) {
        return function () {
          return packLocalTime(obj, _this, onError);
        };
      } else if ((0, _temporalTypes.isTime)(obj)) {
        return function () {
          return packTime(obj, _this, onError);
        };
      } else if ((0, _temporalTypes.isDate)(obj)) {
        return function () {
          return packDate(obj, _this, onError);
        };
      } else if ((0, _temporalTypes.isLocalDateTime)(obj)) {
        return function () {
          return packLocalDateTime(obj, _this, onError);
        };
      } else if ((0, _temporalTypes.isDateTime)(obj)) {
        return function () {
          return packDateTime(obj, _this, onError);
        };
      } else {
        return (0, _get2["default"])((0, _getPrototypeOf2["default"])(Packer.prototype), "packable", this).call(this, obj, onError);
      }
    }
  }]);
  return Packer;
}(v1.Packer);

exports.Packer = Packer;

var Unpacker =
/*#__PURE__*/
function (_v1$Unpacker) {
  (0, _inherits2["default"])(Unpacker, _v1$Unpacker);

  /**
   * @constructor
   * @param {boolean} disableLosslessIntegers if this unpacker should convert all received integers to native JS numbers.
   */
  function Unpacker() {
    var disableLosslessIntegers = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
    (0, _classCallCheck2["default"])(this, Unpacker);
    return (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(Unpacker).call(this, disableLosslessIntegers));
  }

  (0, _createClass2["default"])(Unpacker, [{
    key: "_unpackUnknownStruct",
    value: function _unpackUnknownStruct(signature, structSize, buffer) {
      if (signature === POINT_2D) {
        return unpackPoint2D(this, structSize, buffer);
      } else if (signature === POINT_3D) {
        return unpackPoint3D(this, structSize, buffer);
      } else if (signature === DURATION) {
        return unpackDuration(this, structSize, buffer);
      } else if (signature === LOCAL_TIME) {
        return unpackLocalTime(this, structSize, buffer, this._disableLosslessIntegers);
      } else if (signature === TIME) {
        return unpackTime(this, structSize, buffer, this._disableLosslessIntegers);
      } else if (signature === DATE) {
        return unpackDate(this, structSize, buffer, this._disableLosslessIntegers);
      } else if (signature === LOCAL_DATE_TIME) {
        return unpackLocalDateTime(this, structSize, buffer, this._disableLosslessIntegers);
      } else if (signature === DATE_TIME_WITH_ZONE_OFFSET) {
        return unpackDateTimeWithZoneOffset(this, structSize, buffer, this._disableLosslessIntegers);
      } else if (signature === DATE_TIME_WITH_ZONE_ID) {
        return unpackDateTimeWithZoneId(this, structSize, buffer, this._disableLosslessIntegers);
      } else {
        return (0, _get2["default"])((0, _getPrototypeOf2["default"])(Unpacker.prototype), "_unpackUnknownStruct", this).call(this, signature, structSize, buffer, this._disableLosslessIntegers);
      }
    }
  }]);
  return Unpacker;
}(v1.Unpacker);
/**
 * Pack given 2D or 3D point.
 * @param {Point} point the point value to pack.
 * @param {Packer} packer the packer to use.
 * @param {function} onError the error callback.
 */


exports.Unpacker = Unpacker;

function packPoint(point, packer, onError) {
  var is2DPoint = point.z === null || point.z === undefined;

  if (is2DPoint) {
    packPoint2D(point, packer, onError);
  } else {
    packPoint3D(point, packer, onError);
  }
}
/**
 * Pack given 2D point.
 * @param {Point} point the point value to pack.
 * @param {Packer} packer the packer to use.
 * @param {function} onError the error callback.
 */


function packPoint2D(point, packer, onError) {
  var packableStructFields = [packer.packable((0, _integer["int"])(point.srid), onError), packer.packable(point.x, onError), packer.packable(point.y, onError)];
  packer.packStruct(POINT_2D, packableStructFields, onError);
}
/**
 * Pack given 3D point.
 * @param {Point} point the point value to pack.
 * @param {Packer} packer the packer to use.
 * @param {function} onError the error callback.
 */


function packPoint3D(point, packer, onError) {
  var packableStructFields = [packer.packable((0, _integer["int"])(point.srid), onError), packer.packable(point.x, onError), packer.packable(point.y, onError), packer.packable(point.z, onError)];
  packer.packStruct(POINT_3D, packableStructFields, onError);
}
/**
 * Unpack 2D point value using the given unpacker.
 * @param {Unpacker} unpacker the unpacker to use.
 * @param {number} structSize the retrieved struct size.
 * @param {BaseBuffer} buffer the buffer to unpack from.
 * @return {Point} the unpacked 2D point value.
 */


function unpackPoint2D(unpacker, structSize, buffer) {
  unpacker._verifyStructSize('Point2D', POINT_2D_STRUCT_SIZE, structSize);

  return new _spatialTypes.Point(unpacker.unpack(buffer), // srid
  unpacker.unpack(buffer), // x
  unpacker.unpack(buffer), // y
  undefined // z
  );
}
/**
 * Unpack 3D point value using the given unpacker.
 * @param {Unpacker} unpacker the unpacker to use.
 * @param {number} structSize the retrieved struct size.
 * @param {BaseBuffer} buffer the buffer to unpack from.
 * @return {Point} the unpacked 3D point value.
 */


function unpackPoint3D(unpacker, structSize, buffer) {
  unpacker._verifyStructSize('Point3D', POINT_3D_STRUCT_SIZE, structSize);

  return new _spatialTypes.Point(unpacker.unpack(buffer), // srid
  unpacker.unpack(buffer), // x
  unpacker.unpack(buffer), // y
  unpacker.unpack(buffer) // z
  );
}
/**
 * Pack given duration.
 * @param {Duration} value the duration value to pack.
 * @param {Packer} packer the packer to use.
 * @param {function} onError the error callback.
 */


function packDuration(value, packer, onError) {
  var months = (0, _integer["int"])(value.months);
  var days = (0, _integer["int"])(value.days);
  var seconds = (0, _integer["int"])(value.seconds);
  var nanoseconds = (0, _integer["int"])(value.nanoseconds);
  var packableStructFields = [packer.packable(months, onError), packer.packable(days, onError), packer.packable(seconds, onError), packer.packable(nanoseconds, onError)];
  packer.packStruct(DURATION, packableStructFields, onError);
}
/**
 * Unpack duration value using the given unpacker.
 * @param {Unpacker} unpacker the unpacker to use.
 * @param {number} structSize the retrieved struct size.
 * @param {BaseBuffer} buffer the buffer to unpack from.
 * @return {Duration} the unpacked duration value.
 */


function unpackDuration(unpacker, structSize, buffer) {
  unpacker._verifyStructSize('Duration', DURATION_STRUCT_SIZE, structSize);

  var months = unpacker.unpack(buffer);
  var days = unpacker.unpack(buffer);
  var seconds = unpacker.unpack(buffer);
  var nanoseconds = unpacker.unpack(buffer);
  return new _temporalTypes.Duration(months, days, seconds, nanoseconds);
}
/**
 * Pack given local time.
 * @param {LocalTime} value the local time value to pack.
 * @param {Packer} packer the packer to use.
 * @param {function} onError the error callback.
 */


function packLocalTime(value, packer, onError) {
  var nanoOfDay = (0, _temporalUtil.localTimeToNanoOfDay)(value.hour, value.minute, value.second, value.nanosecond);
  var packableStructFields = [packer.packable(nanoOfDay, onError)];
  packer.packStruct(LOCAL_TIME, packableStructFields, onError);
}
/**
 * Unpack local time value using the given unpacker.
 * @param {Unpacker} unpacker the unpacker to use.
 * @param {number} structSize the retrieved struct size.
 * @param {BaseBuffer} buffer the buffer to unpack from.
 * @param {boolean} disableLosslessIntegers if integer properties in the result local time should be native JS numbers.
 * @return {LocalTime} the unpacked local time value.
 */


function unpackLocalTime(unpacker, structSize, buffer, disableLosslessIntegers) {
  unpacker._verifyStructSize('LocalTime', LOCAL_TIME_STRUCT_SIZE, structSize);

  var nanoOfDay = unpacker.unpackInteger(buffer);
  var result = (0, _temporalUtil.nanoOfDayToLocalTime)(nanoOfDay);
  return convertIntegerPropsIfNeeded(result, disableLosslessIntegers);
}
/**
 * Pack given time.
 * @param {Time} value the time value to pack.
 * @param {Packer} packer the packer to use.
 * @param {function} onError the error callback.
 */


function packTime(value, packer, onError) {
  var nanoOfDay = (0, _temporalUtil.localTimeToNanoOfDay)(value.hour, value.minute, value.second, value.nanosecond);
  var offsetSeconds = (0, _integer["int"])(value.timeZoneOffsetSeconds);
  var packableStructFields = [packer.packable(nanoOfDay, onError), packer.packable(offsetSeconds, onError)];
  packer.packStruct(TIME, packableStructFields, onError);
}
/**
 * Unpack time value using the given unpacker.
 * @param {Unpacker} unpacker the unpacker to use.
 * @param {number} structSize the retrieved struct size.
 * @param {BaseBuffer} buffer the buffer to unpack from.
 * @param {boolean} disableLosslessIntegers if integer properties in the result time should be native JS numbers.
 * @return {Time} the unpacked time value.
 */


function unpackTime(unpacker, structSize, buffer, disableLosslessIntegers) {
  unpacker._verifyStructSize('Time', TIME_STRUCT_SIZE, structSize);

  var nanoOfDay = unpacker.unpackInteger(buffer);
  var offsetSeconds = unpacker.unpackInteger(buffer);
  var localTime = (0, _temporalUtil.nanoOfDayToLocalTime)(nanoOfDay);
  var result = new _temporalTypes.Time(localTime.hour, localTime.minute, localTime.second, localTime.nanosecond, offsetSeconds);
  return convertIntegerPropsIfNeeded(result, disableLosslessIntegers);
}
/**
 * Pack given neo4j date.
 * @param {Date} value the date value to pack.
 * @param {Packer} packer the packer to use.
 * @param {function} onError the error callback.
 */


function packDate(value, packer, onError) {
  var epochDay = (0, _temporalUtil.dateToEpochDay)(value.year, value.month, value.day);
  var packableStructFields = [packer.packable(epochDay, onError)];
  packer.packStruct(DATE, packableStructFields, onError);
}
/**
 * Unpack neo4j date value using the given unpacker.
 * @param {Unpacker} unpacker the unpacker to use.
 * @param {number} structSize the retrieved struct size.
 * @param {BaseBuffer} buffer the buffer to unpack from.
 * @param {boolean} disableLosslessIntegers if integer properties in the result date should be native JS numbers.
 * @return {Date} the unpacked neo4j date value.
 */


function unpackDate(unpacker, structSize, buffer, disableLosslessIntegers) {
  unpacker._verifyStructSize('Date', DATE_STRUCT_SIZE, structSize);

  var epochDay = unpacker.unpackInteger(buffer);
  var result = (0, _temporalUtil.epochDayToDate)(epochDay);
  return convertIntegerPropsIfNeeded(result, disableLosslessIntegers);
}
/**
 * Pack given local date time.
 * @param {LocalDateTime} value the local date time value to pack.
 * @param {Packer} packer the packer to use.
 * @param {function} onError the error callback.
 */


function packLocalDateTime(value, packer, onError) {
  var epochSecond = (0, _temporalUtil.localDateTimeToEpochSecond)(value.year, value.month, value.day, value.hour, value.minute, value.second, value.nanosecond);
  var nano = (0, _integer["int"])(value.nanosecond);
  var packableStructFields = [packer.packable(epochSecond, onError), packer.packable(nano, onError)];
  packer.packStruct(LOCAL_DATE_TIME, packableStructFields, onError);
}
/**
 * Unpack local date time value using the given unpacker.
 * @param {Unpacker} unpacker the unpacker to use.
 * @param {number} structSize the retrieved struct size.
 * @param {BaseBuffer} buffer the buffer to unpack from.
 * @param {boolean} disableLosslessIntegers if integer properties in the result local date-time should be native JS numbers.
 * @return {LocalDateTime} the unpacked local date time value.
 */


function unpackLocalDateTime(unpacker, structSize, buffer, disableLosslessIntegers) {
  unpacker._verifyStructSize('LocalDateTime', LOCAL_DATE_TIME_STRUCT_SIZE, structSize);

  var epochSecond = unpacker.unpackInteger(buffer);
  var nano = unpacker.unpackInteger(buffer);
  var result = (0, _temporalUtil.epochSecondAndNanoToLocalDateTime)(epochSecond, nano);
  return convertIntegerPropsIfNeeded(result, disableLosslessIntegers);
}
/**
 * Pack given date time.
 * @param {DateTime} value the date time value to pack.
 * @param {Packer} packer the packer to use.
 * @param {function} onError the error callback.
 */


function packDateTime(value, packer, onError) {
  if (value.timeZoneId) {
    packDateTimeWithZoneId(value, packer, onError);
  } else {
    packDateTimeWithZoneOffset(value, packer, onError);
  }
}
/**
 * Pack given date time with zone offset.
 * @param {DateTime} value the date time value to pack.
 * @param {Packer} packer the packer to use.
 * @param {function} onError the error callback.
 */


function packDateTimeWithZoneOffset(value, packer, onError) {
  var epochSecond = (0, _temporalUtil.localDateTimeToEpochSecond)(value.year, value.month, value.day, value.hour, value.minute, value.second, value.nanosecond);
  var nano = (0, _integer["int"])(value.nanosecond);
  var timeZoneOffsetSeconds = (0, _integer["int"])(value.timeZoneOffsetSeconds);
  var packableStructFields = [packer.packable(epochSecond, onError), packer.packable(nano, onError), packer.packable(timeZoneOffsetSeconds, onError)];
  packer.packStruct(DATE_TIME_WITH_ZONE_OFFSET, packableStructFields, onError);
}
/**
 * Unpack date time with zone offset value using the given unpacker.
 * @param {Unpacker} unpacker the unpacker to use.
 * @param {number} structSize the retrieved struct size.
 * @param {BaseBuffer} buffer the buffer to unpack from.
 * @param {boolean} disableLosslessIntegers if integer properties in the result date-time should be native JS numbers.
 * @return {DateTime} the unpacked date time with zone offset value.
 */


function unpackDateTimeWithZoneOffset(unpacker, structSize, buffer, disableLosslessIntegers) {
  unpacker._verifyStructSize('DateTimeWithZoneOffset', DATE_TIME_WITH_ZONE_OFFSET_STRUCT_SIZE, structSize);

  var epochSecond = unpacker.unpackInteger(buffer);
  var nano = unpacker.unpackInteger(buffer);
  var timeZoneOffsetSeconds = unpacker.unpackInteger(buffer);
  var localDateTime = (0, _temporalUtil.epochSecondAndNanoToLocalDateTime)(epochSecond, nano);
  var result = new _temporalTypes.DateTime(localDateTime.year, localDateTime.month, localDateTime.day, localDateTime.hour, localDateTime.minute, localDateTime.second, localDateTime.nanosecond, timeZoneOffsetSeconds, null);
  return convertIntegerPropsIfNeeded(result, disableLosslessIntegers);
}
/**
 * Pack given date time with zone id.
 * @param {DateTime} value the date time value to pack.
 * @param {Packer} packer the packer to use.
 * @param {function} onError the error callback.
 */


function packDateTimeWithZoneId(value, packer, onError) {
  var epochSecond = (0, _temporalUtil.localDateTimeToEpochSecond)(value.year, value.month, value.day, value.hour, value.minute, value.second, value.nanosecond);
  var nano = (0, _integer["int"])(value.nanosecond);
  var timeZoneId = value.timeZoneId;
  var packableStructFields = [packer.packable(epochSecond, onError), packer.packable(nano, onError), packer.packable(timeZoneId, onError)];
  packer.packStruct(DATE_TIME_WITH_ZONE_ID, packableStructFields, onError);
}
/**
 * Unpack date time with zone id value using the given unpacker.
 * @param {Unpacker} unpacker the unpacker to use.
 * @param {number} structSize the retrieved struct size.
 * @param {BaseBuffer} buffer the buffer to unpack from.
 * @param {boolean} disableLosslessIntegers if integer properties in the result date-time should be native JS numbers.
 * @return {DateTime} the unpacked date time with zone id value.
 */


function unpackDateTimeWithZoneId(unpacker, structSize, buffer, disableLosslessIntegers) {
  unpacker._verifyStructSize('DateTimeWithZoneId', DATE_TIME_WITH_ZONE_ID_STRUCT_SIZE, structSize);

  var epochSecond = unpacker.unpackInteger(buffer);
  var nano = unpacker.unpackInteger(buffer);
  var timeZoneId = unpacker.unpack(buffer);
  var localDateTime = (0, _temporalUtil.epochSecondAndNanoToLocalDateTime)(epochSecond, nano);
  var result = new _temporalTypes.DateTime(localDateTime.year, localDateTime.month, localDateTime.day, localDateTime.hour, localDateTime.minute, localDateTime.second, localDateTime.nanosecond, null, timeZoneId);
  return convertIntegerPropsIfNeeded(result, disableLosslessIntegers);
}

function convertIntegerPropsIfNeeded(obj, disableLosslessIntegers) {
  if (!disableLosslessIntegers) {
    return obj;
  }

  var clone = Object.create(Object.getPrototypeOf(obj));

  for (var prop in obj) {
    if (obj.hasOwnProperty(prop)) {
      var value = obj[prop];
      clone[prop] = (0, _integer.isInt)(value) ? value.toNumberOrInfinity() : value;
    }
  }

  Object.freeze(clone);
  return clone;
}

},{"../integer":32,"../internal/temporal-util":76,"../spatial-types":86,"../temporal-types":87,"./packstream-v1":60,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/get":8,"@babel/runtime/helpers/getPrototypeOf":9,"@babel/runtime/helpers/inherits":10,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/interopRequireWildcard":12,"@babel/runtime/helpers/possibleConstructorReturn":18}],62:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.DEFAULT_ACQUISITION_TIMEOUT = exports.DEFAULT_MAX_SIZE = exports["default"] = void 0;

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
var DEFAULT_MAX_SIZE = 100;
exports.DEFAULT_MAX_SIZE = DEFAULT_MAX_SIZE;
var DEFAULT_ACQUISITION_TIMEOUT = 60 * 1000; // 60 seconds

exports.DEFAULT_ACQUISITION_TIMEOUT = DEFAULT_ACQUISITION_TIMEOUT;

var PoolConfig =
/*#__PURE__*/
function () {
  function PoolConfig(maxSize, acquisitionTimeout) {
    (0, _classCallCheck2["default"])(this, PoolConfig);
    this.maxSize = valueOrDefault(maxSize, DEFAULT_MAX_SIZE);
    this.acquisitionTimeout = valueOrDefault(acquisitionTimeout, DEFAULT_ACQUISITION_TIMEOUT);
  }

  (0, _createClass2["default"])(PoolConfig, null, [{
    key: "defaultConfig",
    value: function defaultConfig() {
      return new PoolConfig(DEFAULT_MAX_SIZE, DEFAULT_ACQUISITION_TIMEOUT);
    }
  }, {
    key: "fromDriverConfig",
    value: function fromDriverConfig(config) {
      var maxIdleSizeConfigured = isConfigured(config.connectionPoolSize);
      var maxSizeConfigured = isConfigured(config.maxConnectionPoolSize);
      var maxSize;

      if (maxSizeConfigured) {
        // correct size setting is set - use it's value
        maxSize = config.maxConnectionPoolSize;
      } else if (maxIdleSizeConfigured) {
        // deprecated size setting is set - use it's value
        console.warn('WARNING: neo4j-driver setting "connectionPoolSize" is deprecated, please use "maxConnectionPoolSize" instead');
        maxSize = config.connectionPoolSize;
      } else {
        maxSize = DEFAULT_MAX_SIZE;
      }

      var acquisitionTimeoutConfigured = isConfigured(config.connectionAcquisitionTimeout);
      var acquisitionTimeout = acquisitionTimeoutConfigured ? config.connectionAcquisitionTimeout : DEFAULT_ACQUISITION_TIMEOUT;
      return new PoolConfig(maxSize, acquisitionTimeout);
    }
  }]);
  return PoolConfig;
}();

exports["default"] = PoolConfig;

function valueOrDefault(value, defaultValue) {
  return value === 0 || value ? value : defaultValue;
}

function isConfigured(value) {
  return value === 0 || value;
}

},{"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11}],63:[function(require,module,exports){
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

},{"../error":29,"./logger":59,"./pool-config":62,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11}],64:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _node = require('./browser');

var _error = require("../error");

var _boltProtocolV = _interopRequireDefault(require("./bolt-protocol-v1"));

var _boltProtocolV2 = _interopRequireDefault(require("./bolt-protocol-v2"));

var _boltProtocolV3 = _interopRequireDefault(require("./bolt-protocol-v3"));

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
var HTTP_MAGIC_PREAMBLE = 1213486160; // == 0x48545450 == "HTTP"

var BOLT_MAGIC_PREAMBLE = 0x6060b017;

var ProtocolHandshaker =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {Connection} connection the connection owning this protocol.
   * @param {Channel} channel the network channel.
   * @param {Chunker} chunker the message chunker.
   * @param {boolean} disableLosslessIntegers flag to use native JS numbers.
   * @param {Logger} log the logger.
   */
  function ProtocolHandshaker(connection, channel, chunker, disableLosslessIntegers, log) {
    (0, _classCallCheck2["default"])(this, ProtocolHandshaker);
    this._connection = connection;
    this._channel = channel;
    this._chunker = chunker;
    this._disableLosslessIntegers = disableLosslessIntegers;
    this._log = log;
  }
  /**
   * Write a Bolt handshake into the underlying network channel.
   */


  (0, _createClass2["default"])(ProtocolHandshaker, [{
    key: "writeHandshakeRequest",
    value: function writeHandshakeRequest() {
      this._channel.write(newHandshakeBuffer());
    }
    /**
     * Read the given handshake response and create the negotiated bolt protocol.
     * @param {BaseBuffer} buffer byte buffer containing the handshake response.
     * @return {BoltProtocol} bolt protocol corresponding to the version suggested by the database.
     * @throws {Neo4jError} when bolt protocol can't be instantiated.
     */

  }, {
    key: "createNegotiatedProtocol",
    value: function createNegotiatedProtocol(buffer) {
      var negotiatedVersion = buffer.readInt32();

      if (this._log.isDebugEnabled()) {
        this._log.debug("".concat(this._connection, " negotiated protocol version ").concat(negotiatedVersion));
      }

      return this._createProtocolWithVersion(negotiatedVersion);
    }
    /**
     * @return {BoltProtocol}
     * @private
     */

  }, {
    key: "_createProtocolWithVersion",
    value: function _createProtocolWithVersion(version) {
      switch (version) {
        case 1:
          return new _boltProtocolV["default"](this._connection, this._chunker, this._disableLosslessIntegers);

        case 2:
          return new _boltProtocolV2["default"](this._connection, this._chunker, this._disableLosslessIntegers);

        case 3:
          return new _boltProtocolV3["default"](this._connection, this._chunker, this._disableLosslessIntegers);

        case HTTP_MAGIC_PREAMBLE:
          throw (0, _error.newError)('Server responded HTTP. Make sure you are not trying to connect to the http endpoint ' + '(HTTP defaults to port 7474 whereas BOLT defaults to port 7687)');

        default:
          throw (0, _error.newError)('Unknown Bolt protocol version: ' + version);
      }
    }
  }]);
  return ProtocolHandshaker;
}();
/**
 * @return {BaseBuffer}
 * @private
 */


exports["default"] = ProtocolHandshaker;

function newHandshakeBuffer() {
  var handshakeBuffer = (0, _node.alloc)(5 * 4); // magic preamble

  handshakeBuffer.writeInt32(BOLT_MAGIC_PREAMBLE); // proposed versions

  handshakeBuffer.writeInt32(3);
  handshakeBuffer.writeInt32(2);
  handshakeBuffer.writeInt32(1);
  handshakeBuffer.writeInt32(0); // reset the reader position

  handshakeBuffer.reset();
  return handshakeBuffer;
}

},{"../error":29,"./bolt-protocol-v1":33,"./bolt-protocol-v2":34,"./bolt-protocol-v3":35,"./browser":41,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11}],65:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _routingTable = _interopRequireDefault(require("./routing-table"));

var _error = require("../error");

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
var Rediscovery =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {RoutingUtil} routingUtil the util to use.
   */
  function Rediscovery(routingUtil) {
    (0, _classCallCheck2["default"])(this, Rediscovery);
    this._routingUtil = routingUtil;
  }
  /**
   * Try to fetch new routing table from the given router.
   * @param {Session} session the session to use.
   * @param {string} routerAddress the URL of the router.
   * @return {Promise<RoutingTable>} promise resolved with new routing table or null when connection error happened.
   */


  (0, _createClass2["default"])(Rediscovery, [{
    key: "lookupRoutingTableOnRouter",
    value: function lookupRoutingTableOnRouter(session, routerAddress) {
      var _this = this;

      return this._routingUtil.callRoutingProcedure(session, routerAddress).then(function (records) {
        if (records === null) {
          // connection error happened, unable to retrieve routing table from this router, next one should be queried
          return null;
        }

        if (records.length !== 1) {
          throw (0, _error.newError)('Illegal response from router "' + routerAddress + '". ' + 'Received ' + records.length + ' records but expected only one.\n' + JSON.stringify(records), _error.PROTOCOL_ERROR);
        }

        var record = records[0];

        var expirationTime = _this._routingUtil.parseTtl(record, routerAddress);

        var _this$_routingUtil$pa = _this._routingUtil.parseServers(record, routerAddress),
            routers = _this$_routingUtil$pa.routers,
            readers = _this$_routingUtil$pa.readers,
            writers = _this$_routingUtil$pa.writers;

        Rediscovery._assertNonEmpty(routers, 'routers', routerAddress);

        Rediscovery._assertNonEmpty(readers, 'readers', routerAddress); // case with no writers is processed higher in the promise chain because only RoutingDriver knows
        // how to deal with such table and how to treat router that returned such table


        return new _routingTable["default"](routers, readers, writers, expirationTime);
      });
    }
  }], [{
    key: "_assertNonEmpty",
    value: function _assertNonEmpty(serverAddressesArray, serversName, routerAddress) {
      if (serverAddressesArray.length === 0) {
        throw (0, _error.newError)('Received no ' + serversName + ' from router ' + routerAddress, _error.PROTOCOL_ERROR);
      }
    }
  }]);
  return Rediscovery;
}();

exports["default"] = Rediscovery;

},{"../error":29,"./routing-table":71,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11}],66:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _constants = require("./constants");

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
// Signature bytes for each request message type
var INIT = 0x01; // 0000 0001 // INIT <user_agent> <authentication_token>
// eslint-disable-next-line no-unused-vars

var ACK_FAILURE = 0x0e; // 0000 1110 // ACK_FAILURE - unused

var RESET = 0x0f; // 0000 1111 // RESET

var RUN = 0x10; // 0001 0000 // RUN <statement> <parameters>
// eslint-disable-next-line no-unused-vars

var DISCARD_ALL = 0x2f; // 0010 1111 // DISCARD_ALL - unused

var PULL_ALL = 0x3f; // 0011 1111 // PULL_ALL

var HELLO = 0x01; // 0000 0001 // HELLO <metadata>

var GOODBYE = 0x02; // 0000 0010 // GOODBYE

var BEGIN = 0x11; // 0001 0001 // BEGIN <metadata>

var COMMIT = 0x12; // 0001 0010 // COMMIT

var ROLLBACK = 0x13; // 0001 0011 // ROLLBACK

var READ_MODE = 'r';

var RequestMessage =
/*#__PURE__*/
function () {
  function RequestMessage(signature, fields, toString) {
    (0, _classCallCheck2["default"])(this, RequestMessage);
    this.signature = signature;
    this.fields = fields;
    this.toString = toString;
  }
  /**
   * Create a new INIT message.
   * @param {string} clientName the client name.
   * @param {object} authToken the authentication token.
   * @return {RequestMessage} new INIT message.
   */


  (0, _createClass2["default"])(RequestMessage, null, [{
    key: "init",
    value: function init(clientName, authToken) {
      return new RequestMessage(INIT, [clientName, authToken], function () {
        return "INIT ".concat(clientName, " {...}");
      });
    }
    /**
     * Create a new RUN message.
     * @param {string} statement the cypher statement.
     * @param {object} parameters the statement parameters.
     * @return {RequestMessage} new RUN message.
     */

  }, {
    key: "run",
    value: function run(statement, parameters) {
      return new RequestMessage(RUN, [statement, parameters], function () {
        return "RUN ".concat(statement, " ").concat(JSON.stringify(parameters));
      });
    }
    /**
     * Get a PULL_ALL message.
     * @return {RequestMessage} the PULL_ALL message.
     */

  }, {
    key: "pullAll",
    value: function pullAll() {
      return PULL_ALL_MESSAGE;
    }
    /**
     * Get a RESET message.
     * @return {RequestMessage} the RESET message.
     */

  }, {
    key: "reset",
    value: function reset() {
      return RESET_MESSAGE;
    }
    /**
     * Create a new HELLO message.
     * @param {string} userAgent the user agent.
     * @param {object} authToken the authentication token.
     * @return {RequestMessage} new HELLO message.
     */

  }, {
    key: "hello",
    value: function hello(userAgent, authToken) {
      var metadata = Object.assign({
        user_agent: userAgent
      }, authToken);
      return new RequestMessage(HELLO, [metadata], function () {
        return "HELLO {user_agent: '".concat(userAgent, "', ...}");
      });
    }
    /**
     * Create a new BEGIN message.
     * @param {Bookmark} bookmark the bookmark.
     * @param {TxConfig} txConfig the configuration.
     * @param {string} mode the access mode.
     * @return {RequestMessage} new BEGIN message.
     */

  }, {
    key: "begin",
    value: function begin(bookmark, txConfig, mode) {
      var metadata = buildTxMetadata(bookmark, txConfig, mode);
      return new RequestMessage(BEGIN, [metadata], function () {
        return "BEGIN ".concat(JSON.stringify(metadata));
      });
    }
    /**
     * Get a COMMIT message.
     * @return {RequestMessage} the COMMIT message.
     */

  }, {
    key: "commit",
    value: function commit() {
      return COMMIT_MESSAGE;
    }
    /**
     * Get a ROLLBACK message.
     * @return {RequestMessage} the ROLLBACK message.
     */

  }, {
    key: "rollback",
    value: function rollback() {
      return ROLLBACK_MESSAGE;
    }
    /**
     * Create a new RUN message with additional metadata.
     * @param {string} statement the cypher statement.
     * @param {object} parameters the statement parameters.
     * @param {Bookmark} bookmark the bookmark.
     * @param {TxConfig} txConfig the configuration.
     * @param {string} mode the access mode.
     * @return {RequestMessage} new RUN message with additional metadata.
     */

  }, {
    key: "runWithMetadata",
    value: function runWithMetadata(statement, parameters, bookmark, txConfig, mode) {
      var metadata = buildTxMetadata(bookmark, txConfig, mode);
      return new RequestMessage(RUN, [statement, parameters, metadata], function () {
        return "RUN ".concat(statement, " ").concat(JSON.stringify(parameters), " ").concat(JSON.stringify(metadata));
      });
    }
    /**
     * Get a GOODBYE message.
     * @return {RequestMessage} the GOODBYE message.
     */

  }, {
    key: "goodbye",
    value: function goodbye() {
      return GOODBYE_MESSAGE;
    }
  }]);
  return RequestMessage;
}();
/**
 * Create an object that represent transaction metadata.
 * @param {Bookmark} bookmark the bookmark.
 * @param {TxConfig} txConfig the configuration.
 * @param {string} mode the access mode.
 * @return {object} a metadata object.
 */


exports["default"] = RequestMessage;

function buildTxMetadata(bookmark, txConfig, mode) {
  var metadata = {};

  if (!bookmark.isEmpty()) {
    metadata['bookmarks'] = bookmark.values();
  }

  if (txConfig.timeout) {
    metadata['tx_timeout'] = txConfig.timeout;
  }

  if (txConfig.metadata) {
    metadata['tx_metadata'] = txConfig.metadata;
  }

  if (mode === _constants.ACCESS_MODE_READ) {
    metadata['mode'] = READ_MODE;
  }

  return metadata;
} // constants for messages that never change


var PULL_ALL_MESSAGE = new RequestMessage(PULL_ALL, [], function () {
  return 'PULL_ALL';
});
var RESET_MESSAGE = new RequestMessage(RESET, [], function () {
  return 'RESET';
});
var COMMIT_MESSAGE = new RequestMessage(COMMIT, [], function () {
  return 'COMMIT';
});
var ROLLBACK_MESSAGE = new RequestMessage(ROLLBACK, [], function () {
  return 'ROLLBACK';
});
var GOODBYE_MESSAGE = new RequestMessage(GOODBYE, [], function () {
  return 'GOODBYE';
});

},{"./constants":51,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11}],67:[function(require,module,exports){
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
var BaseHostNameResolver =
/*#__PURE__*/
function () {
  function BaseHostNameResolver() {
    (0, _classCallCheck2["default"])(this, BaseHostNameResolver);
  }

  (0, _createClass2["default"])(BaseHostNameResolver, [{
    key: "resolve",
    value: function resolve() {
      throw new Error('Abstract function');
    }
    /**
     * @protected
     */

  }, {
    key: "_resolveToItself",
    value: function _resolveToItself(address) {
      return Promise.resolve([address]);
    }
  }]);
  return BaseHostNameResolver;
}();

exports["default"] = BaseHostNameResolver;

},{"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11}],68:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _serverAddress = _interopRequireDefault(require("../server-address"));

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
function resolveToSelf(address) {
  return Promise.resolve([address]);
}

var ConfiguredCustomResolver =
/*#__PURE__*/
function () {
  function ConfiguredCustomResolver(resolverFunction) {
    (0, _classCallCheck2["default"])(this, ConfiguredCustomResolver);
    this._resolverFunction = resolverFunction || resolveToSelf;
  }

  (0, _createClass2["default"])(ConfiguredCustomResolver, [{
    key: "resolve",
    value: function resolve(seedRouter) {
      var _this = this;

      return new Promise(function (resolve) {
        return resolve(_this._resolverFunction(seedRouter.asHostPort()));
      }).then(function (resolved) {
        if (!Array.isArray(resolved)) {
          throw new TypeError("Configured resolver function should either return an array of addresses or a Promise resolved with an array of addresses." + "Each address is '<host>:<port>'. Got: ".concat(resolved));
        }

        return resolved.map(function (r) {
          return _serverAddress["default"].fromUrl(r);
        });
      });
    }
  }]);
  return ConfiguredCustomResolver;
}();

exports["default"] = ConfiguredCustomResolver;

},{"../server-address":73,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11}],69:[function(require,module,exports){
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
var RoundRobinArrayIndex =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {number} [initialOffset=0] the initial offset for round robin.
   */
  function RoundRobinArrayIndex(initialOffset) {
    (0, _classCallCheck2["default"])(this, RoundRobinArrayIndex);
    this._offset = initialOffset || 0;
  }
  /**
   * Get next index for an array with given length.
   * @param {number} arrayLength the array length.
   * @return {number} index in the array.
   */


  (0, _createClass2["default"])(RoundRobinArrayIndex, [{
    key: "next",
    value: function next(arrayLength) {
      if (arrayLength === 0) {
        return -1;
      }

      var nextOffset = this._offset;
      this._offset += 1;

      if (this._offset === Number.MAX_SAFE_INTEGER) {
        this._offset = 0;
      }

      return nextOffset % arrayLength;
    }
  }]);
  return RoundRobinArrayIndex;
}();

exports["default"] = RoundRobinArrayIndex;

},{"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11}],70:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = exports.ROUND_ROBIN_STRATEGY_NAME = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _possibleConstructorReturn2 = _interopRequireDefault(require("@babel/runtime/helpers/possibleConstructorReturn"));

var _getPrototypeOf2 = _interopRequireDefault(require("@babel/runtime/helpers/getPrototypeOf"));

var _inherits2 = _interopRequireDefault(require("@babel/runtime/helpers/inherits"));

var _roundRobinArrayIndex = _interopRequireDefault(require("./round-robin-array-index"));

var _loadBalancingStrategy = _interopRequireDefault(require("./load-balancing-strategy"));

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
var ROUND_ROBIN_STRATEGY_NAME = 'round_robin';
exports.ROUND_ROBIN_STRATEGY_NAME = ROUND_ROBIN_STRATEGY_NAME;

var RoundRobinLoadBalancingStrategy =
/*#__PURE__*/
function (_LoadBalancingStrateg) {
  (0, _inherits2["default"])(RoundRobinLoadBalancingStrategy, _LoadBalancingStrateg);

  function RoundRobinLoadBalancingStrategy() {
    var _this;

    (0, _classCallCheck2["default"])(this, RoundRobinLoadBalancingStrategy);
    _this = (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(RoundRobinLoadBalancingStrategy).call(this));
    _this._readersIndex = new _roundRobinArrayIndex["default"]();
    _this._writersIndex = new _roundRobinArrayIndex["default"]();
    return _this;
  }
  /**
   * @inheritDoc
   */


  (0, _createClass2["default"])(RoundRobinLoadBalancingStrategy, [{
    key: "selectReader",
    value: function selectReader(knownReaders) {
      return this._select(knownReaders, this._readersIndex);
    }
    /**
     * @inheritDoc
     */

  }, {
    key: "selectWriter",
    value: function selectWriter(knownWriters) {
      return this._select(knownWriters, this._writersIndex);
    }
  }, {
    key: "_select",
    value: function _select(addresses, roundRobinIndex) {
      var length = addresses.length;

      if (length === 0) {
        return null;
      }

      var index = roundRobinIndex.next(length);
      return addresses[index];
    }
  }]);
  return RoundRobinLoadBalancingStrategy;
}(_loadBalancingStrategy["default"]);

exports["default"] = RoundRobinLoadBalancingStrategy;

},{"./load-balancing-strategy":58,"./round-robin-array-index":69,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/getPrototypeOf":9,"@babel/runtime/helpers/inherits":10,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/possibleConstructorReturn":18}],71:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _toConsumableArray2 = _interopRequireDefault(require("@babel/runtime/helpers/toConsumableArray"));

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _integer = require("../integer");

var _driver = require("../driver");

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
var MIN_ROUTERS = 1;

var RoutingTable =
/*#__PURE__*/
function () {
  function RoutingTable(routers, readers, writers, expirationTime) {
    (0, _classCallCheck2["default"])(this, RoutingTable);
    this.routers = routers || [];
    this.readers = readers || [];
    this.writers = writers || [];
    this.expirationTime = expirationTime || (0, _integer["int"])(0);
  }

  (0, _createClass2["default"])(RoutingTable, [{
    key: "forget",
    value: function forget(address) {
      // Don't remove it from the set of routers, since that might mean we lose our ability to re-discover,
      // just remove it from the set of readers and writers, so that we don't use it for actual work without
      // performing discovery first.
      this.readers = removeFromArray(this.readers, address);
      this.writers = removeFromArray(this.writers, address);
    }
  }, {
    key: "forgetRouter",
    value: function forgetRouter(address) {
      this.routers = removeFromArray(this.routers, address);
    }
  }, {
    key: "forgetWriter",
    value: function forgetWriter(address) {
      this.writers = removeFromArray(this.writers, address);
    }
    /**
     * Check if this routing table is fresh to perform the required operation.
     * @param {string} accessMode the type of operation. Allowed values are {@link READ} and {@link WRITE}.
     * @return {boolean} `true` when this table contains servers to serve the required operation, `false` otherwise.
     */

  }, {
    key: "isStaleFor",
    value: function isStaleFor(accessMode) {
      return this.expirationTime.lessThan(Date.now()) || this.routers.length < MIN_ROUTERS || accessMode === _driver.READ && this.readers.length === 0 || accessMode === _driver.WRITE && this.writers.length === 0;
    }
  }, {
    key: "allServers",
    value: function allServers() {
      return [].concat((0, _toConsumableArray2["default"])(this.routers), (0, _toConsumableArray2["default"])(this.readers), (0, _toConsumableArray2["default"])(this.writers));
    }
  }, {
    key: "toString",
    value: function toString() {
      return "RoutingTable[" + "expirationTime=".concat(this.expirationTime, ", ") + "currentTime=".concat(Date.now(), ", ") + "routers=[".concat(this.routers, "], ") + "readers=[".concat(this.readers, "], ") + "writers=[".concat(this.writers, "]]");
    }
  }]);
  return RoutingTable;
}();
/**
 * Remove all occurrences of the element in the array.
 * @param {Array} array the array to filter.
 * @param {object} element the element to remove.
 * @return {Array} new filtered array.
 */


exports["default"] = RoutingTable;

function removeFromArray(array, element) {
  return array.filter(function (item) {
    return item.asKey() !== element.asKey();
  });
}

},{"../driver":28,"../integer":32,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/toConsumableArray":22}],72:[function(require,module,exports){
"use strict";

var _interopRequireWildcard = require("@babel/runtime/helpers/interopRequireWildcard");

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _error = require("../error");

var _integer = _interopRequireWildcard(require("../integer"));

var _serverVersion = require("./server-version");

var _bookmark = _interopRequireDefault(require("./bookmark"));

var _txConfig = _interopRequireDefault(require("./tx-config"));

var _constants = require("./constants");

var _serverAddress = _interopRequireDefault(require("./server-address"));

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
var CALL_GET_SERVERS = 'CALL dbms.cluster.routing.getServers';
var CALL_GET_ROUTING_TABLE = 'CALL dbms.cluster.routing.getRoutingTable($context)';
var PROCEDURE_NOT_FOUND_CODE = 'Neo.ClientError.Procedure.ProcedureNotFound';

var RoutingUtil =
/*#__PURE__*/
function () {
  function RoutingUtil(routingContext) {
    (0, _classCallCheck2["default"])(this, RoutingUtil);
    this._routingContext = routingContext;
  }
  /**
   * Invoke routing procedure using the given session.
   * @param {Session} session the session to use.
   * @param {string} routerAddress the URL of the router.
   * @return {Promise<Record[]>} promise resolved with records returned by the procedure call or null if
   * connection error happened.
   */


  (0, _createClass2["default"])(RoutingUtil, [{
    key: "callRoutingProcedure",
    value: function callRoutingProcedure(session, routerAddress) {
      return this._callAvailableRoutingProcedure(session).then(function (result) {
        session.close();
        return result.records;
      })["catch"](function (error) {
        if (error.code === PROCEDURE_NOT_FOUND_CODE) {
          // throw when getServers procedure not found because this is clearly a configuration issue
          throw (0, _error.newError)("Server at ".concat(routerAddress.asHostPort(), " can't perform routing. Make sure you are connecting to a causal cluster"), _error.SERVICE_UNAVAILABLE);
        } else {
          // return nothing when failed to connect because code higher in the callstack is still able to retry with a
          // different session towards a different router
          return null;
        }
      });
    }
  }, {
    key: "parseTtl",
    value: function parseTtl(record, routerAddress) {
      try {
        var now = (0, _integer["int"])(Date.now());
        var expires = (0, _integer["int"])(record.get('ttl')).multiply(1000).add(now); // if the server uses a really big expire time like Long.MAX_VALUE this may have overflowed

        if (expires.lessThan(now)) {
          return _integer["default"].MAX_VALUE;
        }

        return expires;
      } catch (error) {
        throw (0, _error.newError)("Unable to parse TTL entry from router ".concat(routerAddress, " from record:\n").concat(JSON.stringify(record), "\nError message: ").concat(error.message), _error.PROTOCOL_ERROR);
      }
    }
  }, {
    key: "parseServers",
    value: function parseServers(record, routerAddress) {
      try {
        var servers = record.get('servers');
        var routers = [];
        var readers = [];
        var writers = [];
        servers.forEach(function (server) {
          var role = server['role'];
          var addresses = server['addresses'];

          if (role === 'ROUTE') {
            routers = parseArray(addresses).map(function (address) {
              return _serverAddress["default"].fromUrl(address);
            });
          } else if (role === 'WRITE') {
            writers = parseArray(addresses).map(function (address) {
              return _serverAddress["default"].fromUrl(address);
            });
          } else if (role === 'READ') {
            readers = parseArray(addresses).map(function (address) {
              return _serverAddress["default"].fromUrl(address);
            });
          } else {
            throw (0, _error.newError)('Unknown server role "' + role + '"', _error.PROTOCOL_ERROR);
          }
        });
        return {
          routers: routers,
          readers: readers,
          writers: writers
        };
      } catch (error) {
        throw (0, _error.newError)("Unable to parse servers entry from router ".concat(routerAddress, " from record:\n").concat(JSON.stringify(record), "\nError message: ").concat(error.message), _error.PROTOCOL_ERROR);
      }
    }
  }, {
    key: "_callAvailableRoutingProcedure",
    value: function _callAvailableRoutingProcedure(session) {
      var _this = this;

      return session._run(null, null, function (connection, streamObserver) {
        var serverVersionString = connection.server.version;

        var serverVersion = _serverVersion.ServerVersion.fromString(serverVersionString);

        var query;
        var params;

        if (serverVersion.compareTo(_serverVersion.VERSION_3_2_0) >= 0) {
          query = CALL_GET_ROUTING_TABLE;
          params = {
            context: _this._routingContext
          };
        } else {
          query = CALL_GET_SERVERS;
          params = {};
        }

        connection.protocol().run(query, params, _bookmark["default"].empty(), _txConfig["default"].empty(), _constants.ACCESS_MODE_WRITE, streamObserver);
      });
    }
  }]);
  return RoutingUtil;
}();

exports["default"] = RoutingUtil;

function parseArray(addresses) {
  if (!Array.isArray(addresses)) {
    throw new TypeError('Array expected but got: ' + addresses);
  }

  return Array.from(addresses);
}

},{"../error":29,"../integer":32,"./bookmark":36,"./constants":51,"./server-address":73,"./server-version":74,"./tx-config":78,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/interopRequireWildcard":12}],73:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _util = require("./util");

var _urlUtil = _interopRequireDefault(require("./url-util"));

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
var ServerAddress =
/*#__PURE__*/
function () {
  function ServerAddress(host, resolved, port, hostPort) {
    (0, _classCallCheck2["default"])(this, ServerAddress);
    this._host = (0, _util.assertString)(host, 'host');
    this._resolved = resolved ? (0, _util.assertString)(resolved, 'resolved') : null;
    this._port = (0, _util.assertNumber)(port, 'port');
    this._hostPort = hostPort;
    this._stringValue = resolved ? "".concat(hostPort, "(").concat(resolved, ")") : "".concat(hostPort);
  }

  (0, _createClass2["default"])(ServerAddress, [{
    key: "host",
    value: function host() {
      return this._host;
    }
  }, {
    key: "resolvedHost",
    value: function resolvedHost() {
      return this._resolved ? this._resolved : this._host;
    }
  }, {
    key: "port",
    value: function port() {
      return this._port;
    }
  }, {
    key: "resolveWith",
    value: function resolveWith(resolved) {
      return new ServerAddress(this._host, resolved, this._port, this._hostPort);
    }
  }, {
    key: "asHostPort",
    value: function asHostPort() {
      return this._hostPort;
    }
  }, {
    key: "asKey",
    value: function asKey() {
      return this._hostPort;
    }
  }, {
    key: "toString",
    value: function toString() {
      return this._stringValue;
    }
  }], [{
    key: "fromUrl",
    value: function fromUrl(url) {
      var urlParsed = _urlUtil["default"].parseDatabaseUrl(url);

      return new ServerAddress(urlParsed.host, null, urlParsed.port, urlParsed.hostAndPort);
    }
  }]);
  return ServerAddress;
}();

exports["default"] = ServerAddress;

},{"./url-util":79,"./util":80,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11}],74:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.VERSION_IN_DEV = exports.VERSION_4_0_0 = exports.VERSION_3_5_0 = exports.VERSION_3_4_0 = exports.VERSION_3_2_0 = exports.VERSION_3_1_0 = exports.ServerVersion = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _util = require("./util");

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
var SERVER_VERSION_REGEX = new RegExp('^(Neo4j/)?(\\d+)\\.(\\d+)(?:\\.)?(\\d*)(\\.|-|\\+)?([0-9A-Za-z-.]*)?$');
var NEO4J_IN_DEV_VERSION_STRING = 'Neo4j/dev';

var ServerVersion =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {number} major the major version number.
   * @param {number} minor the minor version number.
   * @param {number} patch the patch version number.
   */
  function ServerVersion(major, minor, patch) {
    (0, _classCallCheck2["default"])(this, ServerVersion);
    this.major = major;
    this.minor = minor;
    this.patch = patch;
  }
  /**
   * Fetch server version using the given driver.
   * @param {Driver} driver the driver to use.
   * @return {Promise<ServerVersion>} promise resolved with a {@link ServerVersion} object or rejected with error.
   */


  (0, _createClass2["default"])(ServerVersion, [{
    key: "compareTo",

    /**
     * Compare this version to the given one.
     * @param {ServerVersion} other the version to compare with.
     * @return {number} value 0 if this version is the same as the given one, value less then 0 when this version
     * was released earlier than the given one and value greater then 0 when this version was released after
     * than the given one.
     */
    value: function compareTo(other) {
      var result = compareInts(this.major, other.major);

      if (result === 0) {
        result = compareInts(this.minor, other.minor);

        if (result === 0) {
          result = compareInts(this.patch, other.patch);
        }
      }

      return result;
    }
  }], [{
    key: "fromDriver",
    value: function fromDriver(driver) {
      var session = driver.session();
      return session.run('RETURN 1').then(function (result) {
        session.close();
        return ServerVersion.fromString(result.summary.server.version);
      });
    }
    /**
     * Parse given string to a {@link ServerVersion} object.
     * @param {string} versionStr the string to parse.
     * @return {ServerVersion} version for the given string.
     * @throws Error if given string can't be parsed.
     */

  }, {
    key: "fromString",
    value: function fromString(versionStr) {
      if (!versionStr) {
        return new ServerVersion(3, 0, 0);
      }

      (0, _util.assertString)(versionStr, 'Neo4j version string');

      if (versionStr.toLowerCase() === NEO4J_IN_DEV_VERSION_STRING.toLowerCase()) {
        return VERSION_IN_DEV;
      }

      var version = versionStr.match(SERVER_VERSION_REGEX);

      if (!version) {
        throw new Error("Unparsable Neo4j version: ".concat(versionStr));
      }

      var major = parseIntStrict(version[2]);
      var minor = parseIntStrict(version[3]);
      var patch = parseIntStrict(version[4] || 0);
      return new ServerVersion(major, minor, patch);
    }
  }]);
  return ServerVersion;
}();

exports.ServerVersion = ServerVersion;

function parseIntStrict(str, name) {
  var value = parseInt(str, 10);

  if (!value && value !== 0) {
    throw new Error("Unparsable number ".concat(name, ": '").concat(str, "'"));
  }

  return value;
}

function compareInts(x, y) {
  return x < y ? -1 : x === y ? 0 : 1;
}

var VERSION_3_1_0 = new ServerVersion(3, 1, 0);
exports.VERSION_3_1_0 = VERSION_3_1_0;
var VERSION_3_2_0 = new ServerVersion(3, 2, 0);
exports.VERSION_3_2_0 = VERSION_3_2_0;
var VERSION_3_4_0 = new ServerVersion(3, 4, 0);
exports.VERSION_3_4_0 = VERSION_3_4_0;
var VERSION_3_5_0 = new ServerVersion(3, 5, 0);
exports.VERSION_3_5_0 = VERSION_3_5_0;
var VERSION_4_0_0 = new ServerVersion(4, 0, 0);
exports.VERSION_4_0_0 = VERSION_4_0_0;
var maxVer = Number.MAX_SAFE_INTEGER;
var VERSION_IN_DEV = new ServerVersion(maxVer, maxVer, maxVer);
exports.VERSION_IN_DEV = VERSION_IN_DEV;

},{"./util":80,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11}],75:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _record = _interopRequireDefault(require("../record"));

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
 * Handles a RUN/PULL_ALL, or RUN/DISCARD_ALL requests, maps the responses
 * in a way that a user-provided observer can see these as a clean Stream
 * of records.
 * This class will queue up incoming messages until a user-provided observer
 * for the incoming stream is registered. Thus, we keep fields around
 * for tracking head/records/tail. These are only used if there is no
 * observer registered.
 * @access private
 */
var StreamObserver =
/*#__PURE__*/
function () {
  function StreamObserver() {
    (0, _classCallCheck2["default"])(this, StreamObserver);
    this._fieldKeys = null;
    this._fieldLookup = null;
    this._queuedRecords = [];
    this._tail = null;
    this._error = null;
    this._hasFailed = false;
    this._observer = null;
    this._conn = null;
    this._meta = {};
  }
  /**
   * Will be called on every record that comes in and transform a raw record
   * to a Object. If user-provided observer is present, pass transformed record
   * to it's onNext method, otherwise, push to record que.
   * @param {Array} rawRecord - An array with the raw record
   */


  (0, _createClass2["default"])(StreamObserver, [{
    key: "onNext",
    value: function onNext(rawRecord) {
      var record = new _record["default"](this._fieldKeys, rawRecord, this._fieldLookup);

      if (this._observer) {
        this._observer.onNext(record);
      } else {
        this._queuedRecords.push(record);
      }
    }
  }, {
    key: "onCompleted",
    value: function onCompleted(meta) {
      if (this._fieldKeys === null) {
        // Stream header, build a name->index field lookup table
        // to be used by records. This is an optimization to make it
        // faster to look up fields in a record by name, rather than by index.
        // Since the records we get back via Bolt are just arrays of values.
        this._fieldKeys = [];
        this._fieldLookup = {};

        if (meta.fields && meta.fields.length > 0) {
          this._fieldKeys = meta.fields;

          for (var i = 0; i < meta.fields.length; i++) {
            this._fieldLookup[meta.fields[i]] = i;
          }
        }
      } else {
        // End of stream
        if (this._observer) {
          this._observer.onCompleted(meta);
        } else {
          this._tail = meta;
        }
      }

      this._copyMetadataOnCompletion(meta);
    }
  }, {
    key: "_copyMetadataOnCompletion",
    value: function _copyMetadataOnCompletion(meta) {
      for (var key in meta) {
        if (meta.hasOwnProperty(key)) {
          this._meta[key] = meta[key];
        }
      }
    }
  }, {
    key: "serverMetadata",
    value: function serverMetadata() {
      var serverMeta = {
        server: this._conn.server
      };
      return Object.assign({}, this._meta, serverMeta);
    }
  }, {
    key: "resolveConnection",
    value: function resolveConnection(conn) {
      this._conn = conn;
    }
    /**
     * Stream observer defaults to handling responses for two messages: RUN + PULL_ALL or RUN + DISCARD_ALL.
     * Response for RUN initializes statement keys. Response for PULL_ALL / DISCARD_ALL exposes the result stream.
     *
     * However, some operations can be represented as a single message which receives full metadata in a single response.
     * For example, operations to begin, commit and rollback an explicit transaction use two messages in Bolt V1 but a single message in Bolt V3.
     * Messages are `RUN "BEGIN" {}` + `PULL_ALL` in Bolt V1 and `BEGIN` in Bolt V3.
     *
     * This function prepares the observer to only handle a single response message.
     */

  }, {
    key: "prepareToHandleSingleResponse",
    value: function prepareToHandleSingleResponse() {
      this._fieldKeys = [];
    }
    /**
     * Mark this observer as if it has completed with no metadata.
     */

  }, {
    key: "markCompleted",
    value: function markCompleted() {
      this._fieldKeys = [];
      this._tail = {};
    }
    /**
     * Will be called on errors.
     * If user-provided observer is present, pass the error
     * to it's onError method, otherwise set instance variable _error.
     * @param {Object} error - An error object
     */

  }, {
    key: "onError",
    value: function onError(error) {
      if (this._hasFailed) {
        return;
      }

      this._hasFailed = true;

      if (this._observer) {
        if (this._observer.onError) {
          this._observer.onError(error);
        } else {
          console.log(error);
        }
      } else {
        this._error = error;
      }
    }
    /**
     * Subscribe to events with provided observer.
     * @param {Object} observer - Observer object
     * @param {function(record: Object)} observer.onNext - Handle records, one by one.
     * @param {function(metadata: Object)} observer.onComplete - Handle stream tail, the metadata.
     * @param {function(error: Object)} observer.onError - Handle errors.
     */

  }, {
    key: "subscribe",
    value: function subscribe(observer) {
      if (this._error) {
        observer.onError(this._error);
        return;
      }

      if (this._queuedRecords.length > 0) {
        for (var i = 0; i < this._queuedRecords.length; i++) {
          observer.onNext(this._queuedRecords[i]);
        }
      }

      if (this._tail) {
        observer.onCompleted(this._tail);
      }

      this._observer = observer;
    }
  }, {
    key: "hasFailed",
    value: function hasFailed() {
      return this._hasFailed;
    }
  }]);
  return StreamObserver;
}();

var _default = StreamObserver;
exports["default"] = _default;

},{"../record":81,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11}],76:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.normalizeSecondsForDuration = normalizeSecondsForDuration;
exports.normalizeNanosecondsForDuration = normalizeNanosecondsForDuration;
exports.localTimeToNanoOfDay = localTimeToNanoOfDay;
exports.nanoOfDayToLocalTime = nanoOfDayToLocalTime;
exports.localDateTimeToEpochSecond = localDateTimeToEpochSecond;
exports.epochSecondAndNanoToLocalDateTime = epochSecondAndNanoToLocalDateTime;
exports.dateToEpochDay = dateToEpochDay;
exports.epochDayToDate = epochDayToDate;
exports.durationToIsoString = durationToIsoString;
exports.timeToIsoString = timeToIsoString;
exports.timeZoneOffsetToIsoString = timeZoneOffsetToIsoString;
exports.dateToIsoString = dateToIsoString;
exports.totalNanoseconds = totalNanoseconds;
exports.timeZoneOffsetInSeconds = timeZoneOffsetInSeconds;
exports.assertValidYear = assertValidYear;
exports.assertValidMonth = assertValidMonth;
exports.assertValidDay = assertValidDay;
exports.assertValidHour = assertValidHour;
exports.assertValidMinute = assertValidMinute;
exports.assertValidSecond = assertValidSecond;
exports.assertValidNanosecond = assertValidNanosecond;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _integer = require("../integer");

var _temporalTypes = require("../temporal-types");

var _util = require("./util");

var _error = require("../error");

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
  Code in this util should be compatible with code in the database that uses JSR-310 java.time APIs.

  It is based on a library called ThreeTen (https://github.com/ThreeTen/threetenbp) which was derived
  from JSR-310 reference implementation previously hosted on GitHub. Code uses `Integer` type everywhere
  to correctly handle large integer values that are greater than `Number.MAX_SAFE_INTEGER`.

  Please consult either ThreeTen or js-joda (https://github.com/js-joda/js-joda) when working with the
  conversion functions.
 */
var ValueRange =
/*#__PURE__*/
function () {
  function ValueRange(min, max) {
    (0, _classCallCheck2["default"])(this, ValueRange);
    this._minNumber = min;
    this._maxNumber = max;
    this._minInteger = (0, _integer["int"])(min);
    this._maxInteger = (0, _integer["int"])(max);
  }

  (0, _createClass2["default"])(ValueRange, [{
    key: "contains",
    value: function contains(value) {
      if ((0, _integer.isInt)(value)) {
        return value.greaterThanOrEqual(this._minInteger) && value.lessThanOrEqual(this._maxInteger);
      } else {
        return value >= this._minNumber && value <= this._maxNumber;
      }
    }
  }, {
    key: "toString",
    value: function toString() {
      return "[".concat(this._minNumber, ", ").concat(this._maxNumber, "]");
    }
  }]);
  return ValueRange;
}();

var YEAR_RANGE = new ValueRange(-999999999, 999999999);
var MONTH_OF_YEAR_RANGE = new ValueRange(1, 12);
var DAY_OF_MONTH_RANGE = new ValueRange(1, 31);
var HOUR_OF_DAY_RANGE = new ValueRange(0, 23);
var MINUTE_OF_HOUR_RANGE = new ValueRange(0, 59);
var SECOND_OF_MINUTE_RANGE = new ValueRange(0, 59);
var NANOSECOND_OF_SECOND_RANGE = new ValueRange(0, 999999999);
var MINUTES_PER_HOUR = 60;
var SECONDS_PER_MINUTE = 60;
var SECONDS_PER_HOUR = SECONDS_PER_MINUTE * MINUTES_PER_HOUR;
var NANOS_PER_SECOND = 1000000000;
var NANOS_PER_MILLISECOND = 1000000;
var NANOS_PER_MINUTE = NANOS_PER_SECOND * SECONDS_PER_MINUTE;
var NANOS_PER_HOUR = NANOS_PER_MINUTE * MINUTES_PER_HOUR;
var DAYS_0000_TO_1970 = 719528;
var DAYS_PER_400_YEAR_CYCLE = 146097;
var SECONDS_PER_DAY = 86400;

function normalizeSecondsForDuration(seconds, nanoseconds) {
  return (0, _integer["int"])(seconds).add(floorDiv(nanoseconds, NANOS_PER_SECOND));
}

function normalizeNanosecondsForDuration(nanoseconds) {
  return floorMod(nanoseconds, NANOS_PER_SECOND);
}
/**
 * Converts given local time into a single integer representing this same time in nanoseconds of the day.
 * @param {Integer|number|string} hour the hour of the local time to convert.
 * @param {Integer|number|string} minute the minute of the local time to convert.
 * @param {Integer|number|string} second the second of the local time to convert.
 * @param {Integer|number|string} nanosecond the nanosecond of the local time to convert.
 * @return {Integer} nanoseconds representing the given local time.
 */


function localTimeToNanoOfDay(hour, minute, second, nanosecond) {
  hour = (0, _integer["int"])(hour);
  minute = (0, _integer["int"])(minute);
  second = (0, _integer["int"])(second);
  nanosecond = (0, _integer["int"])(nanosecond);
  var totalNanos = hour.multiply(NANOS_PER_HOUR);
  totalNanos = totalNanos.add(minute.multiply(NANOS_PER_MINUTE));
  totalNanos = totalNanos.add(second.multiply(NANOS_PER_SECOND));
  return totalNanos.add(nanosecond);
}
/**
 * Converts nanoseconds of the day into local time.
 * @param {Integer|number|string} nanoOfDay the nanoseconds of the day to convert.
 * @return {LocalTime} the local time representing given nanoseconds of the day.
 */


function nanoOfDayToLocalTime(nanoOfDay) {
  nanoOfDay = (0, _integer["int"])(nanoOfDay);
  var hour = nanoOfDay.div(NANOS_PER_HOUR);
  nanoOfDay = nanoOfDay.subtract(hour.multiply(NANOS_PER_HOUR));
  var minute = nanoOfDay.div(NANOS_PER_MINUTE);
  nanoOfDay = nanoOfDay.subtract(minute.multiply(NANOS_PER_MINUTE));
  var second = nanoOfDay.div(NANOS_PER_SECOND);
  var nanosecond = nanoOfDay.subtract(second.multiply(NANOS_PER_SECOND));
  return new _temporalTypes.LocalTime(hour, minute, second, nanosecond);
}
/**
 * Converts given local date time into a single integer representing this same time in epoch seconds UTC.
 * @param {Integer|number|string} year the year of the local date-time to convert.
 * @param {Integer|number|string} month the month of the local date-time to convert.
 * @param {Integer|number|string} day the day of the local date-time to convert.
 * @param {Integer|number|string} hour the hour of the local date-time to convert.
 * @param {Integer|number|string} minute the minute of the local date-time to convert.
 * @param {Integer|number|string} second the second of the local date-time to convert.
 * @param {Integer|number|string} nanosecond the nanosecond of the local date-time to convert.
 * @return {Integer} epoch second in UTC representing the given local date time.
 */


function localDateTimeToEpochSecond(year, month, day, hour, minute, second, nanosecond) {
  var epochDay = dateToEpochDay(year, month, day);
  var localTimeSeconds = localTimeToSecondOfDay(hour, minute, second);
  return epochDay.multiply(SECONDS_PER_DAY).add(localTimeSeconds);
}
/**
 * Converts given epoch second and nanosecond adjustment into a local date time object.
 * @param {Integer|number|string} epochSecond the epoch second to use.
 * @param {Integer|number|string} nano the nanosecond to use.
 * @return {LocalDateTime} the local date time representing given epoch second and nano.
 */


function epochSecondAndNanoToLocalDateTime(epochSecond, nano) {
  var epochDay = floorDiv(epochSecond, SECONDS_PER_DAY);
  var secondsOfDay = floorMod(epochSecond, SECONDS_PER_DAY);
  var nanoOfDay = secondsOfDay.multiply(NANOS_PER_SECOND).add(nano);
  var localDate = epochDayToDate(epochDay);
  var localTime = nanoOfDayToLocalTime(nanoOfDay);
  return new _temporalTypes.LocalDateTime(localDate.year, localDate.month, localDate.day, localTime.hour, localTime.minute, localTime.second, localTime.nanosecond);
}
/**
 * Converts given local date into a single integer representing it's epoch day.
 * @param {Integer|number|string} year the year of the local date to convert.
 * @param {Integer|number|string} month the month of the local date to convert.
 * @param {Integer|number|string} day the day of the local date to convert.
 * @return {Integer} epoch day representing the given date.
 */


function dateToEpochDay(year, month, day) {
  year = (0, _integer["int"])(year);
  month = (0, _integer["int"])(month);
  day = (0, _integer["int"])(day);
  var epochDay = year.multiply(365);

  if (year.greaterThanOrEqual(0)) {
    epochDay = epochDay.add(year.add(3).div(4).subtract(year.add(99).div(100)).add(year.add(399).div(400)));
  } else {
    epochDay = epochDay.subtract(year.div(-4).subtract(year.div(-100)).add(year.div(-400)));
  }

  epochDay = epochDay.add(month.multiply(367).subtract(362).div(12));
  epochDay = epochDay.add(day.subtract(1));

  if (month.greaterThan(2)) {
    epochDay = epochDay.subtract(1);

    if (!isLeapYear(year)) {
      epochDay = epochDay.subtract(1);
    }
  }

  return epochDay.subtract(DAYS_0000_TO_1970);
}
/**
 * Converts given epoch day to a local date.
 * @param {Integer|number|string} epochDay the epoch day to convert.
 * @return {Date} the date representing the epoch day in years, months and days.
 */


function epochDayToDate(epochDay) {
  epochDay = (0, _integer["int"])(epochDay);
  var zeroDay = epochDay.add(DAYS_0000_TO_1970).subtract(60);
  var adjust = (0, _integer["int"])(0);

  if (zeroDay.lessThan(0)) {
    var adjustCycles = zeroDay.add(1).div(DAYS_PER_400_YEAR_CYCLE).subtract(1);
    adjust = adjustCycles.multiply(400);
    zeroDay = zeroDay.add(adjustCycles.multiply(-DAYS_PER_400_YEAR_CYCLE));
  }

  var year = zeroDay.multiply(400).add(591).div(DAYS_PER_400_YEAR_CYCLE);
  var dayOfYearEst = zeroDay.subtract(year.multiply(365).add(year.div(4)).subtract(year.div(100)).add(year.div(400)));

  if (dayOfYearEst.lessThan(0)) {
    year = year.subtract(1);
    dayOfYearEst = zeroDay.subtract(year.multiply(365).add(year.div(4)).subtract(year.div(100)).add(year.div(400)));
  }

  year = year.add(adjust);
  var marchDayOfYear = dayOfYearEst;
  var marchMonth = marchDayOfYear.multiply(5).add(2).div(153);
  var month = marchMonth.add(2).modulo(12).add(1);
  var day = marchDayOfYear.subtract(marchMonth.multiply(306).add(5).div(10)).add(1);
  year = year.add(marchMonth.div(10));
  return new _temporalTypes.Date(year, month, day);
}
/**
 * Format given duration to an ISO 8601 string.
 * @param {Integer|number|string} months the number of months.
 * @param {Integer|number|string} days the number of days.
 * @param {Integer|number|string} seconds the number of seconds.
 * @param {Integer|number|string} nanoseconds the number of nanoseconds.
 * @return {string} ISO string that represents given duration.
 */


function durationToIsoString(months, days, seconds, nanoseconds) {
  var monthsString = formatNumber(months);
  var daysString = formatNumber(days);
  var secondsAndNanosecondsString = formatSecondsAndNanosecondsForDuration(seconds, nanoseconds);
  return "P".concat(monthsString, "M").concat(daysString, "DT").concat(secondsAndNanosecondsString, "S");
}
/**
 * Formats given time to an ISO 8601 string.
 * @param {Integer|number|string} hour the hour value.
 * @param {Integer|number|string} minute the minute value.
 * @param {Integer|number|string} second the second value.
 * @param {Integer|number|string} nanosecond the nanosecond value.
 * @return {string} ISO string that represents given time.
 */


function timeToIsoString(hour, minute, second, nanosecond) {
  var hourString = formatNumber(hour, 2);
  var minuteString = formatNumber(minute, 2);
  var secondString = formatNumber(second, 2);
  var nanosecondString = formatNanosecond(nanosecond);
  return "".concat(hourString, ":").concat(minuteString, ":").concat(secondString).concat(nanosecondString);
}
/**
 * Formats given time zone offset in seconds to string representation like '±HH:MM', '±HH:MM:SS' or 'Z' for UTC.
 * @param {Integer|number|string} offsetSeconds the offset in seconds.
 * @return {string} ISO string that represents given offset.
 */


function timeZoneOffsetToIsoString(offsetSeconds) {
  offsetSeconds = (0, _integer["int"])(offsetSeconds);

  if (offsetSeconds.equals(0)) {
    return 'Z';
  }

  var isNegative = offsetSeconds.isNegative();

  if (isNegative) {
    offsetSeconds = offsetSeconds.multiply(-1);
  }

  var signPrefix = isNegative ? '-' : '+';
  var hours = formatNumber(offsetSeconds.div(SECONDS_PER_HOUR), 2);
  var minutes = formatNumber(offsetSeconds.div(SECONDS_PER_MINUTE).modulo(MINUTES_PER_HOUR), 2);
  var secondsValue = offsetSeconds.modulo(SECONDS_PER_MINUTE);
  var seconds = secondsValue.equals(0) ? null : formatNumber(secondsValue, 2);
  return seconds ? "".concat(signPrefix).concat(hours, ":").concat(minutes, ":").concat(seconds) : "".concat(signPrefix).concat(hours, ":").concat(minutes);
}
/**
 * Formats given date to an ISO 8601 string.
 * @param {Integer|number|string} year the date year.
 * @param {Integer|number|string} month the date month.
 * @param {Integer|number|string} day the date day.
 * @return {string} ISO string that represents given date.
 */


function dateToIsoString(year, month, day) {
  year = (0, _integer["int"])(year);
  var isNegative = year.isNegative();

  if (isNegative) {
    year = year.multiply(-1);
  }

  var yearString = formatNumber(year, 4);

  if (isNegative) {
    yearString = '-' + yearString;
  }

  var monthString = formatNumber(month, 2);
  var dayString = formatNumber(day, 2);
  return "".concat(yearString, "-").concat(monthString, "-").concat(dayString);
}
/**
 * Get the total number of nanoseconds from the milliseconds of the given standard JavaScript date and optional nanosecond part.
 * @param {global.Date} standardDate the standard JavaScript date.
 * @param {Integer|number|undefined} nanoseconds the optional number of nanoseconds.
 * @return {Integer|number} the total amount of nanoseconds.
 */


function totalNanoseconds(standardDate, nanoseconds) {
  nanoseconds = nanoseconds || 0;
  var nanosFromMillis = standardDate.getMilliseconds() * NANOS_PER_MILLISECOND;
  return (0, _integer.isInt)(nanoseconds) ? nanoseconds.add(nanosFromMillis) : nanoseconds + nanosFromMillis;
}
/**
 * Get the time zone offset in seconds from the given standard JavaScript date.
 *
 * <b>Implementation note:</b>
 * Time zone offset returned by the standard JavaScript date is the difference, in minutes, from local time to UTC.
 * So positive value means offset is behind UTC and negative value means it is ahead.
 * For Neo4j temporal types, like `Time` or `DateTime` offset is in seconds and represents difference from UTC to local time.
 * This is different from standard JavaScript dates and that's why implementation negates the returned value.
 *
 * @param {global.Date} standardDate the standard JavaScript date.
 * @return {number} the time zone offset in seconds.
 */


function timeZoneOffsetInSeconds(standardDate) {
  var offsetInMinutes = standardDate.getTimezoneOffset();

  if (offsetInMinutes === 0) {
    return 0;
  }

  return -1 * offsetInMinutes * SECONDS_PER_MINUTE;
}
/**
 * Assert that the year value is valid.
 * @param {Integer|number} year the value to check.
 * @return {Integer|number} the value of the year if it is valid. Exception is thrown otherwise.
 */


function assertValidYear(year) {
  return assertValidTemporalValue(year, YEAR_RANGE, 'Year');
}
/**
 * Assert that the month value is valid.
 * @param {Integer|number} month the value to check.
 * @return {Integer|number} the value of the month if it is valid. Exception is thrown otherwise.
 */


function assertValidMonth(month) {
  return assertValidTemporalValue(month, MONTH_OF_YEAR_RANGE, 'Month');
}
/**
 * Assert that the day value is valid.
 * @param {Integer|number} day the value to check.
 * @return {Integer|number} the value of the day if it is valid. Exception is thrown otherwise.
 */


function assertValidDay(day) {
  return assertValidTemporalValue(day, DAY_OF_MONTH_RANGE, 'Day');
}
/**
 * Assert that the hour value is valid.
 * @param {Integer|number} hour the value to check.
 * @return {Integer|number} the value of the hour if it is valid. Exception is thrown otherwise.
 */


function assertValidHour(hour) {
  return assertValidTemporalValue(hour, HOUR_OF_DAY_RANGE, 'Hour');
}
/**
 * Assert that the minute value is valid.
 * @param {Integer|number} minute the value to check.
 * @return {Integer|number} the value of the minute if it is valid. Exception is thrown otherwise.
 */


function assertValidMinute(minute) {
  return assertValidTemporalValue(minute, MINUTE_OF_HOUR_RANGE, 'Minute');
}
/**
 * Assert that the second value is valid.
 * @param {Integer|number} second the value to check.
 * @return {Integer|number} the value of the second if it is valid. Exception is thrown otherwise.
 */


function assertValidSecond(second) {
  return assertValidTemporalValue(second, SECOND_OF_MINUTE_RANGE, 'Second');
}
/**
 * Assert that the nanosecond value is valid.
 * @param {Integer|number} nanosecond the value to check.
 * @return {Integer|number} the value of the nanosecond if it is valid. Exception is thrown otherwise.
 */


function assertValidNanosecond(nanosecond) {
  return assertValidTemporalValue(nanosecond, NANOSECOND_OF_SECOND_RANGE, 'Nanosecond');
}
/**
 * Check if the given value is of expected type and is in the expected range.
 * @param {Integer|number} value the value to check.
 * @param {ValueRange} range the range.
 * @param {string} name the name of the value.
 * @return {Integer|number} the value if valid. Exception is thrown otherwise.
 */


function assertValidTemporalValue(value, range, name) {
  (0, _util.assertNumberOrInteger)(value, name);

  if (!range.contains(value)) {
    throw (0, _error.newError)("".concat(name, " is expected to be in range ").concat(range, " but was: ").concat(value));
  }

  return value;
}
/**
 * Converts given local time into a single integer representing this same time in seconds of the day. Nanoseconds are skipped.
 * @param {Integer|number|string} hour the hour of the local time.
 * @param {Integer|number|string} minute the minute of the local time.
 * @param {Integer|number|string} second the second of the local time.
 * @return {Integer} seconds representing the given local time.
 */


function localTimeToSecondOfDay(hour, minute, second) {
  hour = (0, _integer["int"])(hour);
  minute = (0, _integer["int"])(minute);
  second = (0, _integer["int"])(second);
  var totalSeconds = hour.multiply(SECONDS_PER_HOUR);
  totalSeconds = totalSeconds.add(minute.multiply(SECONDS_PER_MINUTE));
  return totalSeconds.add(second);
}
/**
 * Check if given year is a leap year. Uses algorithm described here {@link https://en.wikipedia.org/wiki/Leap_year#Algorithm}.
 * @param {Integer|number|string} year the year to check. Will be converted to {@link Integer} for all calculations.
 * @return {boolean} `true` if given year is a leap year, `false` otherwise.
 */


function isLeapYear(year) {
  year = (0, _integer["int"])(year);

  if (!year.modulo(4).equals(0)) {
    return false;
  } else if (!year.modulo(100).equals(0)) {
    return true;
  } else if (!year.modulo(400).equals(0)) {
    return false;
  } else {
    return true;
  }
}
/**
 * @param {Integer|number|string} x the divident.
 * @param {Integer|number|string} y the divisor.
 * @return {Integer} the result.
 */


function floorDiv(x, y) {
  x = (0, _integer["int"])(x);
  y = (0, _integer["int"])(y);
  var result = x.div(y);

  if (x.isPositive() !== y.isPositive() && result.multiply(y).notEquals(x)) {
    result = result.subtract(1);
  }

  return result;
}
/**
 * @param {Integer|number|string} x the divident.
 * @param {Integer|number|string} y the divisor.
 * @return {Integer} the result.
 */


function floorMod(x, y) {
  x = (0, _integer["int"])(x);
  y = (0, _integer["int"])(y);
  return x.subtract(floorDiv(x, y).multiply(y));
}
/**
 * @param {Integer|number|string} seconds the number of seconds to format.
 * @param {Integer|number|string} nanoseconds the number of nanoseconds to format.
 * @return {string} formatted value.
 */


function formatSecondsAndNanosecondsForDuration(seconds, nanoseconds) {
  seconds = (0, _integer["int"])(seconds);
  nanoseconds = (0, _integer["int"])(nanoseconds);
  var secondsString;
  var nanosecondsString;
  var secondsNegative = seconds.isNegative();
  var nanosecondsGreaterThanZero = nanoseconds.greaterThan(0);

  if (secondsNegative && nanosecondsGreaterThanZero) {
    if (seconds.equals(-1)) {
      secondsString = '-0';
    } else {
      secondsString = seconds.add(1).toString();
    }
  } else {
    secondsString = seconds.toString();
  }

  if (nanosecondsGreaterThanZero) {
    if (secondsNegative) {
      nanosecondsString = formatNanosecond(nanoseconds.negate().add(2 * NANOS_PER_SECOND).modulo(NANOS_PER_SECOND));
    } else {
      nanosecondsString = formatNanosecond(nanoseconds.add(NANOS_PER_SECOND).modulo(NANOS_PER_SECOND));
    }
  }

  return nanosecondsString ? secondsString + nanosecondsString : secondsString;
}
/**
 * @param {Integer|number|string} value the number of nanoseconds to format.
 * @return {string} formatted and possibly left-padded nanoseconds part as string.
 */


function formatNanosecond(value) {
  value = (0, _integer["int"])(value);
  return value.equals(0) ? '' : '.' + formatNumber(value, 9);
}
/**
 * @param {Integer|number|string} num the number to format.
 * @param {number} [stringLength=undefined] the string length to left-pad to.
 * @return {string} formatted and possibly left-padded number as string.
 */


function formatNumber(num) {
  var stringLength = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : undefined;
  num = (0, _integer["int"])(num);
  var isNegative = num.isNegative();

  if (isNegative) {
    num = num.negate();
  }

  var numString = num.toString();

  if (stringLength) {
    // left pad the string with zeroes
    while (numString.length < stringLength) {
      numString = '0' + numString;
    }
  }

  return isNegative ? '-' + numString : numString;
}

},{"../error":29,"../integer":32,"../temporal-types":87,"./util":80,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11}],77:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _error = require("../error");

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
var DEFAULT_MAX_RETRY_TIME_MS = 30 * 1000; // 30 seconds

var DEFAULT_INITIAL_RETRY_DELAY_MS = 1000; // 1 seconds

var DEFAULT_RETRY_DELAY_MULTIPLIER = 2.0;
var DEFAULT_RETRY_DELAY_JITTER_FACTOR = 0.2;

var TransactionExecutor =
/*#__PURE__*/
function () {
  function TransactionExecutor(maxRetryTimeMs, initialRetryDelayMs, multiplier, jitterFactor) {
    (0, _classCallCheck2["default"])(this, TransactionExecutor);
    this._maxRetryTimeMs = _valueOrDefault(maxRetryTimeMs, DEFAULT_MAX_RETRY_TIME_MS);
    this._initialRetryDelayMs = _valueOrDefault(initialRetryDelayMs, DEFAULT_INITIAL_RETRY_DELAY_MS);
    this._multiplier = _valueOrDefault(multiplier, DEFAULT_RETRY_DELAY_MULTIPLIER);
    this._jitterFactor = _valueOrDefault(jitterFactor, DEFAULT_RETRY_DELAY_JITTER_FACTOR);
    this._inFlightTimeoutIds = [];

    this._verifyAfterConstruction();
  }

  (0, _createClass2["default"])(TransactionExecutor, [{
    key: "execute",
    value: function execute(transactionCreator, transactionWork) {
      var _this = this;

      return new Promise(function (resolve, reject) {
        _this._executeTransactionInsidePromise(transactionCreator, transactionWork, resolve, reject);
      })["catch"](function (error) {
        var retryStartTimeMs = Date.now();
        var retryDelayMs = _this._initialRetryDelayMs;
        return _this._retryTransactionPromise(transactionCreator, transactionWork, error, retryStartTimeMs, retryDelayMs);
      });
    }
  }, {
    key: "close",
    value: function close() {
      // cancel all existing timeouts to prevent further retries
      this._inFlightTimeoutIds.forEach(function (timeoutId) {
        return clearTimeout(timeoutId);
      });

      this._inFlightTimeoutIds = [];
    }
  }, {
    key: "_retryTransactionPromise",
    value: function _retryTransactionPromise(transactionCreator, transactionWork, error, retryStartTime, retryDelayMs) {
      var _this2 = this;

      var elapsedTimeMs = Date.now() - retryStartTime;

      if (elapsedTimeMs > this._maxRetryTimeMs || !TransactionExecutor._canRetryOn(error)) {
        return Promise.reject(error);
      }

      return new Promise(function (resolve, reject) {
        var nextRetryTime = _this2._computeDelayWithJitter(retryDelayMs);

        var timeoutId = setTimeout(function () {
          // filter out this timeoutId when time has come and function is being executed
          _this2._inFlightTimeoutIds = _this2._inFlightTimeoutIds.filter(function (id) {
            return id !== timeoutId;
          });

          _this2._executeTransactionInsidePromise(transactionCreator, transactionWork, resolve, reject);
        }, nextRetryTime); // add newly created timeoutId to the list of all in-flight timeouts

        _this2._inFlightTimeoutIds.push(timeoutId);
      })["catch"](function (error) {
        var nextRetryDelayMs = retryDelayMs * _this2._multiplier;
        return _this2._retryTransactionPromise(transactionCreator, transactionWork, error, retryStartTime, nextRetryDelayMs);
      });
    }
  }, {
    key: "_executeTransactionInsidePromise",
    value: function _executeTransactionInsidePromise(transactionCreator, transactionWork, resolve, reject) {
      var _this3 = this;

      var tx;

      try {
        tx = transactionCreator();
      } catch (error) {
        // failed to create a transaction
        reject(error);
        return;
      }

      var resultPromise = this._safeExecuteTransactionWork(tx, transactionWork);

      resultPromise.then(function (result) {
        return _this3._handleTransactionWorkSuccess(result, tx, resolve, reject);
      })["catch"](function (error) {
        return _this3._handleTransactionWorkFailure(error, tx, reject);
      });
    }
  }, {
    key: "_safeExecuteTransactionWork",
    value: function _safeExecuteTransactionWork(tx, transactionWork) {
      try {
        var result = transactionWork(tx); // user defined callback is supposed to return a promise, but it might not; so to protect against an
        // incorrect API usage we wrap the returned value with a resolved promise; this is effectively a
        // validation step without type checks

        return Promise.resolve(result);
      } catch (error) {
        return Promise.reject(error);
      }
    }
  }, {
    key: "_handleTransactionWorkSuccess",
    value: function _handleTransactionWorkSuccess(result, tx, resolve, reject) {
      if (tx.isOpen()) {
        // transaction work returned resolved promise and transaction has not been committed/rolled back
        // try to commit the transaction
        tx.commit().then(function () {
          // transaction was committed, return result to the user
          resolve(result);
        })["catch"](function (error) {
          // transaction failed to commit, propagate the failure
          reject(error);
        });
      } else {
        // transaction work returned resolved promise and transaction is already committed/rolled back
        // return the result returned by given transaction work
        resolve(result);
      }
    }
  }, {
    key: "_handleTransactionWorkFailure",
    value: function _handleTransactionWorkFailure(error, tx, reject) {
      if (tx.isOpen()) {
        // transaction work failed and the transaction is still open, roll it back and propagate the failure
        tx.rollback()["catch"](function (ignore) {// ignore the rollback error
        }).then(function () {
          return reject(error);
        }); // propagate the original error we got from the transaction work
      } else {
        // transaction is already rolled back, propagate the error
        reject(error);
      }
    }
  }, {
    key: "_computeDelayWithJitter",
    value: function _computeDelayWithJitter(delayMs) {
      var jitter = delayMs * this._jitterFactor;
      var min = delayMs - jitter;
      var max = delayMs + jitter;
      return Math.random() * (max - min) + min;
    }
  }, {
    key: "_verifyAfterConstruction",
    value: function _verifyAfterConstruction() {
      if (this._maxRetryTimeMs < 0) {
        throw (0, _error.newError)('Max retry time should be >= 0: ' + this._maxRetryTimeMs);
      }

      if (this._initialRetryDelayMs < 0) {
        throw (0, _error.newError)('Initial retry delay should >= 0: ' + this._initialRetryDelayMs);
      }

      if (this._multiplier < 1.0) {
        throw (0, _error.newError)('Multiplier should be >= 1.0: ' + this._multiplier);
      }

      if (this._jitterFactor < 0 || this._jitterFactor > 1) {
        throw (0, _error.newError)('Jitter factor should be in [0.0, 1.0]: ' + this._jitterFactor);
      }
    }
  }], [{
    key: "_canRetryOn",
    value: function _canRetryOn(error) {
      return error && error.code && (error.code === _error.SERVICE_UNAVAILABLE || error.code === _error.SESSION_EXPIRED || this._isTransientError(error));
    }
  }, {
    key: "_isTransientError",
    value: function _isTransientError(error) {
      // Retries should not happen when transaction was explicitly terminated by the user.
      // Termination of transaction might result in two different error codes depending on where it was
      // terminated. These are really client errors but classification on the server is not entirely correct and
      // they are classified as transient.
      var code = error.code;

      if (code.indexOf('TransientError') >= 0) {
        if (code === 'Neo.TransientError.Transaction.Terminated' || code === 'Neo.TransientError.Transaction.LockClientStopped') {
          return false;
        }

        return true;
      }

      return false;
    }
  }]);
  return TransactionExecutor;
}();

exports["default"] = TransactionExecutor;

function _valueOrDefault(value, defaultValue) {
  if (value || value === 0) {
    return value;
  }

  return defaultValue;
}

},{"../error":29,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11}],78:[function(require,module,exports){
"use strict";

var _interopRequireWildcard = require("@babel/runtime/helpers/interopRequireWildcard");

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var util = _interopRequireWildcard(require("./util"));

var _integer = require("../integer");

var _error = require("../error");

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
 * Internal holder of the transaction configuration.
 * It performs input validation and value conversion for further serialization by the Bolt protocol layer.
 * Users of the driver provide transaction configuration as regular objects `{timeout: 10, metadata: {key: 'value'}}`.
 * Driver converts such objects to {@link TxConfig} immediately and uses converted values everywhere.
 */
var TxConfig =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {object} config the raw configuration object.
   */
  function TxConfig(config) {
    (0, _classCallCheck2["default"])(this, TxConfig);
    assertValidConfig(config);
    this.timeout = extractTimeout(config);
    this.metadata = extractMetadata(config);
  }
  /**
   * Get an empty config object.
   * @return {TxConfig} an empty config.
   */


  (0, _createClass2["default"])(TxConfig, [{
    key: "isEmpty",

    /**
     * Check if this config object is empty. I.e. has no configuration values specified.
     * @return {boolean} `true` if this object is empty, `false` otherwise.
     */
    value: function isEmpty() {
      return Object.values(this).every(function (value) {
        return value == null;
      });
    }
  }], [{
    key: "empty",
    value: function empty() {
      return EMPTY_CONFIG;
    }
  }]);
  return TxConfig;
}();

exports["default"] = TxConfig;
var EMPTY_CONFIG = new TxConfig({});
/**
 * @return {Integer|null}
 */

function extractTimeout(config) {
  if (util.isObject(config) && (config.timeout || config.timeout === 0)) {
    util.assertNumberOrInteger(config.timeout, 'Transaction timeout');
    var timeout = (0, _integer["int"])(config.timeout);

    if (timeout.isZero()) {
      throw (0, _error.newError)('Transaction timeout should not be zero');
    }

    if (timeout.isNegative()) {
      throw (0, _error.newError)('Transaction timeout should not be negative');
    }

    return timeout;
  }

  return null;
}
/**
 * @return {object|null}
 */


function extractMetadata(config) {
  if (util.isObject(config) && config.metadata) {
    var metadata = config.metadata;
    util.assertObject(metadata);

    if (Object.keys(metadata).length !== 0) {
      // not an empty object
      return metadata;
    }
  }

  return null;
}

function assertValidConfig(config) {
  if (config) {
    util.assertObject(config, 'Transaction config');
  }
}

},{"../error":29,"../integer":32,"./util":80,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/interopRequireWildcard":12}],79:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _uriJs = require("uri-js");

var _util = require("./util");

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
var DEFAULT_BOLT_PORT = 7687;
var DEFAULT_HTTP_PORT = 7474;
var DEFAULT_HTTPS_PORT = 7473;

var Url = function Url(scheme, host, port, hostAndPort, query) {
  (0, _classCallCheck2["default"])(this, Url);

  /**
   * Nullable scheme (protocol) of the URL.
   * Example: 'bolt', 'bolt+routing', 'http', 'https', etc.
   * @type {string}
   */
  this.scheme = scheme;
  /**
   * Nonnull host name or IP address. IPv6 not wrapped in square brackets.
   * Example: 'neo4j.com', 'localhost', '127.0.0.1', '192.168.10.15', '::1', '2001:4860:4860::8844', etc.
   * @type {string}
   */

  this.host = host;
  /**
   * Nonnull number representing port. Default port for the given scheme is used if given URL string
   * does not contain port. Example: 7687 for bolt, 7474 for HTTP and 7473 for HTTPS.
   * @type {number}
   */

  this.port = port;
  /**
   * Nonnull host name or IP address plus port, separated by ':'. IPv6 wrapped in square brackets.
   * Example: 'neo4j.com', 'neo4j.com:7687', '127.0.0.1', '127.0.0.1:8080', '[2001:4860:4860::8844]',
   * '[2001:4860:4860::8844]:9090', etc.
   * @type {string}
   */

  this.hostAndPort = hostAndPort;
  /**
   * Nonnull object representing parsed query string key-value pairs. Duplicated keys not supported.
   * Example: '{}', '{'key1': 'value1', 'key2': 'value2'}', etc.
   * @type {object}
   */

  this.query = query;
};

function parseDatabaseUrl(url) {
  (0, _util.assertString)(url, 'URL');
  var sanitized = sanitizeUrl(url);
  var parsedUrl = (0, _uriJs.parse)(sanitized.url);
  var scheme = sanitized.schemeMissing ? null : extractScheme(parsedUrl.scheme);
  var host = extractHost(parsedUrl.host); // no square brackets for IPv6

  var formattedHost = formatHost(host); // has square brackets for IPv6

  var port = extractPort(parsedUrl.port, scheme);
  var hostAndPort = "".concat(formattedHost, ":").concat(port);
  var query = extractQuery(parsedUrl.query, url);
  return new Url(scheme, host, port, hostAndPort, query);
}

function sanitizeUrl(url) {
  url = url.trim();

  if (url.indexOf('://') === -1) {
    // url does not contain scheme, add dummy 'none://' to make parser work correctly
    return {
      schemeMissing: true,
      url: "none://".concat(url)
    };
  }

  return {
    schemeMissing: false,
    url: url
  };
}

function extractScheme(scheme) {
  if (scheme) {
    scheme = scheme.trim();

    if (scheme.charAt(scheme.length - 1) === ':') {
      scheme = scheme.substring(0, scheme.length - 1);
    }

    return scheme;
  }

  return null;
}

function extractHost(host, url) {
  if (!host) {
    throw new Error("Unable to extract host from ".concat(url));
  }

  return host.trim();
}

function extractPort(portString, scheme) {
  var port = parseInt(portString, 10);
  return port === 0 || port ? port : defaultPortForScheme(scheme);
}

function extractQuery(queryString, url) {
  var query = trimAndSanitizeQuery(queryString);
  var context = {};

  if (query) {
    query.split('&').forEach(function (pair) {
      var keyValue = pair.split('=');

      if (keyValue.length !== 2) {
        throw new Error("Invalid parameters: '".concat(keyValue, "' in URL '").concat(url, "'."));
      }

      var key = trimAndVerifyQueryElement(keyValue[0], 'key', url);
      var value = trimAndVerifyQueryElement(keyValue[1], 'value', url);

      if (context[key]) {
        throw new Error("Duplicated query parameters with key '".concat(key, "' in URL '").concat(url, "'"));
      }

      context[key] = value;
    });
  }

  return context;
}

function trimAndSanitizeQuery(query) {
  query = (query || '').trim();

  if (query && query.charAt(0) === '?') {
    query = query.substring(1, query.length);
  }

  return query;
}

function trimAndVerifyQueryElement(element, name, url) {
  element = (element || '').trim();

  if (!element) {
    throw new Error("Illegal empty ".concat(name, " in URL query '").concat(url, "'"));
  }

  return element;
}

function escapeIPv6Address(address) {
  var startsWithSquareBracket = address.charAt(0) === '[';
  var endsWithSquareBracket = address.charAt(address.length - 1) === ']';

  if (!startsWithSquareBracket && !endsWithSquareBracket) {
    return "[".concat(address, "]");
  } else if (startsWithSquareBracket && endsWithSquareBracket) {
    return address;
  } else {
    throw new Error("Illegal IPv6 address ".concat(address));
  }
}

function formatHost(host) {
  if (!host) {
    throw new Error("Illegal host ".concat(host));
  }

  var isIPv6Address = host.indexOf(':') >= 0;
  return isIPv6Address ? escapeIPv6Address(host) : host;
}

function formatIPv4Address(address, port) {
  return "".concat(address, ":").concat(port);
}

function formatIPv6Address(address, port) {
  var escapedAddress = escapeIPv6Address(address);
  return "".concat(escapedAddress, ":").concat(port);
}

function defaultPortForScheme(scheme) {
  if (scheme === 'http') {
    return DEFAULT_HTTP_PORT;
  } else if (scheme === 'https') {
    return DEFAULT_HTTPS_PORT;
  } else {
    return DEFAULT_BOLT_PORT;
  }
}

var _default = {
  parseDatabaseUrl: parseDatabaseUrl,
  defaultPortForScheme: defaultPortForScheme,
  formatIPv4Address: formatIPv4Address,
  formatIPv6Address: formatIPv6Address
};
exports["default"] = _default;

},{"./util":80,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/interopRequireDefault":11,"uri-js":26}],80:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.isEmptyObjectOrNull = isEmptyObjectOrNull;
exports.isObject = isObject;
exports.isString = isString;
exports.assertObject = assertObject;
exports.assertString = assertString;
exports.assertNumber = assertNumber;
exports.assertNumberOrInteger = assertNumberOrInteger;
exports.assertValidDate = assertValidDate;
exports.validateStatementAndParameters = validateStatementAndParameters;
exports.ENCRYPTION_OFF = exports.ENCRYPTION_ON = void 0;

var _typeof2 = _interopRequireDefault(require("@babel/runtime/helpers/typeof"));

var _integer = require("../integer");

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
var ENCRYPTION_ON = 'ENCRYPTION_ON';
exports.ENCRYPTION_ON = ENCRYPTION_ON;
var ENCRYPTION_OFF = 'ENCRYPTION_OFF';
exports.ENCRYPTION_OFF = ENCRYPTION_OFF;

function isEmptyObjectOrNull(obj) {
  if (obj === null) {
    return true;
  }

  if (!isObject(obj)) {
    return false;
  }

  for (var prop in obj) {
    if (obj.hasOwnProperty(prop)) {
      return false;
    }
  }

  return true;
}

function isObject(obj) {
  return (0, _typeof2["default"])(obj) === 'object' && !Array.isArray(obj) && obj !== null;
}
/**
 * Check and normalize given statement and parameters.
 * @param {string|{text: string, parameters: object}} statement the statement to check.
 * @param {object} parameters
 * @return {{query: string, params: object}} the normalized query with parameters.
 * @throws TypeError when either given query or parameters are invalid.
 */


function validateStatementAndParameters(statement, parameters) {
  var query = statement;
  var params = parameters || {};

  if ((0, _typeof2["default"])(statement) === 'object' && statement.text) {
    query = statement.text;
    params = statement.parameters || {};
  }

  assertCypherStatement(query);
  assertQueryParameters(params);
  return {
    query: query,
    params: params
  };
}

function assertObject(obj, objName) {
  if (!isObject(obj)) {
    throw new TypeError(objName + ' expected to be an object but was: ' + JSON.stringify(obj));
  }

  return obj;
}

function assertString(obj, objName) {
  if (!isString(obj)) {
    throw new TypeError(objName + ' expected to be string but was: ' + JSON.stringify(obj));
  }

  return obj;
}

function assertNumber(obj, objName) {
  if (typeof obj !== 'number') {
    throw new TypeError(objName + ' expected to be a number but was: ' + JSON.stringify(obj));
  }

  return obj;
}

function assertNumberOrInteger(obj, objName) {
  if (typeof obj !== 'number' && !(0, _integer.isInt)(obj)) {
    throw new TypeError(objName + ' expected to be either a number or an Integer object but was: ' + JSON.stringify(obj));
  }

  return obj;
}

function assertValidDate(obj, objName) {
  if (Object.prototype.toString.call(obj) !== '[object Date]') {
    throw new TypeError(objName + ' expected to be a standard JavaScript Date but was: ' + JSON.stringify(obj));
  }

  if (Number.isNaN(obj.getTime())) {
    throw new TypeError(objName + ' expected to be valid JavaScript Date but its time was NaN: ' + JSON.stringify(obj));
  }

  return obj;
}

function assertCypherStatement(obj) {
  assertString(obj, 'Cypher statement');

  if (obj.trim().length === 0) {
    throw new TypeError('Cypher statement is expected to be a non-empty string.');
  }
}

function assertQueryParameters(obj) {
  if (!isObject(obj)) {
    // objects created with `Object.create(null)` do not have a constructor property
    var _constructor = obj.constructor ? ' ' + obj.constructor.name : '';

    throw new TypeError("Query parameters are expected to either be undefined/null or an object, given:".concat(_constructor, " ").concat(obj));
  }
}

function isString(str) {
  return Object.prototype.toString.call(str) === '[object String]';
}

},{"../integer":32,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/typeof":23}],81:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _error = require("./error");

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
function generateFieldLookup(keys) {
  var lookup = {};
  keys.forEach(function (name, idx) {
    lookup[name] = idx;
  });
  return lookup;
}
/**
 * Records make up the contents of the {@link Result}, and is how you access
 * the output of a statement. A simple statement might yield a result stream
 * with a single record, for instance:
 *
 *     MATCH (u:User) RETURN u.name, u.age
 *
 * This returns a stream of records with two fields, named `u.name` and `u.age`,
 * each record represents one user found by the statement above. You can access
 * the values of each field either by name:
 *
 *     record.get("u.name")
 *
 * Or by it's position:
 *
 *     record.get(0)
 *
 * @access public
 */


var Record =
/*#__PURE__*/
function () {
  /**
   * Create a new record object.
   * @constructor
   * @access private
   * @param {string[]} keys An array of field keys, in the order the fields appear in the record
   * @param {Array} fields An array of field values
   * @param {Object} fieldLookup An object of fieldName -> value index, used to map
   *                            field names to values. If this is null, one will be
   *                            generated.
   */
  function Record(keys, fields) {
    var fieldLookup = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
    (0, _classCallCheck2["default"])(this, Record);
    this.keys = keys;
    this.length = keys.length;
    this._fields = fields;
    this._fieldLookup = fieldLookup || generateFieldLookup(keys);
  }
  /**
   * Run the given function for each field in this record. The function
   * will get three arguments - the value, the key and this record, in that
   * order.
   *
   * @param {function(value: Object, key: string, record: Record)} visitor the function to apply to each field.
   */


  (0, _createClass2["default"])(Record, [{
    key: "forEach",
    value: function forEach(visitor) {
      for (var i = 0; i < this.keys.length; i++) {
        visitor(this._fields[i], this.keys[i], this);
      }
    }
    /**
     * Generates an object out of the current Record
     *
     * @returns {Object}
     */

  }, {
    key: "toObject",
    value: function toObject() {
      var object = {};
      this.forEach(function (value, key) {
        object[key] = value;
      });
      return object;
    }
    /**
     * Get a value from this record, either by index or by field key.
     *
     * @param {string|Number} key Field key, or the index of the field.
     * @returns {*}
     */

  }, {
    key: "get",
    value: function get(key) {
      var index;

      if (!(typeof key === 'number')) {
        index = this._fieldLookup[key];

        if (index === undefined) {
          throw (0, _error.newError)("This record has no field with key '" + key + "', available key are: [" + this.keys + '].');
        }
      } else {
        index = key;
      }

      if (index > this._fields.length - 1 || index < 0) {
        throw (0, _error.newError)("This record has no field with index '" + index + "'. Remember that indexes start at `0`, " + 'and make sure your statement returns records in the shape you meant it to.');
      }

      return this._fields[index];
    }
    /**
     * Check if a value from this record, either by index or by field key, exists.
     *
     * @param {string|Number} key Field key, or the index of the field.
     * @returns {boolean}
     */

  }, {
    key: "has",
    value: function has(key) {
      // if key is a number, we check if it is in the _fields array
      if (typeof key === 'number') {
        return key >= 0 && key < this._fields.length;
      } // if it's not a number, we check _fieldLookup dictionary directly


      return this._fieldLookup[key] !== undefined;
    }
  }]);
  return Record;
}();

var _default = Record;
exports["default"] = _default;

},{"./error":29,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11}],82:[function(require,module,exports){
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

},{"./integer":32,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11}],83:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _resultSummary = _interopRequireDefault(require("./result-summary"));

var _connectionHolder = require("./internal/connection-holder");

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
var DEFAULT_ON_ERROR = function DEFAULT_ON_ERROR(error) {
  console.log('Uncaught error when processing result: ' + error);
};

var DEFAULT_ON_COMPLETED = function DEFAULT_ON_COMPLETED(summary) {};
/**
 * A stream of {@link Record} representing the result of a statement.
 * Can be consumed eagerly as {@link Promise} resolved with array of records and {@link ResultSummary}
 * summary, or rejected with error that contains {@link string} code and {@link string} message.
 * Alternatively can be consumed lazily using {@link Result#subscribe} function.
 * @access public
 */


var Result =
/*#__PURE__*/
function () {
  /**
   * Inject the observer to be used.
   * @constructor
   * @access private
   * @param {StreamObserver} streamObserver
   * @param {mixed} statement - Cypher statement to execute
   * @param {Object} parameters - Map with parameters to use in statement
   * @param metaSupplier function, when called provides metadata
   * @param {ConnectionHolder} connectionHolder - to be notified when result is either fully consumed or error happened.
   */
  function Result(streamObserver, statement, parameters, metaSupplier, connectionHolder) {
    (0, _classCallCheck2["default"])(this, Result);
    this._stack = captureStacktrace();
    this._streamObserver = streamObserver;
    this._p = null;
    this._statement = statement;
    this._parameters = parameters || {};

    this._metaSupplier = metaSupplier || function () {
      return {};
    };

    this._connectionHolder = connectionHolder || _connectionHolder.EMPTY_CONNECTION_HOLDER;
  }
  /**
   * Create and return new Promise
   * @return {Promise} new Promise.
   * @access private
   */


  (0, _createClass2["default"])(Result, [{
    key: "_createPromise",
    value: function _createPromise() {
      if (this._p) {
        return;
      }

      var self = this;
      this._p = new Promise(function (resolve, reject) {
        var records = [];
        var observer = {
          onNext: function onNext(record) {
            records.push(record);
          },
          onCompleted: function onCompleted(summary) {
            resolve({
              records: records,
              summary: summary
            });
          },
          onError: function onError(error) {
            reject(error);
          }
        };
        self.subscribe(observer);
      });
    }
    /**
     * Waits for all results and calls the passed in function with the results.
     * Cannot be combined with the {@link Result#subscribe} function.
     *
     * @param {function(result: {records:Array<Record>, summary: ResultSummary})} onFulfilled - function to be called
     * when finished.
     * @param {function(error: {message:string, code:string})} onRejected - function to be called upon errors.
     * @return {Promise} promise.
     */

  }, {
    key: "then",
    value: function then(onFulfilled, onRejected) {
      this._createPromise();

      return this._p.then(onFulfilled, onRejected);
    }
    /**
     * Catch errors when using promises.
     * Cannot be used with the subscribe function.
     * @param {function(error: Neo4jError)} onRejected - Function to be called upon errors.
     * @return {Promise} promise.
     */

  }, {
    key: "catch",
    value: function _catch(onRejected) {
      this._createPromise();

      return this._p["catch"](onRejected);
    }
    /**
     * Stream records to observer as they come in, this is a more efficient method
     * of handling the results, and allows you to handle arbitrarily large results.
     *
     * @param {Object} observer - Observer object
     * @param {function(record: Record)} observer.onNext - handle records, one by one.
     * @param {function(summary: ResultSummary)} observer.onCompleted - handle stream tail, the result summary.
     * @param {function(error: {message:string, code:string})} observer.onError - handle errors.
     * @return
     */

  }, {
    key: "subscribe",
    value: function subscribe(observer) {
      var _this = this;

      var self = this;
      var onCompletedOriginal = observer.onCompleted || DEFAULT_ON_COMPLETED;

      var onCompletedWrapper = function onCompletedWrapper(metadata) {
        var additionalMeta = self._metaSupplier();

        for (var key in additionalMeta) {
          if (additionalMeta.hasOwnProperty(key)) {
            metadata[key] = additionalMeta[key];
          }
        }

        var sum = new _resultSummary["default"](_this._statement, _this._parameters, metadata); // notify connection holder that the used connection is not needed any more because result has
        // been fully consumed; call the original onCompleted callback after that

        self._connectionHolder.releaseConnection().then(function () {
          onCompletedOriginal.call(observer, sum);
        });
      };

      observer.onCompleted = onCompletedWrapper;
      var onErrorOriginal = observer.onError || DEFAULT_ON_ERROR;

      var onErrorWrapper = function onErrorWrapper(error) {
        // notify connection holder that the used connection is not needed any more because error happened
        // and result can't bee consumed any further; call the original onError callback after that
        self._connectionHolder.releaseConnection().then(function () {
          replaceStacktrace(error, _this._stack);
          onErrorOriginal.call(observer, error);
        });
      };

      observer.onError = onErrorWrapper;

      this._streamObserver.subscribe(observer);
    }
  }]);
  return Result;
}();

function captureStacktrace() {
  var error = new Error('');

  if (error.stack) {
    return error.stack.replace(/^Error(\n\r)*/, ''); // we don't need the 'Error\n' part, if only it exists
  }

  return null;
}

function replaceStacktrace(error, newStack) {
  if (newStack) {
    // Error.prototype.toString() concatenates error.name and error.message nicely
    // then we add the rest of the stack trace
    error.stack = error.toString() + '\n' + newStack;
  }
}

var _default = Result;
exports["default"] = _default;

},{"./internal/connection-holder":47,"./result-summary":82,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11}],84:[function(require,module,exports){
"use strict";

var _interopRequireWildcard = require("@babel/runtime/helpers/interopRequireWildcard");

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

var _driver = require("./driver");

var _error = require("./error");

var _connectionProviders = require("./internal/connection-providers");

var _leastConnectedLoadBalancingStrategy = _interopRequireWildcard(require("./internal/least-connected-load-balancing-strategy"));

var _roundRobinLoadBalancingStrategy = _interopRequireWildcard(require("./internal/round-robin-load-balancing-strategy"));

var _connectionErrorHandler = _interopRequireDefault(require("./internal/connection-error-handler"));

var _configuredCustomResolver = _interopRequireDefault(require("./internal/resolver/configured-custom-resolver"));

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
 * A driver that supports routing in a causal cluster.
 * @private
 */
var RoutingDriver =
/*#__PURE__*/
function (_Driver) {
  (0, _inherits2["default"])(RoutingDriver, _Driver);

  function RoutingDriver(address, routingContext, userAgent) {
    var _this;

    var token = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : {};
    var config = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : {};
    (0, _classCallCheck2["default"])(this, RoutingDriver);
    _this = (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(RoutingDriver).call(this, address, userAgent, token, validateConfig(config)));
    _this._routingContext = routingContext;
    return _this;
  }

  (0, _createClass2["default"])(RoutingDriver, [{
    key: "_afterConstruction",
    value: function _afterConstruction() {
      this._log.info("Routing driver ".concat(this._id, " created for server address ").concat(this._address));
    }
  }, {
    key: "_createConnectionProvider",
    value: function _createConnectionProvider(address, connectionPool, driverOnErrorCallback) {
      var loadBalancingStrategy = RoutingDriver._createLoadBalancingStrategy(this._config, connectionPool);

      var resolver = createHostNameResolver(this._config);
      return new _connectionProviders.LoadBalancer(address, this._routingContext, connectionPool, loadBalancingStrategy, resolver, driverOnErrorCallback, this._log);
    }
  }, {
    key: "_createConnectionErrorHandler",
    value: function _createConnectionErrorHandler() {
      var _this2 = this;

      // connection errors mean SERVICE_UNAVAILABLE for direct driver but for routing driver they should only
      // result in SESSION_EXPIRED because there might still exist other servers capable of serving the request
      return new _connectionErrorHandler["default"](_error.SESSION_EXPIRED, function (error, address) {
        return _this2._handleUnavailability(error, address);
      }, function (error, address) {
        return _this2._handleWriteFailure(error, address);
      });
    }
  }, {
    key: "_handleUnavailability",
    value: function _handleUnavailability(error, address) {
      this._log.warn("Routing driver ".concat(this._id, " will forget ").concat(address, " because of an error ").concat(error.code, " '").concat(error.message, "'"));

      this._connectionProvider.forget(address);

      return error;
    }
  }, {
    key: "_handleWriteFailure",
    value: function _handleWriteFailure(error, address) {
      this._log.warn("Routing driver ".concat(this._id, " will forget writer ").concat(address, " because of an error ").concat(error.code, " '").concat(error.message, "'"));

      this._connectionProvider.forgetWriter(address);

      return (0, _error.newError)('No longer possible to write to server at ' + address, _error.SESSION_EXPIRED);
    }
    /**
     * Create new load balancing strategy based on the config.
     * @param {object} config the user provided config.
     * @param {Pool} connectionPool the connection pool for this driver.
     * @return {LoadBalancingStrategy} new strategy.
     * @private
     */

  }], [{
    key: "_createLoadBalancingStrategy",
    value: function _createLoadBalancingStrategy(config, connectionPool) {
      var configuredValue = config.loadBalancingStrategy;

      if (!configuredValue || configuredValue === _leastConnectedLoadBalancingStrategy.LEAST_CONNECTED_STRATEGY_NAME) {
        return new _leastConnectedLoadBalancingStrategy["default"](connectionPool);
      } else if (configuredValue === _roundRobinLoadBalancingStrategy.ROUND_ROBIN_STRATEGY_NAME) {
        return new _roundRobinLoadBalancingStrategy["default"]();
      } else {
        throw (0, _error.newError)('Unknown load balancing strategy: ' + configuredValue);
      }
    }
  }]);
  return RoutingDriver;
}(_driver.Driver);
/**
 * @private
 * @returns {ConfiguredCustomResolver} new custom resolver that wraps the passed-in resolver function.
 *              If resolved function is not specified, it defaults to an identity resolver.
 */


function createHostNameResolver(config) {
  return new _configuredCustomResolver["default"](config.resolver);
}
/**
 * @private
 * @returns {object} the given config.
 */


function validateConfig(config) {
  if (config.trust === 'TRUST_ON_FIRST_USE') {
    throw (0, _error.newError)('The chosen trust mode is not compatible with a routing driver');
  }

  var resolver = config.resolver;

  if (resolver && typeof resolver !== 'function') {
    throw new TypeError("Configured resolver should be a function. Got: ".concat(resolver));
  }

  return config;
}

var _default = RoutingDriver;
exports["default"] = _default;

},{"./driver":28,"./error":29,"./internal/connection-error-handler":46,"./internal/connection-providers":48,"./internal/least-connected-load-balancing-strategy":57,"./internal/resolver/configured-custom-resolver":68,"./internal/round-robin-load-balancing-strategy":70,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/getPrototypeOf":9,"@babel/runtime/helpers/inherits":10,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/interopRequireWildcard":12,"@babel/runtime/helpers/possibleConstructorReturn":18}],85:[function(require,module,exports){
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

var _transaction = _interopRequireDefault(require("./transaction"));

var _error = require("./error");

var _util = require("./internal/util");

var _connectionHolder = _interopRequireDefault(require("./internal/connection-holder"));

var _driver = _interopRequireDefault(require("./driver"));

var _constants = require("./internal/constants");

var _transactionExecutor = _interopRequireDefault(require("./internal/transaction-executor"));

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
// Typedef for JSDoc. Declares TransactionConfig type and makes it possible to use in in method-level docs.

/**
 * Configuration object containing settings for explicit and auto-commit transactions.
 * <p>
 * Configuration is supported for:
 * <ul>
 *   <li>queries executed in auto-commit transactions using {@link Session#run}</li>
 *   <li>transactions started by transaction functions using {@link Session#readTransaction} and {@link Session#writeTransaction}</li>
 *   <li>explicit transactions using {@link Session#beginTransaction}</li>
 * </ul>
 * @typedef {object} TransactionConfig
 * @property {number} timeout - the transaction timeout in **milliseconds**. Transactions that execute longer than the configured timeout will
 * be terminated by the database. This functionality allows to limit query/transaction execution time. Specified timeout overrides the default timeout
 * configured in the database using `dbms.transaction.timeout` setting. Value should not represent a duration of zero or negative duration.
 * @property {object} metadata - the transaction metadata. Specified metadata will be attached to the executing transaction and visible in the output of
 * `dbms.listQueries` and `dbms.listTransactions` procedures. It will also get logged to the `query.log`. This functionality makes it easier to tag
 * transactions and is equivalent to `dbms.setTXMetaData` procedure.
 */

/**
 * A Session instance is used for handling the connection and
 * sending statements through the connection.
 * In a single session, multiple queries will be executed serially.
 * In order to execute parallel queries, multiple sessions are required.
 * @access public
 */
var Session =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {string} mode the default access mode for this session.
   * @param {ConnectionProvider} connectionProvider - the connection provider to acquire connections from.
   * @param {Bookmark} bookmark - the initial bookmark for this session.
   * @param {Object} [config={}] - this driver configuration.
   */
  function Session(mode, connectionProvider, bookmark, config) {
    (0, _classCallCheck2["default"])(this, Session);
    this._mode = mode;
    this._readConnectionHolder = new _connectionHolder["default"](_constants.ACCESS_MODE_READ, connectionProvider);
    this._writeConnectionHolder = new _connectionHolder["default"](_constants.ACCESS_MODE_WRITE, connectionProvider);
    this._open = true;
    this._hasTx = false;
    this._lastBookmark = bookmark;
    this._transactionExecutor = _createTransactionExecutor(config);
  }
  /**
   * Run Cypher statement
   * Could be called with a statement object i.e.: `{text: "MATCH ...", parameters: {param: 1}}`
   * or with the statement and parameters as separate arguments.
   * @param {mixed} statement - Cypher statement to execute
   * @param {Object} parameters - Map with parameters to use in statement
   * @param {TransactionConfig} [transactionConfig] - configuration for the new auto-commit transaction.
   * @return {Result} - New Result
   */


  (0, _createClass2["default"])(Session, [{
    key: "run",
    value: function run(statement, parameters, transactionConfig) {
      var _this = this;

      var _validateStatementAnd = (0, _util.validateStatementAndParameters)(statement, parameters),
          query = _validateStatementAnd.query,
          params = _validateStatementAnd.params;

      var autoCommitTxConfig = transactionConfig ? new _txConfig["default"](transactionConfig) : _txConfig["default"].empty();
      return this._run(query, params, function (connection, streamObserver) {
        return connection.protocol().run(query, params, _this._lastBookmark, autoCommitTxConfig, _this._mode, streamObserver);
      });
    }
  }, {
    key: "_run",
    value: function _run(statement, parameters, statementRunner) {
      var streamObserver = new SessionStreamObserver(this);

      var connectionHolder = this._connectionHolderWithMode(this._mode);

      if (!this._hasTx) {
        connectionHolder.initializeConnection();
        connectionHolder.getConnection(streamObserver).then(function (connection) {
          return statementRunner(connection, streamObserver);
        })["catch"](function (error) {
          return streamObserver.onError(error);
        });
      } else {
        streamObserver.onError((0, _error.newError)('Statements cannot be run directly on a ' + 'session with an open transaction; either run from within the ' + 'transaction or use a different session.'));
      }

      return new _result["default"](streamObserver, statement, parameters, function () {
        return streamObserver.serverMetadata();
      }, connectionHolder);
    }
    /**
     * Begin a new transaction in this session. A session can have at most one transaction running at a time, if you
     * want to run multiple concurrent transactions, you should use multiple concurrent sessions.
     *
     * While a transaction is open the session cannot be used to run statements outside the transaction.
     *
     * @param {TransactionConfig} [transactionConfig] - configuration for the new auto-commit transaction.
     * @returns {Transaction} - New Transaction
     */

  }, {
    key: "beginTransaction",
    value: function beginTransaction(transactionConfig) {
      // this function needs to support bookmarks parameter for backwards compatibility
      // parameter was of type {string|string[]} and represented either a single or multiple bookmarks
      // that's why we need to check parameter type and decide how to interpret the value
      var arg = transactionConfig;

      var txConfig = _txConfig["default"].empty();

      if (typeof arg === 'string' || arg instanceof String || Array.isArray(arg)) {
        // argument looks like a single or multiple bookmarks
        // bookmarks in this function are deprecated but need to be supported for backwards compatibility
        this._updateBookmark(new _bookmark["default"](arg));
      } else if (arg) {
        // argument is probably a transaction configuration
        txConfig = new _txConfig["default"](arg);
      }

      return this._beginTransaction(this._mode, txConfig);
    }
  }, {
    key: "_beginTransaction",
    value: function _beginTransaction(accessMode, txConfig) {
      if (this._hasTx) {
        throw (0, _error.newError)('You cannot begin a transaction on a session with an open transaction; ' + 'either run from within the transaction or use a different session.');
      }

      var mode = _driver["default"]._validateSessionMode(accessMode);

      var connectionHolder = this._connectionHolderWithMode(mode);

      connectionHolder.initializeConnection();
      this._hasTx = true;
      var tx = new _transaction["default"](connectionHolder, this._transactionClosed.bind(this), this._updateBookmark.bind(this));

      tx._begin(this._lastBookmark, txConfig);

      return tx;
    }
  }, {
    key: "_transactionClosed",
    value: function _transactionClosed() {
      this._hasTx = false;
    }
    /**
     * Return the bookmark received following the last completed {@link Transaction}.
     *
     * @return {string|null} a reference to a previous transaction
     */

  }, {
    key: "lastBookmark",
    value: function lastBookmark() {
      return this._lastBookmark.maxBookmarkAsString();
    }
    /**
     * Execute given unit of work in a {@link READ} transaction.
     *
     * Transaction will automatically be committed unless the given function throws or returns a rejected promise.
     * Some failures of the given function or the commit itself will be retried with exponential backoff with initial
     * delay of 1 second and maximum retry time of 30 seconds. Maximum retry time is configurable via driver config's
     * `maxTransactionRetryTime` property in milliseconds.
     *
     * @param {function(tx: Transaction): Promise} transactionWork - callback that executes operations against
     * a given {@link Transaction}.
     * @param {TransactionConfig} [transactionConfig] - configuration for all transactions started to execute the unit of work.
     * @return {Promise} resolved promise as returned by the given function or rejected promise when given
     * function or commit fails.
     */

  }, {
    key: "readTransaction",
    value: function readTransaction(transactionWork, transactionConfig) {
      var config = new _txConfig["default"](transactionConfig);
      return this._runTransaction(_constants.ACCESS_MODE_READ, config, transactionWork);
    }
    /**
     * Execute given unit of work in a {@link WRITE} transaction.
     *
     * Transaction will automatically be committed unless the given function throws or returns a rejected promise.
     * Some failures of the given function or the commit itself will be retried with exponential backoff with initial
     * delay of 1 second and maximum retry time of 30 seconds. Maximum retry time is configurable via driver config's
     * `maxTransactionRetryTime` property in milliseconds.
     *
     * @param {function(tx: Transaction): Promise} transactionWork - callback that executes operations against
     * a given {@link Transaction}.
     * @param {TransactionConfig} [transactionConfig] - configuration for all transactions started to execute the unit of work.
     * @return {Promise} resolved promise as returned by the given function or rejected promise when given
     * function or commit fails.
     */

  }, {
    key: "writeTransaction",
    value: function writeTransaction(transactionWork, transactionConfig) {
      var config = new _txConfig["default"](transactionConfig);
      return this._runTransaction(_constants.ACCESS_MODE_WRITE, config, transactionWork);
    }
  }, {
    key: "_runTransaction",
    value: function _runTransaction(accessMode, transactionConfig, transactionWork) {
      var _this2 = this;

      return this._transactionExecutor.execute(function () {
        return _this2._beginTransaction(accessMode, transactionConfig);
      }, transactionWork);
    }
    /**
     * Update value of the last bookmark.
     * @param {Bookmark} newBookmark the new bookmark.
     */

  }, {
    key: "_updateBookmark",
    value: function _updateBookmark(newBookmark) {
      if (newBookmark && !newBookmark.isEmpty()) {
        this._lastBookmark = newBookmark;
      }
    }
    /**
     * Close this session.
     * @param {function()} callback - Function to be called after the session has been closed
     * @return
     */

  }, {
    key: "close",
    value: function close() {
      var _this3 = this;

      var callback = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : function () {
        return null;
      };

      if (this._open) {
        this._open = false;

        this._transactionExecutor.close();

        this._readConnectionHolder.close().then(function () {
          _this3._writeConnectionHolder.close().then(function () {
            callback();
          });
        });
      } else {
        callback();
      }
    }
  }, {
    key: "_connectionHolderWithMode",
    value: function _connectionHolderWithMode(mode) {
      if (mode === _constants.ACCESS_MODE_READ) {
        return this._readConnectionHolder;
      } else if (mode === _constants.ACCESS_MODE_WRITE) {
        return this._writeConnectionHolder;
      } else {
        throw (0, _error.newError)('Unknown access mode: ' + mode);
      }
    }
  }]);
  return Session;
}();
/**
 * @private
 */


var SessionStreamObserver =
/*#__PURE__*/
function (_StreamObserver) {
  (0, _inherits2["default"])(SessionStreamObserver, _StreamObserver);

  function SessionStreamObserver(session) {
    var _this4;

    (0, _classCallCheck2["default"])(this, SessionStreamObserver);
    _this4 = (0, _possibleConstructorReturn2["default"])(this, (0, _getPrototypeOf2["default"])(SessionStreamObserver).call(this));
    _this4._session = session;
    return _this4;
  }

  (0, _createClass2["default"])(SessionStreamObserver, [{
    key: "onCompleted",
    value: function onCompleted(meta) {
      (0, _get2["default"])((0, _getPrototypeOf2["default"])(SessionStreamObserver.prototype), "onCompleted", this).call(this, meta);
      var bookmark = new _bookmark["default"](meta.bookmark);

      this._session._updateBookmark(bookmark);
    }
  }]);
  return SessionStreamObserver;
}(_streamObserver["default"]);

function _createTransactionExecutor(config) {
  var maxRetryTimeMs = config && config.maxTransactionRetryTime ? config.maxTransactionRetryTime : null;
  return new _transactionExecutor["default"](maxRetryTimeMs);
}

var _default = Session;
exports["default"] = _default;

},{"./driver":28,"./error":29,"./internal/bookmark":36,"./internal/connection-holder":47,"./internal/constants":51,"./internal/stream-observer":75,"./internal/transaction-executor":77,"./internal/tx-config":78,"./internal/util":80,"./result":83,"./transaction":88,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/get":8,"@babel/runtime/helpers/getPrototypeOf":9,"@babel/runtime/helpers/inherits":10,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/possibleConstructorReturn":18}],86:[function(require,module,exports){
"use strict";

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.isPoint = isPoint;
exports.Point = void 0;

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var _util = require("./internal/util");

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
var POINT_IDENTIFIER_PROPERTY = '__isPoint__';
/**
 * Represents a single two or three-dimensional point in a particular coordinate reference system.
 * Created `Point` objects are frozen with `Object.freeze()` in constructor and thus immutable.
 */

var Point =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {Integer|number} srid the coordinate reference system identifier.
   * @param {number} x the `x` coordinate of the point.
   * @param {number} y the `y` coordinate of the point.
   * @param {number} [z=undefined] the `y` coordinate of the point or `undefined` if point has 2 dimensions.
   */
  function Point(srid, x, y, z) {
    (0, _classCallCheck2["default"])(this, Point);
    this.srid = (0, _util.assertNumberOrInteger)(srid, 'SRID');
    this.x = (0, _util.assertNumber)(x, 'X coordinate');
    this.y = (0, _util.assertNumber)(y, 'Y coordinate');
    this.z = z === null || z === undefined ? z : (0, _util.assertNumber)(z, 'Z coordinate');
    Object.freeze(this);
  }

  (0, _createClass2["default"])(Point, [{
    key: "toString",
    value: function toString() {
      return this.z || this.z === 0 ? "Point{srid=".concat(formatAsFloat(this.srid), ", x=").concat(formatAsFloat(this.x), ", y=").concat(formatAsFloat(this.y), ", z=").concat(formatAsFloat(this.z), "}") : "Point{srid=".concat(formatAsFloat(this.srid), ", x=").concat(formatAsFloat(this.x), ", y=").concat(formatAsFloat(this.y), "}");
    }
  }]);
  return Point;
}();

exports.Point = Point;

function formatAsFloat(number) {
  return Number.isInteger(number) ? number + '.0' : number.toString();
}

Object.defineProperty(Point.prototype, POINT_IDENTIFIER_PROPERTY, {
  value: true,
  enumerable: false,
  configurable: false
});
/**
 * Test if given object is an instance of {@link Point} class.
 * @param {object} obj the object to test.
 * @return {boolean} `true` if given object is a {@link Point}, `false` otherwise.
 */

function isPoint(obj) {
  return (obj && obj[POINT_IDENTIFIER_PROPERTY]) === true;
}

},{"./internal/util":80,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11}],87:[function(require,module,exports){
"use strict";

var _interopRequireWildcard = require("@babel/runtime/helpers/interopRequireWildcard");

var _interopRequireDefault = require("@babel/runtime/helpers/interopRequireDefault");

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.isDuration = isDuration;
exports.isLocalTime = isLocalTime;
exports.isTime = isTime;
exports.isDate = isDate;
exports.isLocalDateTime = isLocalDateTime;
exports.isDateTime = isDateTime;
exports.DateTime = exports.LocalDateTime = exports.Date = exports.Time = exports.LocalTime = exports.Duration = void 0;

var _slicedToArray2 = _interopRequireDefault(require("@babel/runtime/helpers/slicedToArray"));

var _classCallCheck2 = _interopRequireDefault(require("@babel/runtime/helpers/classCallCheck"));

var _createClass2 = _interopRequireDefault(require("@babel/runtime/helpers/createClass"));

var util = _interopRequireWildcard(require("./internal/temporal-util"));

var _util = require("./internal/util");

var _error = require("./error");

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
var IDENTIFIER_PROPERTY_ATTRIBUTES = {
  value: true,
  enumerable: false,
  configurable: false
};
var DURATION_IDENTIFIER_PROPERTY = '__isDuration__';
var LOCAL_TIME_IDENTIFIER_PROPERTY = '__isLocalTime__';
var TIME_IDENTIFIER_PROPERTY = '__isTime__';
var DATE_IDENTIFIER_PROPERTY = '__isDate__';
var LOCAL_DATE_TIME_IDENTIFIER_PROPERTY = '__isLocalDateTime__';
var DATE_TIME_IDENTIFIER_PROPERTY = '__isDateTime__';
/**
 * Represents an ISO 8601 duration. Contains both date-based values (years, months, days) and time-based values (seconds, nanoseconds).
 * Created `Duration` objects are frozen with `Object.freeze()` in constructor and thus immutable.
 */

var Duration =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {Integer|number} months the number of months for the new duration.
   * @param {Integer|number} days the number of days for the new duration.
   * @param {Integer|number} seconds the number of seconds for the new duration.
   * @param {Integer|number} nanoseconds the number of nanoseconds for the new duration.
   */
  function Duration(months, days, seconds, nanoseconds) {
    (0, _classCallCheck2["default"])(this, Duration);
    this.months = (0, _util.assertNumberOrInteger)(months, 'Months');
    this.days = (0, _util.assertNumberOrInteger)(days, 'Days');
    (0, _util.assertNumberOrInteger)(seconds, 'Seconds');
    (0, _util.assertNumberOrInteger)(nanoseconds, 'Nanoseconds');
    this.seconds = util.normalizeSecondsForDuration(seconds, nanoseconds);
    this.nanoseconds = util.normalizeNanosecondsForDuration(nanoseconds);
    Object.freeze(this);
  }

  (0, _createClass2["default"])(Duration, [{
    key: "toString",
    value: function toString() {
      return util.durationToIsoString(this.months, this.days, this.seconds, this.nanoseconds);
    }
  }]);
  return Duration;
}();

exports.Duration = Duration;
Object.defineProperty(Duration.prototype, DURATION_IDENTIFIER_PROPERTY, IDENTIFIER_PROPERTY_ATTRIBUTES);
/**
 * Test if given object is an instance of {@link Duration} class.
 * @param {object} obj the object to test.
 * @return {boolean} `true` if given object is a {@link Duration}, `false` otherwise.
 */

function isDuration(obj) {
  return hasIdentifierProperty(obj, DURATION_IDENTIFIER_PROPERTY);
}
/**
 * Represents an instant capturing the time of day, but not the date, nor the timezone.
 * Created `LocalTime` objects are frozen with `Object.freeze()` in constructor and thus immutable.
 */


var LocalTime =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {Integer|number} hour the hour for the new local time.
   * @param {Integer|number} minute the minute for the new local time.
   * @param {Integer|number} second the second for the new local time.
   * @param {Integer|number} nanosecond the nanosecond for the new local time.
   */
  function LocalTime(hour, minute, second, nanosecond) {
    (0, _classCallCheck2["default"])(this, LocalTime);
    this.hour = util.assertValidHour(hour);
    this.minute = util.assertValidMinute(minute);
    this.second = util.assertValidSecond(second);
    this.nanosecond = util.assertValidNanosecond(nanosecond);
    Object.freeze(this);
  }
  /**
   * Create a local time object from the given standard JavaScript `Date` and optional nanoseconds.
   * Year, month, day and time zone offset components of the given date are ignored.
   * @param {global.Date} standardDate the standard JavaScript date to convert.
   * @param {Integer|number|undefined} nanosecond the optional amount of nanoseconds.
   * @return {LocalTime} new local time.
   */


  (0, _createClass2["default"])(LocalTime, [{
    key: "toString",
    value: function toString() {
      return util.timeToIsoString(this.hour, this.minute, this.second, this.nanosecond);
    }
  }], [{
    key: "fromStandardDate",
    value: function fromStandardDate(standardDate, nanosecond) {
      verifyStandardDateAndNanos(standardDate, nanosecond);
      return new LocalTime(standardDate.getHours(), standardDate.getMinutes(), standardDate.getSeconds(), util.totalNanoseconds(standardDate, nanosecond));
    }
  }]);
  return LocalTime;
}();

exports.LocalTime = LocalTime;
Object.defineProperty(LocalTime.prototype, LOCAL_TIME_IDENTIFIER_PROPERTY, IDENTIFIER_PROPERTY_ATTRIBUTES);
/**
 * Test if given object is an instance of {@link LocalTime} class.
 * @param {object} obj the object to test.
 * @return {boolean} `true` if given object is a {@link LocalTime}, `false` otherwise.
 */

function isLocalTime(obj) {
  return hasIdentifierProperty(obj, LOCAL_TIME_IDENTIFIER_PROPERTY);
}
/**
 * Represents an instant capturing the time of day, and the timezone offset in seconds, but not the date.
 * Created `Time` objects are frozen with `Object.freeze()` in constructor and thus immutable.
 */


var Time =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {Integer|number} hour the hour for the new local time.
   * @param {Integer|number} minute the minute for the new local time.
   * @param {Integer|number} second the second for the new local time.
   * @param {Integer|number} nanosecond the nanosecond for the new local time.
   * @param {Integer|number} timeZoneOffsetSeconds the time zone offset in seconds. Value represents the difference, in seconds, from UTC to local time.
   * This is different from standard JavaScript `Date.getTimezoneOffset()` which is the difference, in minutes, from local time to UTC.
   */
  function Time(hour, minute, second, nanosecond, timeZoneOffsetSeconds) {
    (0, _classCallCheck2["default"])(this, Time);
    this.hour = util.assertValidHour(hour);
    this.minute = util.assertValidMinute(minute);
    this.second = util.assertValidSecond(second);
    this.nanosecond = util.assertValidNanosecond(nanosecond);
    this.timeZoneOffsetSeconds = (0, _util.assertNumberOrInteger)(timeZoneOffsetSeconds, 'Time zone offset in seconds');
    Object.freeze(this);
  }
  /**
   * Create a time object from the given standard JavaScript `Date` and optional nanoseconds.
   * Year, month and day components of the given date are ignored.
   * @param {global.Date} standardDate the standard JavaScript date to convert.
   * @param {Integer|number|undefined} nanosecond the optional amount of nanoseconds.
   * @return {Time} new time.
   */


  (0, _createClass2["default"])(Time, [{
    key: "toString",
    value: function toString() {
      return util.timeToIsoString(this.hour, this.minute, this.second, this.nanosecond) + util.timeZoneOffsetToIsoString(this.timeZoneOffsetSeconds);
    }
  }], [{
    key: "fromStandardDate",
    value: function fromStandardDate(standardDate, nanosecond) {
      verifyStandardDateAndNanos(standardDate, nanosecond);
      return new Time(standardDate.getHours(), standardDate.getMinutes(), standardDate.getSeconds(), util.totalNanoseconds(standardDate, nanosecond), util.timeZoneOffsetInSeconds(standardDate));
    }
  }]);
  return Time;
}();

exports.Time = Time;
Object.defineProperty(Time.prototype, TIME_IDENTIFIER_PROPERTY, IDENTIFIER_PROPERTY_ATTRIBUTES);
/**
 * Test if given object is an instance of {@link Time} class.
 * @param {object} obj the object to test.
 * @return {boolean} `true` if given object is a {@link Time}, `false` otherwise.
 */

function isTime(obj) {
  return hasIdentifierProperty(obj, TIME_IDENTIFIER_PROPERTY);
}
/**
 * Represents an instant capturing the date, but not the time, nor the timezone.
 * Created `Date` objects are frozen with `Object.freeze()` in constructor and thus immutable.
 */


var Date =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {Integer|number} year the year for the new local date.
   * @param {Integer|number} month the month for the new local date.
   * @param {Integer|number} day the day for the new local date.
   */
  function Date(year, month, day) {
    (0, _classCallCheck2["default"])(this, Date);
    this.year = util.assertValidYear(year);
    this.month = util.assertValidMonth(month);
    this.day = util.assertValidDay(day);
    Object.freeze(this);
  }
  /**
   * Create a date object from the given standard JavaScript `Date`.
   * Hour, minute, second, millisecond and time zone offset components of the given date are ignored.
   * @param {global.Date} standardDate the standard JavaScript date to convert.
   * @return {Date} new date.
   */


  (0, _createClass2["default"])(Date, [{
    key: "toString",
    value: function toString() {
      return util.dateToIsoString(this.year, this.month, this.day);
    }
  }], [{
    key: "fromStandardDate",
    value: function fromStandardDate(standardDate) {
      verifyStandardDateAndNanos(standardDate, null);
      return new Date(standardDate.getFullYear(), standardDate.getMonth() + 1, standardDate.getDate());
    }
  }]);
  return Date;
}();

exports.Date = Date;
Object.defineProperty(Date.prototype, DATE_IDENTIFIER_PROPERTY, IDENTIFIER_PROPERTY_ATTRIBUTES);
/**
 * Test if given object is an instance of {@link Date} class.
 * @param {object} obj the object to test.
 * @return {boolean} `true` if given object is a {@link Date}, `false` otherwise.
 */

function isDate(obj) {
  return hasIdentifierProperty(obj, DATE_IDENTIFIER_PROPERTY);
}
/**
 * Represents an instant capturing the date and the time, but not the timezone.
 * Created `LocalDateTime` objects are frozen with `Object.freeze()` in constructor and thus immutable.
 */


var LocalDateTime =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {Integer|number} year the year for the new local date.
   * @param {Integer|number} month the month for the new local date.
   * @param {Integer|number} day the day for the new local date.
   * @param {Integer|number} hour the hour for the new local time.
   * @param {Integer|number} minute the minute for the new local time.
   * @param {Integer|number} second the second for the new local time.
   * @param {Integer|number} nanosecond the nanosecond for the new local time.
   */
  function LocalDateTime(year, month, day, hour, minute, second, nanosecond) {
    (0, _classCallCheck2["default"])(this, LocalDateTime);
    this.year = util.assertValidYear(year);
    this.month = util.assertValidMonth(month);
    this.day = util.assertValidDay(day);
    this.hour = util.assertValidHour(hour);
    this.minute = util.assertValidMinute(minute);
    this.second = util.assertValidSecond(second);
    this.nanosecond = util.assertValidNanosecond(nanosecond);
    Object.freeze(this);
  }
  /**
   * Create a local date-time object from the given standard JavaScript `Date` and optional nanoseconds.
   * Time zone offset component of the given date is ignored.
   * @param {global.Date} standardDate the standard JavaScript date to convert.
   * @param {Integer|number|undefined} nanosecond the optional amount of nanoseconds.
   * @return {LocalDateTime} new local date-time.
   */


  (0, _createClass2["default"])(LocalDateTime, [{
    key: "toString",
    value: function toString() {
      return localDateTimeToString(this.year, this.month, this.day, this.hour, this.minute, this.second, this.nanosecond);
    }
  }], [{
    key: "fromStandardDate",
    value: function fromStandardDate(standardDate, nanosecond) {
      verifyStandardDateAndNanos(standardDate, nanosecond);
      return new LocalDateTime(standardDate.getFullYear(), standardDate.getMonth() + 1, standardDate.getDate(), standardDate.getHours(), standardDate.getMinutes(), standardDate.getSeconds(), util.totalNanoseconds(standardDate, nanosecond));
    }
  }]);
  return LocalDateTime;
}();

exports.LocalDateTime = LocalDateTime;
Object.defineProperty(LocalDateTime.prototype, LOCAL_DATE_TIME_IDENTIFIER_PROPERTY, IDENTIFIER_PROPERTY_ATTRIBUTES);
/**
 * Test if given object is an instance of {@link LocalDateTime} class.
 * @param {object} obj the object to test.
 * @return {boolean} `true` if given object is a {@link LocalDateTime}, `false` otherwise.
 */

function isLocalDateTime(obj) {
  return hasIdentifierProperty(obj, LOCAL_DATE_TIME_IDENTIFIER_PROPERTY);
}
/**
 * Represents an instant capturing the date, the time and the timezone identifier.
 * Created `DateTime` objects are frozen with `Object.freeze()` in constructor and thus immutable.
 */


var DateTime =
/*#__PURE__*/
function () {
  /**
   * @constructor
   * @param {Integer|number} year the year for the new date-time.
   * @param {Integer|number} month the month for the new date-time.
   * @param {Integer|number} day the day for the new date-time.
   * @param {Integer|number} hour the hour for the new date-time.
   * @param {Integer|number} minute the minute for the new date-time.
   * @param {Integer|number} second the second for the new date-time.
   * @param {Integer|number} nanosecond the nanosecond for the new date-time.
   * @param {Integer|number} timeZoneOffsetSeconds the time zone offset in seconds. Either this argument or `timeZoneId` should be defined.
   * Value represents the difference, in seconds, from UTC to local time.
   * This is different from standard JavaScript `Date.getTimezoneOffset()` which is the difference, in minutes, from local time to UTC.
   * @param {string|null} timeZoneId the time zone id for the new date-time. Either this argument or `timeZoneOffsetSeconds` should be defined.
   */
  function DateTime(year, month, day, hour, minute, second, nanosecond, timeZoneOffsetSeconds, timeZoneId) {
    (0, _classCallCheck2["default"])(this, DateTime);
    this.year = util.assertValidYear(year);
    this.month = util.assertValidMonth(month);
    this.day = util.assertValidDay(day);
    this.hour = util.assertValidHour(hour);
    this.minute = util.assertValidMinute(minute);
    this.second = util.assertValidSecond(second);
    this.nanosecond = util.assertValidNanosecond(nanosecond);

    var _verifyTimeZoneArgume = verifyTimeZoneArguments(timeZoneOffsetSeconds, timeZoneId),
        _verifyTimeZoneArgume2 = (0, _slicedToArray2["default"])(_verifyTimeZoneArgume, 2),
        offset = _verifyTimeZoneArgume2[0],
        id = _verifyTimeZoneArgume2[1];

    this.timeZoneOffsetSeconds = offset;
    this.timeZoneId = id;
    Object.freeze(this);
  }
  /**
   * Create a date-time object from the given standard JavaScript `Date` and optional nanoseconds.
   * @param {global.Date} standardDate the standard JavaScript date to convert.
   * @param {Integer|number|undefined} nanosecond the optional amount of nanoseconds.
   * @return {DateTime} new date-time.
   */


  (0, _createClass2["default"])(DateTime, [{
    key: "toString",
    value: function toString() {
      var localDateTimeStr = localDateTimeToString(this.year, this.month, this.day, this.hour, this.minute, this.second, this.nanosecond);
      var timeZoneStr = this.timeZoneId ? "[".concat(this.timeZoneId, "]") : util.timeZoneOffsetToIsoString(this.timeZoneOffsetSeconds);
      return localDateTimeStr + timeZoneStr;
    }
  }], [{
    key: "fromStandardDate",
    value: function fromStandardDate(standardDate, nanosecond) {
      verifyStandardDateAndNanos(standardDate, nanosecond);
      return new DateTime(standardDate.getFullYear(), standardDate.getMonth() + 1, standardDate.getDate(), standardDate.getHours(), standardDate.getMinutes(), standardDate.getSeconds(), util.totalNanoseconds(standardDate, nanosecond), util.timeZoneOffsetInSeconds(standardDate), null
      /* no time zone id */
      );
    }
  }]);
  return DateTime;
}();

exports.DateTime = DateTime;
Object.defineProperty(DateTime.prototype, DATE_TIME_IDENTIFIER_PROPERTY, IDENTIFIER_PROPERTY_ATTRIBUTES);
/**
 * Test if given object is an instance of {@link DateTime} class.
 * @param {object} obj the object to test.
 * @return {boolean} `true` if given object is a {@link DateTime}, `false` otherwise.
 */

function isDateTime(obj) {
  return hasIdentifierProperty(obj, DATE_TIME_IDENTIFIER_PROPERTY);
}

function hasIdentifierProperty(obj, property) {
  return (obj && obj[property]) === true;
}

function localDateTimeToString(year, month, day, hour, minute, second, nanosecond) {
  return util.dateToIsoString(year, month, day) + 'T' + util.timeToIsoString(hour, minute, second, nanosecond);
}

function verifyTimeZoneArguments(timeZoneOffsetSeconds, timeZoneId) {
  var offsetDefined = timeZoneOffsetSeconds || timeZoneOffsetSeconds === 0;
  var idDefined = timeZoneId && timeZoneId !== '';

  if (offsetDefined && !idDefined) {
    (0, _util.assertNumberOrInteger)(timeZoneOffsetSeconds, 'Time zone offset in seconds');
    return [timeZoneOffsetSeconds, null];
  } else if (!offsetDefined && idDefined) {
    (0, _util.assertString)(timeZoneId, 'Time zone ID');
    return [null, timeZoneId];
  } else if (offsetDefined && idDefined) {
    throw (0, _error.newError)("Unable to create DateTime with both time zone offset and id. Please specify either of them. Given offset: ".concat(timeZoneOffsetSeconds, " and id: ").concat(timeZoneId));
  } else {
    throw (0, _error.newError)("Unable to create DateTime without either time zone offset or id. Please specify either of them. Given offset: ".concat(timeZoneOffsetSeconds, " and id: ").concat(timeZoneId));
  }
}

function verifyStandardDateAndNanos(standardDate, nanosecond) {
  (0, _util.assertValidDate)(standardDate, 'Standard date');

  if (nanosecond !== null && nanosecond !== undefined) {
    (0, _util.assertNumberOrInteger)(nanosecond, 'Nanosecond');
  }
}

},{"./error":29,"./internal/temporal-util":76,"./internal/util":80,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/interopRequireWildcard":12,"@babel/runtime/helpers/slicedToArray":20}],88:[function(require,module,exports){
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

},{"./internal/bookmark":36,"./internal/connection-holder":47,"./internal/stream-observer":75,"./internal/tx-config":78,"./internal/util":80,"./result":83,"@babel/runtime/helpers/classCallCheck":4,"@babel/runtime/helpers/createClass":6,"@babel/runtime/helpers/get":8,"@babel/runtime/helpers/getPrototypeOf":9,"@babel/runtime/helpers/inherits":10,"@babel/runtime/helpers/interopRequireDefault":11,"@babel/runtime/helpers/possibleConstructorReturn":18}],89:[function(require,module,exports){
"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports["default"] = void 0;

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
// DO NOT CHANGE THE VERSION BELOW HERE
// This is set by the build system at release time, using
//   gulp set --version <releaseversion>
//
// This is set up this way to keep the version in the code in
// sync with the npm package version, and to allow the build
// system to control version names at packaging time.
var _default = '0.0.0-dev';
exports["default"] = _default;

},{}]},{},[27])(27)
});
