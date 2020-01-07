'use strict';

class ResourceIdentity
{
    /**
     * Constructor.
     *
     * @param  {string} uniqueIdentifier
     * @param  {string|null} className
     */
    constructor(uniqueIdentifier, className = null)
    {
        this.resource = {uniqueIdentifier, className};
    }

    /**
     * Return the unique identifier of the resource.
     *
     * @return {string}
     */
    uniqueIdentifier()
    {
        return this.resource.uniqueIdentifier;
    }

    /**
     * Return the class name of the resource.
     *
     * @return {string|null}
     */
    className()
    {
        return this.resource.className;
    }

    /**
     * Unserialize a resource identity.
     *
     * @param  {Object} identity
     * @return {ResourceIdentity}
     */
    static unserialize(identity)
    {
        return new ResourceIdentity(identity.id, identity.class_name);
    }

    /**
     * Serialize the resource identity.
     *
     * @return {Object}
     */
    serialize()
    {
        return {
            __rialto_resource__: true,
            id: this.uniqueIdentifier(),
            class_name: this.className(),
        };
    }
}

module.exports = ResourceIdentity;
