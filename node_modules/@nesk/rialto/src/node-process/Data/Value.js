'use strict';

const _ = require('lodash');

class Value
{
    /**
     * Determine if the value is a string, a number, a boolean, or null.
     *
     * @param  {*} value
     * @return {boolean}
     */
    static isScalar(value)
    {
        return _.isString(value) || _.isNumber(value) || _.isBoolean(value) || _.isNull(value);
    }

    /**
     * Determine if the value is an array or a plain object.
     *
     * @param  {*} value
     * @return {boolean}
     */
    static isContainer(value)
    {
        return _.isArray(value) || _.isPlainObject(value);
    }

    /**
     * Map the values of a container.
     *
     * @param  {*} container
     * @param  {callback} mapper
     * @return {array}
     */
    static mapContainer(container, mapper)
    {
        if (_.isArray(container)) {
            return container.map(mapper);
        } else if (_.isPlainObject(container)) {
            return Object.entries(container).reduce((finalObject, [key, value]) => {
                finalObject[key] = mapper(value);

                return finalObject;
            }, {});
        } else {
            return container;
        }
    }

    /**
     * Determine if the value is a resource.
     *
     * @param  {*} value
     * @return {boolean}
     */
    static isResource(value)
    {
        return !Value.isContainer(value) && !Value.isScalar(value);
    }
}

module.exports = Value;
