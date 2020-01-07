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