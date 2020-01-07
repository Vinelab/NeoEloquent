'use strict';

/**
 * @callback responseHandler
 * @param  {*} value
 */

/**
 * @callback errorHandler
 * @param  {Error} error
 */

/**
 * Handle the requests of a connection.
 */
class ConnectionDelegate
{
    /**
     * Constructor.
     *
     * @param  {Object} options
     */
    constructor(options)
    {
        this.options = options;
    }

    /**
     * Handle the provided instruction and respond to it.
     *
     * @param  {Instruction} instruction
     * @param  {responseHandler} responseHandler
     * @param  {errorHandler} errorHandler
     */
    handleInstruction(instruction, responseHandler, errorHandler)
    {
        responseHandler(null);
    }
}

module.exports = ConnectionDelegate;
