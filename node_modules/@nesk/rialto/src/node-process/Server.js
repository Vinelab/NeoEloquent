'use strict';

const net = require('net'),
    Connection = require('./Connection');

/**
 * Listen for new socket connections.
 */
class Server
{
    /**
     * Constructor.
     *
     * @param  {ConnectionDelegate} connectionDelegate
     * @param  {Object} options
     */
    constructor(connectionDelegate, options = {})
    {
        this.options = options;

        this.started = this.start(connectionDelegate);

        this.resetIdleTimeout();
    }

    /**
     * Start the server and listen for new connections.
     *
     * @param  {ConnectionDelegate} connectionDelegate
     * @return {Promise}
     */
    start(connectionDelegate)
    {
        this.server = net.createServer(socket => {
            const connection = new Connection(socket, connectionDelegate);

            connection.on('activity', () => this.resetIdleTimeout());

            this.resetIdleTimeout();
        });

        return new Promise(resolve => {
            this.server.listen(() => resolve());
        });
    }

    /**
     * Write the listening port on the process output.
     */
    writePortToOutput()
    {
        process.stdout.write(`${this.server.address().port}\n`);
    }

    /**
     * Reset the idle timeout.
     *
     * @protected
     */
    resetIdleTimeout()
    {
        clearTimeout(this.idleTimer);

        const {idle_timeout: idleTimeout} = this.options;

        if (idleTimeout !== null) {
            this.idleTimer = setTimeout(() => {
                throw new Error('The idle timeout has been reached.');
            }, idleTimeout * 1000);
        }
    }
}

module.exports = Server;
