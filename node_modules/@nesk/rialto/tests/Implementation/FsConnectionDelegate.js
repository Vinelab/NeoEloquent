'use strict';

const fs = require('fs'),
    {ConnectionDelegate} = require('../../src/node-process');

/**
 * Handle the requests of a connection to control the "fs" module.
 */
class FsConnectionDelegate extends ConnectionDelegate
{
    async handleInstruction(instruction, responseHandler, errorHandler)
    {
        instruction.setDefaultResource(this.extendFsModule(fs));

        let value = null;

        try {
            value = await instruction.execute();
        } catch (error) {
            if (instruction.shouldCatchErrors()) {
                return errorHandler(error);
            }

            throw error;
        }

        responseHandler(value);
    }

    extendFsModule(fs)
    {
        fs.multipleStatSync = (...paths) => paths.map(fs.statSync);

        fs.multipleResourcesIsFile = resources => resources.map(resource => resource.isFile());

        fs.getHeavyPayloadWithNonAsciiChars = () => {
            let payload = '';

            for (let i = 0 ; i < 1024 ; i++) {
                payload += 'a';
            }

            return `😘${payload}😘`;
        };

        fs.wait = ms => new Promise(resolve => setTimeout(resolve, ms));

        fs.runCallback = cb => cb(fs);

        fs.getOption = name => this.options[name];

        return fs;
    }
}

module.exports = FsConnectionDelegate;
