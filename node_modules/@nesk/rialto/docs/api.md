# API usage

## Options

The entry point of Rialto accepts [multiple options](https://github.com/nesk/rialto/blob/75b5a9464235a597e3ab71ac90246779a40fe145/src/ProcessSupervisor.php#L42-L70), here are some descriptions with the default values:

```php
[
    // Node's executable path
    'executable_path' => 'node',

    // How much time (in seconds) the process can stay inactive before being killed (set to null to disable)
    'idle_timeout' => 60,

    // How much time (in seconds) an instruction can take to return a value (set to null to disable)
    'read_timeout' => 30,

    // How much time (in seconds) the process can take to shutdown properly before being killed
    'stop_timeout' => 3,

    // A logger instance for debugging (must implement \Psr\Log\LoggerInterface)
    'logger' => null,

    // Logs the output of console methods (console.log, console.debug, console.table, etc...) to the PHP logger
    'log_node_console' => false,

    // Enables debugging mode:
    //   - adds the --inspect flag to Node's command
    //   - appends stack traces to Node exception messages
    'debug' => false,
]
```

You can define an option in your entry point using the third parameter of the parent constructor:

```php
class MyEntryPoint extends AbstractEntryPoint
{
    public function __construct()
    {
        // ...

        $myOptions = [
            'idle_timeout' => 300, // 5 minutes
        ];

        parent::__construct($connectionDelegate, $processDelegate, $myOptions);
    }
}
```

### Accepting user options

If you want your users to define some of Rialto's options, you can use the fourth parameter:

```php
class MyEntryPoint extends AbstractEntryPoint
{
    public function __construct(array $userOptions = [])
    {
        // ...

        parent::__construct($connectionDelegate, $processDelegate, $myOptions, $userOptions);
    }
}
```

User options will override your own defaults. To prevent a user to define some specific options, use the `$forbiddenOptions` property:

```php
class MyEntryPoint extends AbstractEntryPoint
{
    protected $forbiddenOptions = ['idle_timeout', 'stop_timeout'];

    public function __construct(array $userOptions = [])
    {
        // ...

        parent::__construct($connectionDelegate, $processDelegate, $myOptions, $userOptions);
    }
}
```

By default, users are forbidden to define the `stop_timeout` option.

**Note:** You should authorize your users to define, at least, the `executable_path`, `logger` and `debug` options.

## Node errors

If an error (or an unhandled rejection) occurs in Node, a `Node\FatalException` will be thrown and the process closed, you will have to create a new instance of your entry point.

To avoid that, you can ask Node to catch these errors by prepending your instruction with `->tryCatch`:

```php
use Nesk\Rialto\Exceptions\Node;

try {
    $someResource->tryCatch->inexistantMethod();
} catch (Node\Exception $exception) {
    // Handle the exception...
}
```

Instead, a `Node\Exception` will be thrown, the Node process will stay alive and usable.

## JavaScript functions

With Rialto you can create JavaScript functions and pass them to the Node process, this can be useful to map some values or any other actions based on callbacks.

To create them, you need to use the `Nesk\Rialto\Data\JsFunction` class and call one or multiple methods in this list:

- `parameters(array)`: Sets parameters for your function, each string in the array is a parameter. You can define a default value for a parameter by using the parameter name as a key and its default value as the item value (e.g. `->parameters(['firstParam', 'secondParam' => 'Default string value'])`).

- `body(string)`: Sets the body of your function, just write your JS code in a PHP string (e.g. `->body("return 'Hello world!'")`).

- `scope(array)`: Defines scope variables for your function. Say you have `$hello = 'Hello world!'` in your PHP and you want to use it in your JS code, you can write `->scope(['myVar' => $hello])` and you will be able to use it in your body `->body("console.log(myVar)")`.
<br> **Note:** Scope variables must be JSON serializable values or resources created by Rialto.

- `async(?bool)`: Makes your JS function async. Optionally, you can provide a boolean: `true` will make the function async, `false` will remove the `async` state.
<br> **Note:** Like in the ECMAScript specification, JS functions _aren't_ async by default.

To create a new JS function, use `JsFunction::createWith__METHOD_NAME__` with the method name you want (in the list just above):

```php
JsFunction::createWithParameters(['a', 'b'])
    ->body('return a + b;');
```

Here we used `createWithParameters` to start the creation, but we could have used `createWithBody`, `createWithScope`, etc…

<details>
<summary><strong>⚙️ Some examples showing how to use these methods</strong></summary> <br>

- A function with a body:

```php
$jsFunction = JsFunction::createWithBody("return process.uptime()");

$someResource->someMethodWithCallback($jsFunction);
```

- A function with parameters and a body:

```php
$jsFunction = JsFunction::createWithParameters(['str', 'str2' => 'Default value!'])
    ->body("return 'This is my string: ' + str");

$someResource->someMethodWithCallback($jsFunction);
```

- A function with parameters, a body, scoped values, and async flag:

```php
$functionScope = ['stringtoPrepend' => 'This is another string: '];

$jsFunction = JsFunction::createWithAsync()
    ->parameters(['str'])
    ->body("return stringToPrepend + str")
    ->scope($functionScope);

$someResource->someMethodWithCallback($jsFunction);
```

</details>

<br>

<details>
<summary><strong>⚠️ Deprecated examples of the <code>JsFunction::create()</code> method</strong></summary> <br>

- A function with a body:

```php
$jsFunction = JsFunction::create("
    return process.uptime();
");

$someResource->someMethodWithCallback($jsFunction);
```

- A function with parameters:

```php
$jsFunction = JsFunction::create(['str', 'str2' => 'Default value!'], "
    return 'This is my string: ' + str;
");

$someResource->someMethodWithCallback($jsFunction);
```

- A function with parameters, a body, and scoped values:

```php
$functionScope = ['stringtoPrepend' => 'This is another string: '];

$jsFunction = JsFunction::create(['str'], "
    return stringToPrepend + str;
", $functionScope);

$someResource->someMethodWithCallback($jsFunction);
```

</details>

## Destruction

If you're worried about the destruction of the Node process, here's two things you need to know:

- Once the entry point and all the resources (like the `BasicResource` class) are unset, the Node process is automatically terminated.
- If, for any reason, the Node process doesn't terminate, it will kill itself once the `idle_timeout` is exceeded.
