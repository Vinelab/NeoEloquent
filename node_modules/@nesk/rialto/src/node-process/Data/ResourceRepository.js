'use strict';

const ResourceIdentity = require('./ResourceIdentity');

class ResourceRepository
{
    /**
     * Constructor.
     */
    constructor()
    {
        this.resources = new Map;
    }

    /**
     * Retrieve a resource with its identity from a specific storage.
     *
     * @param  {Map} storage
     * @param  {ResourceIdentity} identity
     * @return {*}
     */
    static retrieveFrom(storage, identity)
    {
        for (let [resource, id] of storage) {
            if (identity.uniqueIdentifier() === id) {
                return resource;
            }
        }

        return null;
    }

    /**
     * Retrieve a resource with its identity from the local storage.
     *
     * @param  {ResourceIdentity} identity
     * @return {*}
     */
    retrieve(identity)
    {
        return ResourceRepository.retrieveFrom(this.resources, identity);
    }

    /**
     * Retrieve a resource with its unique identifier from the global storage.
     *
     * @param  {string} uniqueIdentifier
     * @return {*}
     */
    static retrieveGlobal(uniqueIdentifier)
    {
        const identity = new ResourceIdentity(uniqueIdentifier);
        return ResourceRepository.retrieveFrom(ResourceRepository.globalResources, identity);
    }

    /**
     * Store a resource in a specific storage and return its identity.
     *
     * @param  {Map} storage
     * @param  {*} resource
     * @return {ResourceIdentity}
     */
    static storeIn(storage, resource)
    {
        if (storage.has(resource)) {
            return ResourceRepository.generateResourceIdentity(resource, storage.get(resource));
        }

        const id = String(Date.now() + Math.random());

        storage.set(resource, id);

        return ResourceRepository.generateResourceIdentity(resource, id);
    }

    /**
     * Store a resource in the local storage and return its identity.
     *
     * @param  {*} resource
     * @return {ResourceIdentity}
     */
    store(resource)
    {
        return ResourceRepository.storeIn(this.resources, resource);
    }

    /**
     * Store a resource in the global storage and return its unique identifier.
     *
     * @param  {*} resource
     * @return {string}
     */
    static storeGlobal(resource)
    {
        return ResourceRepository.storeIn(ResourceRepository.globalResources, resource).uniqueIdentifier();
    }

    /**
     * Generate a resource identity.
     *
     * @param  {*} resource
     * @param  {string} uniqueIdentifier
     * @return {ResourceIdentity}
     */
    static generateResourceIdentity(resource, uniqueIdentifier)
    {
        return new ResourceIdentity(uniqueIdentifier, resource.constructor.name);
    }
}

ResourceRepository.globalResources = new Map;

module.exports = ResourceRepository;
