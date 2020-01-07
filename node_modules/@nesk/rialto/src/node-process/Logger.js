'use strict';

class Logger
{
    /**
     * Add a new log to the queue.
     *
     * @param  {string} origin
     * @param  {string} level
     * @param  {string} message
     */
    static log(origin, level, message) {
        this.logsQueue.push({origin, level, message});
    }

    /**
     * Flush and return the logs in the queue.
     *
     * @return {array}
     */
    static logs() {
        const logs = this.logsQueue;
        this.logsQueue = [];
        return logs;
    }
}

Logger.logsQueue = [];

module.exports = Logger;
