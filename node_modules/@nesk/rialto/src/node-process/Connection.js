'use strict';

const EventEmitter = require('events'),
    ConnectionDelegate = require('./ConnectionDelegate'),
    ResourceRepository = require('./Data/ResourceRepository'),
    Instruction = require('./Instruction'),
    DataSerializer = require('./Data/Serializer'),
    DataUnserializer = require('./Data/Unserializer'),
    Logger = require('./Logger');

/**
 * Handle a connection interacting with this process.
 */
class Connection extends EventEmitter
{
    /**
     * Constructor.
     *
     * @param  {net.Socket} socket
     * @param  {ConnectionDelegate} delegate
     */
    constructor(socket, delegate)
    {
        super();

        this.socket = this.configureSocket(socket);

        this.delegate = delegate;

        this.resources = new ResourceRepository;

        this.dataSerializer = new DataSerializer(this.resources);
        this.dataUnserializer = new DataUnserializer(this.resources);
    }

    /**
     * Configure the socket for communication.
     *
     * @param  {net.Socket} socket
     * @return {net.Socket}
     */
    configureSocket(socket)
    {
        socket.setEncoding('utf8');

        socket.on('data', data => {
            this.emit('activity');

            this.handleSocketData(data);
        });

        return socket;
    }

    /**
     * Handle data received on the socket.
     *
     * @param  {string} data
     */
    handleSocketData(data)
    {
        const instruction = new Instruction(JSON.parse(data), this.resources, this.dataUnserializer),
            {responseHandler, errorHandler} = this.createInstructionHandlers();

        this.delegate.handleInstruction(instruction, responseHandler, errorHandler);
    }

    /**
     * Generate response and errors handlers.
     *
     * @return {Object}
     */
    createInstructionHandlers()
    {
        let handlerHasBeenCalled = false;

        const handler = (serializingMethod, value) => {
            if (handlerHasBeenCalled) {
                throw new Error('You can call only once the response/error handler.');
            }

            handlerHasBeenCalled = true;

            this.writeToSocket(JSON.stringify({
                logs: Logger.logs(),
                value: this[serializingMethod](value),
            }));
        };

        return {
            responseHandler: handler.bind(this, 'serializeValue'),
            errorHandler: handler.bind(this, 'serializeError'),
        };
    }

    /**
     * Write a string to the socket by slitting it in packets of fixed length.
     *
     * @param  {string} str
     */
    writeToSocket(str)
    {
        const payload = Buffer.from(str).toString('base64');

        const bodySize = Connection.SOCKET_PACKET_SIZE - Connection.SOCKET_HEADER_SIZE,
            chunkCount = Math.ceil(payload.length / bodySize);

        for (let i = 0 ; i < chunkCount ; i++) {
            const chunk = payload.substr(i * bodySize, bodySize);

            let chunksLeft = String(chunkCount - 1 - i);
            chunksLeft = chunksLeft.padStart(Connection.SOCKET_HEADER_SIZE - 1, '0');

            this.socket.write(`${chunksLeft}:${chunk}`);
        }
    }

    /**
     * Serialize a value to return to the client.
     *
     * @param  {*} value
     * @return {Object}
     */
    serializeValue(value)
    {
        return this.dataSerializer.serialize(value);
    }

    /**
     * Serialize an error to return to the client.
     *
     * @param  {Error} error
     * @return {Object}
     */
    serializeError(error)
    {
        return DataSerializer.serializeError(error);
    }
}

/**
 * The size of a packet sent through the sockets.
 *
 * @constant
 * @type {number}
*/
Connection.SOCKET_PACKET_SIZE = 1024;

/**
 * The size of the header in each packet sent through the sockets.
 *
 * @constant
 * @type {number}
 */
Connection.SOCKET_HEADER_SIZE = 5;

module.exports = Connection;
