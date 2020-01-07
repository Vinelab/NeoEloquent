'use strict';

const Value = require('./Value');

class Serializer
{
    /**
     * Serialize an error to JSON.
     *
     * @param  {Error} error
     * @return {Object}
     */
    static serializeError(error)
    {
        return {
            __rialto_error__: true,
            message: error.message,
            stack: error.stack,
        };
    }

    /**
     * Constructor.
     *
     * @param  {ResourceRepository} resources
     */
    constructor(resources)
    {
        this.resources = resources;
    }

    /**
     * Serialize a value.
     *
     * @param  {*} value
     * @return {*}
     */
    serialize(value)
    {
        value = value === undefined ? null : value;

        if (Value.isContainer(value)) {
            return Value.mapContainer(value, this.serialize.bind(this));
        } else if (Value.isScalar(value)) {
            return value;
        } else {
            return this.resources.store(value).serialize();
        }
    }
}

module.exports = Serializer;
