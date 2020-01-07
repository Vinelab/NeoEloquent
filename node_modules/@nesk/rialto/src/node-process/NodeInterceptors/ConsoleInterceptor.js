'use strict';

const StandardStreamsInterceptor = require('./StandardStreamsInterceptor');

const SUPPORTED_CONSOLE_METHODS = {
    'debug': 'DEBUG',
    'dir': 'DEBUG',
    'dirxml': 'INFO',
    'error': 'ERROR',
    'info': 'INFO',
    'log': 'INFO',
    'table': 'DEBUG',
    'warn': 'WARNING',
};

class ConsoleInterceptor
{
    /**
     * Log interceptor.
     *
     * @callback logInterceptor
     * @param  {string} type
     * @param  {string} message
     */

    /**
     * Replace the global "console" object by a proxy to intercept the logs.
     *
     * @param  {logInterceptor} interceptor
     */
    static startInterceptingLogs(interceptor) {
        const consoleProxy = new Proxy(console, {
            get: (_, type) => this.getLoggingMethod(type, interceptor),
        });

        // Define the property instead of directly setting the property, the latter is forbidden in some environments.
        Object.defineProperty(global, 'console', {value: consoleProxy});
    }

    /**
     * Return an appropriate logging method for the console proxy.
     *
     * @param  {string} type
     * @param  {logInterceptor} interceptor
     * @return {callback}
     */
    static getLoggingMethod(type, interceptor) {
        const originalMethod = this.originalConsole[type].bind(this.originalConsole);

        if (!this.typeIsSupported(type)) {
            return originalMethod;
        }

        return (...args) => {
            StandardStreamsInterceptor.startInterceptingStrings(message => interceptor(type, message));
            originalMethod(...args);
            StandardStreamsInterceptor.stopInterceptingStrings();
        };
    }

    /**
     * Check if the type of the log is supported.
     *
     * @param  {*} type
     * @return {boolean}
     */
    static typeIsSupported(type) {
        return Object.keys(SUPPORTED_CONSOLE_METHODS).includes(type);
    }

    /**
     * Return a log level based on the provided type.
     *
     * @param  {*} type
     * @return {string|null}
     */
    static getLevelFromType(type) {
        return SUPPORTED_CONSOLE_METHODS[type] || null;
    }

    /**
     * Format a message from a console method.
     *
     * @param  {string} message
     * @return {string}
     */
    static formatMessage(message) {
        // Remove terminal colors written as escape sequences
        // See: https://stackoverflow.com/a/41407246/1513045
        message = message.replace(/\x1b\[\d+m/g, '');

        // Remove the final new line
        message = message.endsWith('\n') ? message.slice(0, -1) : message;

        return message;
    }
}

ConsoleInterceptor.originalConsole = console;

module.exports = ConsoleInterceptor;
