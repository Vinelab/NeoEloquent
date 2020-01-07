'use strict';

const _ = require('lodash'),
    Value = require('./Value'),
    ResourceIdentity = require('./ResourceIdentity'),
    ResourceRepository = require('./ResourceRepository');

// Some unserialized functions require an access to the ResourceRepository class, so we must put it in the global scope.
global.__rialto_ResourceRepository__ = ResourceRepository;

class Unserializer
{
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
     * Unserialize a value.
     *
     * @param  {*} value
     * @return {*}
     */
    unserialize(value)
    {
        if (_.get(value, '__rialto_resource__') === true) {
            return this.resources.retrieve(ResourceIdentity.unserialize(value));
        } else if (_.get(value, '__rialto_function__') === true) {
            return this.unserializeFunction(value);
        } else if (Value.isContainer(value)) {
            return Value.mapContainer(value, this.unserialize.bind(this));
        } else {
            return value;
        }
    }

    /**
     * Return a string used to embed a value in a function.
     *
     * @param  {*} value
     * @return {string}
     */
    embedFunctionValue(value)
    {
        value = this.unserialize(value);
        const valueUniqueIdentifier = ResourceRepository.storeGlobal(value);

        const a = Value.isResource(value)
            ? `
                __rialto_ResourceRepository__
                    .retrieveGlobal(${JSON.stringify(valueUniqueIdentifier)})
            `
            : JSON.stringify(value);

        return a;
    }

    /**
     * Unserialize a function.
     *
     * @param  {Object} value
     * @return {Function}
     */
    unserializeFunction(value)
    {
        const scopedVariables = [];

        for (let [varName, varValue] of Object.entries(value.scope)) {
            scopedVariables.push(`var ${varName} = ${this.embedFunctionValue(varValue)};`);
        }

        const parameters = [];

        for (let [paramKey, paramValue] of Object.entries(value.parameters)) {
            if (!isNaN(parseInt(paramKey, 10))) {
                parameters.push(paramValue);
            } else {
                parameters.push(`${paramKey} = ${this.embedFunctionValue(paramValue)}`);
            }
        }

        const asyncFlag = value.async ? 'async' : '';

        return new Function(`
            return ${asyncFlag} function (${parameters.join(', ')}) {
                ${scopedVariables.join('\n')}
                ${value.body}
            };
        `)();
    }
}

module.exports = Unserializer;
