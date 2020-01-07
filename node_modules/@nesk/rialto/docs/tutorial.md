# Creating your first bridge with Rialto

We will create a bridge to use [Node's File System module](https://nodejs.org/api/fs.html) in PHP. This is not especially useful but it will show you how Rialto works and handles pretty much everything for you.

## Importing Rialto

Import Rialto in your project:

```
composer require nesk/rialto
npm install @nesk/rialto
```

## The essential files

You will need to create at least two files for your package:

- **An entry point** (`FileSystem.php`): this PHP class inherits [`AbstractEntryPoint`](../src/AbstractEntryPoint.php) and its instanciation creates the Node process. Every instruction (calling a method, setting a property, etc…) made on this class will be intercepted and sent to Node.

```php
use Nesk\Rialto\AbstractEntryPoint;

class FileSystem extends AbstractEntryPoint
{
    public function __construct()
    {
        parent::__construct(__DIR__.'/FileSystemConnectionDelegate.js');
    }
}
```

- **A connection delegate** (`FileSystemConnectionDelegate.js`): this JavaScript class inherits [`ConnectionDelegate`](../src/node-process/ConnectionDelegate.js) and will execute the instructions made with PHP (calling a method, setting a property, etc…).

```js
const fs = require('fs'),
    {ConnectionDelegate} = require('@nesk/rialto');

module.exports = class FileSystemConnectionDelegate extends ConnectionDelegate
{
    handleInstruction(instruction, responseHandler, errorHandler)
    {
        // Define on which resource the instruction should be applied by default,
        // here we want to apply them on the "fs" module.
        instruction.setDefaultResource(fs);

        let value = null;

        try {
            // Try to execute the instruction
            value = instruction.execute();
        } catch (error) {
            // If the instruction fails and the user asked to catch errors (see the `tryCatch` property in the API),
            // send it with the error handler.
            if (instruction.shouldCatchErrors()) {
                return errorHandler(error);
            }

            throw error;
        }

        // Send back the value returned by the instruction
        responseHandler(value);
    }
}
```

With these two files, you should already be able to use your bridge:

```php
use Nesk\Puphpeteer\Fs\FileSystem;

$fs = new FileSystem;

$stats = $fs->statSync('/valid/file/path'); // Returns a basic resource representing a Stats instance

$stats->isFile(); // Returns true if the path points to a file
```

**Note:** You should use the synchronous methods of Node's FileSystem module. There is no way to handle asynchronous callbacks with Rialto for the moment.

## Creating specific resources

The example above returns a [`BasicResource`](../src/Data/BasicResource.php) class when the JavaScript API returns a resource (typically, a class instance). See this example:

```php
$buffer = $fs->readFileSync('/valid/file/path'); // Returns a basic resource representing a Buffer instance

$stats = $fs->statSync('/valid/file/path'); // Returns a basic resource representing a Stats instance
```

Its possible to know the name of the resource class:

```php
$buffer->getResourceIdentity()->className(); // Returns "Buffer"

$stats->getResourceIdentity()->className(); // Returns "Stats"
```

However, this is not convenient. That's why you can create specific resources to improve that. We will create 3 files:

- **A process delegate** (`FileSystemProcessDelegate.php`): this PHP class implements [`ShouldHandleProcessDelegation`](../src/Interfaces/ShouldHandleProcessDelegation.php) and is responsible to return the class names of the specific and default resources.

```php
use Nesk\Rialto\Traits\UsesBasicResourceAsDefault;
use Nesk\Rialto\Interfaces\ShouldHandleProcessDelegation;

class FileSystemProcessDelegate implements ShouldHandleProcessDelegation
{
    // Define that we want to use the BasicResource class as a default if resourceFromOriginalClassName() returns null
    use UsesBasicResourceAsDefault;

    public function resourceFromOriginalClassName(string $jsClassName): ?string
    {
        // Generate the appropriate class name for PHP
        $class = "{$jsClassName}Resource";

        // If the PHP class doesn't exist, return null, it will automatically create a basic resource.
        return class_exists($class) ? $class : null;
    }
}
```

- **A resource to represent Buffer instances** (`BufferResource.php`): this class inherits `BasicResource` by convenience but the only requirement is to implement the [`ShouldIdentifyResource`](../src/Interfaces/ShouldIdentifyResource.php) interface.

```php
use Nesk\Rialto\Data\BasicResource;

class BufferResource extends BasicResource
{
}
```

- **A resource to represent Stats instances** (`StatsResource.php`):

```php
use Nesk\Rialto\Data\BasicResource;

class StatsResource extends BasicResource
{
}
```

Once those 3 files are created, you will have to register the process delegate in your entry point (`FileSystem.php`):

```php
use Nesk\Rialto\AbstractEntryPoint;

class FileSystem extends AbstractEntryPoint
{
    public function __construct()
    {
        // Add the process delegate in addition to the connection delegate
        parent::__construct(__DIR__.'/FileSystemConnectionDelegate.js', new FileSystemProcessDelegate);
    }
}
```

Now you will get specific resources instead of the default one:

```php
$fs->readFileSync('/valid/file/path'); // Returns BufferResource

$fs->statSync('/valid/file/path'); // Returns StatsResource

$fs->statSync('/valid/file/path')->birthtime; // Returns a basic resource representing a Date instance
```

Specific resources can also help you to improve your API by adding methods to them:

```php
use Nesk\Rialto\Data\BasicResource;

class StatsResource extends BasicResource
{
    public function birthtime(): \DateTime
    {
        return (new \DateTime)->setTimestamp($this->birthtimeMs / 1000);
    }
}
```

```php
$fs->statSync('/valid/file/path')->birthtime(); // Returns a PHP's DateTime instance
```

## Learn more

Your first bridge with Rialto is ready, you can learn more by reading the [API documentation](api.md).
