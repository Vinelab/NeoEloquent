<?php

namespace Nesk\Rialto\Tests;

use Monolog\Logger;
use Nesk\Rialto\Data\JsFunction;
use Nesk\Rialto\Exceptions\Node;
use Nesk\Rialto\Data\BasicResource;
use Symfony\Component\Process\Process;
use Nesk\Rialto\Tests\Implementation\Resources\Stats;
use Nesk\Rialto\Tests\Implementation\{FsWithProcessDelegation, FsWithoutProcessDelegation};

class ImplementationTest extends TestCase
{
    const JS_FUNCTION_CREATE_DEPRECATION_PATTERN = '/^Nesk\\\\Rialto\\\\Data\\\\JsFunction::create\(\)/';

    public function setUp(): void
    {
        parent::setUp();

        $this->dirPath = realpath(__DIR__.'/resources');
        $this->filePath = "{$this->dirPath}/file";

        $this->fs = $this->canPopulateProperty('fs') ? new FsWithProcessDelegation : null;
    }

    public function tearDown(): void
    {
        $this->fs = null;
    }

    /** @test */
    public function can_call_method_and_get_its_return_value()
    {
        $content = $this->fs->readFileSync($this->filePath, 'utf8');

        $this->assertEquals('Hello world!', $content);
    }

    /** @test */
    public function can_get_property()
    {
        $constants = $this->fs->constants;

        $this->assertInternalType('array', $constants);
    }

    /** @test */
    public function can_set_property()
    {
        $this->fs->foo = 'bar';
        $this->assertEquals('bar', $this->fs->foo);

        $this->fs->foo = null;
        $this->assertNull($this->fs->foo);
    }

    /** @test */
    public function can_return_basic_resources()
    {
        $resource = $this->fs->readFileSync($this->filePath);

        $this->assertInstanceOf(BasicResource::class, $resource);
    }

    /** @test */
    public function can_return_specific_resources()
    {
        $resource = $this->fs->statSync($this->filePath);

        $this->assertInstanceOf(Stats::class, $resource);
    }

    /** @test */
    public function can_cast_resources_to_string()
    {
        $resource = $this->fs->statSync($this->filePath);

        $this->assertEquals('[object Object]', (string) $resource);
    }

    /**
     * @test
     * @dontPopulateProperties fs
     */
    public function can_omit_process_delegation()
    {
        $this->fs = new FsWithoutProcessDelegation;

        $resource = $this->fs->statSync($this->filePath);

        $this->assertInstanceOf(BasicResource::class, $resource);
        $this->assertNotInstanceOf(Stats::class, $resource);
    }

    /** @test */
    public function can_use_nested_resources()
    {
        $resources = $this->fs->multipleStatSync($this->dirPath, $this->filePath);

        $this->assertCount(2, $resources);
        $this->assertContainsOnlyInstancesOf(Stats::class, $resources);

        $isFile = $this->fs->multipleResourcesIsFile($resources);

        $this->assertFalse($isFile[0]);
        $this->assertTrue($isFile[1]);
    }

    /** @test */
    public function can_use_multiple_resources_without_confusion()
    {
        $dirStats = $this->fs->statSync($this->dirPath);
        $fileStats = $this->fs->statSync($this->filePath);

        $this->assertInstanceOf(Stats::class, $dirStats);
        $this->assertInstanceOf(Stats::class, $fileStats);

        $this->assertTrue($dirStats->isDirectory());
        $this->assertTrue($fileStats->isFile());
    }

    /** @test */
    public function can_return_multiple_times_the_same_resource()
    {
        $stats1 = $this->fs->Stats;
        $stats2 = $this->fs->Stats;

        $this->assertEquals($stats1, $stats2);
    }

    /**
     * @test
     * @group js-functions
     */
    public function can_use_js_functions_with_a_body()
    {
        $functions = [
            $this->ignoreUserDeprecation(self::JS_FUNCTION_CREATE_DEPRECATION_PATTERN, function () {
                return JsFunction::create("return 'Simple callback';");
            }),
            JsFunction::createWithBody("return 'Simple callback';"),
        ];

        foreach ($functions as $function) {
            $value = $this->fs->runCallback($function);
            $this->assertEquals('Simple callback', $value);
        }
    }

    /**
     * @test
     * @group js-functions
     */
    public function can_use_js_functions_with_parameters()
    {
        $functions = [
            $this->ignoreUserDeprecation(self::JS_FUNCTION_CREATE_DEPRECATION_PATTERN, function () {
                return JsFunction::create(['fs'], "
                    return 'Callback using arguments: ' + fs.constructor.name;
                ");
            }),
            JsFunction::createWithParameters(['fs'])
                ->body("return 'Callback using arguments: ' + fs.constructor.name;"),
        ];

        foreach ($functions as $function) {
            $value = $this->fs->runCallback($function);
            $this->assertEquals('Callback using arguments: Object', $value);
        }
    }

    /**
     * @test
     * @group js-functions
     */
    public function can_use_js_functions_with_scope()
    {
        $functions = [
            $this->ignoreUserDeprecation(self::JS_FUNCTION_CREATE_DEPRECATION_PATTERN, function () {
                return JsFunction::create("
                    return 'Callback using scope: ' + foo;
                ", ['foo' => 'bar']);
            }),
            JsFunction::createWithScope(['foo' => 'bar'])
                ->body("return 'Callback using scope: ' + foo;"),
        ];

        foreach ($functions as $function) {
            $value = $this->fs->runCallback($function);
            $this->assertEquals('Callback using scope: bar', $value);
        }
    }

    /**
     * @test
     * @group js-functions
     */
    public function can_use_resources_in_js_functions()
    {
        $fileStats = $this->fs->statSync($this->filePath);

        $functions = [
            JsFunction::createWithParameters(['fs', 'fileStats' => $fileStats])
                ->body("return fileStats.isFile();"),
            JsFunction::createWithScope(['fileStats' => $fileStats])
                ->body("return fileStats.isFile();"),
        ];

        foreach ($functions as $function) {
            $isFile = $this->fs->runCallback($function);
            $this->assertTrue($isFile);
        }
    }

    /**
     * @test
     * @group js-functions
     */
    public function can_use_async_with_js_functions()
    {
        $function = JsFunction::createWithAsync()
            ->body("
                await Promise.resolve();
                return true;
            ");

        $this->assertTrue($this->fs->runCallback($function));

        $function = $function->async(false);

        $this->expectException(Node\FatalException::class);
        $this->expectExceptionMessage('await is only valid in async function');

        $this->fs->runCallback($function);
    }

    /**
     * @test
     * @group js-functions
     */
    public function js_functions_are_sync_by_default()
    {
        $function = JsFunction::createWithBody('await null');

        $this->expectException(Node\FatalException::class);
        $this->expectExceptionMessage('await is only valid in async function');

        $this->fs->runCallback($function);
    }

    /** @test */
    public function can_receive_heavy_payloads_with_non_ascii_chars()
    {
        $payload = $this->fs->getHeavyPayloadWithNonAsciiChars();

        $this->assertStringStartsWith('😘', $payload);
        $this->assertStringEndsWith('😘', $payload);
    }

    /**
     * @test
     * @expectedException \Nesk\Rialto\Exceptions\Node\FatalException
     * @expectedExceptionMessage Object.__inexistantMethod__ is not a function
     */
    public function node_crash_throws_a_fatal_exception()
    {
        $this->fs->__inexistantMethod__();
    }

    /**
     * @test
     * @expectedException \Nesk\Rialto\Exceptions\Node\Exception
     * @expectedExceptionMessage Object.__inexistantMethod__ is not a function
     */
    public function can_catch_errors()
    {
        $this->fs->tryCatch->__inexistantMethod__();
    }

    /**
     * @test
     * @expectedException \Nesk\Rialto\Exceptions\Node\FatalException
     * @expectedExceptionMessage Object.__inexistantMethod__ is not a function
     */
    public function catching_a_node_exception_doesnt_catch_fatal_exceptions()
    {
        try {
            $this->fs->__inexistantMethod__();
        } catch (Node\Exception $exception) {
            //
        }
    }

    /**
     * @test
     * @dontPopulateProperties fs
     */
    public function in_debug_mode_node_exceptions_contain_stack_trace_in_message()
    {
        $this->fs = new FsWithProcessDelegation(['debug' => true]);

        $regex = '/\n\nError: "Object\.__inexistantMethod__ is not a function"\n\s+at /';

        try {
            $this->fs->tryCatch->__inexistantMethod__();
        } catch (Node\Exception $exception) {
            $this->assertRegExp($regex, $exception->getMessage());
        }

        try {
            $this->fs->__inexistantMethod__();
        } catch (Node\FatalException $exception) {
            $this->assertRegExp($regex, $exception->getMessage());
        }
    }

    /** @test */
    public function node_current_working_directory_is_the_same_as_php()
    {
        $result = $this->fs->accessSync('tests/resources/file');

        $this->assertNull($result);
    }

    /**
     * @test
     * @expectedException \Symfony\Component\Process\Exception\ProcessFailedException
     * @expectedExceptionMessageRegExp /Error Output:\n=+\n.*__inexistant_process__.*not found/
     */
    public function executable_path_option_changes_the_process_prefix()
    {
        new FsWithProcessDelegation(['executable_path' => '__inexistant_process__']);
    }

    /**
     * @test
     * @dontPopulateProperties fs
     */
    public function idle_timeout_option_closes_node_once_timer_is_reached()
    {
        $this->fs = new FsWithProcessDelegation(['idle_timeout' => 0.5]);

        $this->fs->constants;

        sleep(1);

        $this->expectException(\Nesk\Rialto\Exceptions\IdleTimeoutException::class);
        $this->expectExceptionMessageRegExp('/^The idle timeout \(0\.500 seconds\) has been exceeded/');

        $this->fs->constants;
    }

    /**
     * @test
     * @dontPopulateProperties fs
     * @expectedException \Nesk\Rialto\Exceptions\ReadSocketTimeoutException
     * @expectedExceptionMessageRegExp /^The timeout \(0\.010 seconds\) has been exceeded/
     */
    public function read_timeout_option_throws_an_exception_on_long_actions()
    {
        $this->fs = new FsWithProcessDelegation(['read_timeout' => 0.01]);

        $this->fs->wait(20);
    }

    /**
     * @test
     * @group logs
     * @dontPopulateProperties fs
     */
    public function forbidden_options_are_removed()
    {
        $this->fs = new FsWithProcessDelegation([
            'logger' => $this->loggerMock(
                $this->at(0),
                $this->isLogLevel(),
                'Applying options...',
                $this->callback(function ($context) {
                    $this->assertArrayHasKey('read_timeout', $context['options']);
                    $this->assertArrayNotHasKey('stop_timeout', $context['options']);
                    $this->assertArrayNotHasKey('foo', $context['options']);

                    return true;
                })
            ),

            'read_timeout' => 5,
            'stop_timeout' => 0,
            'foo' => 'bar',
        ]);
    }

    /**
     * @test
     * @dontPopulateProperties fs
     */
    public function connection_delegate_receives_options()
    {
        $this->fs = new FsWithProcessDelegation([
            'log_node_console' => true,
            'new_option' => false,
        ]);

        $this->assertNull($this->fs->getOption('read_timeout')); // Assert this option is stripped by the supervisor
        $this->assertTrue($this->fs->getOption('log_node_console'));
        $this->assertFalse($this->fs->getOption('new_option'));
    }

    /**
     * @test
     * @dontPopulateProperties fs
     */
    public function process_status_is_tracked()
    {
        if (PHP_OS === 'WINNT') {
            $this->markTestSkipped('This test is not supported on Windows.');
        }

        if ((new Process('which pgrep'))->run() !== 0) {
            $this->markTestSkipped('The "pgrep" command is not available.');
        }

        $oldPids = $this->getPidsForProcessName('node');
        $this->fs = new FsWithProcessDelegation;
        $newPids = $this->getPidsForProcessName('node');

        $newNodeProcesses = array_values(array_diff($newPids, $oldPids));
        $newNodeProcessesCount = count($newNodeProcesses);
        $this->assertCount(
            1,
            $newNodeProcesses,
            "One Node process should have been created instead of $newNodeProcessesCount. Try running again."
        );

        $processKilled = posix_kill($newNodeProcesses[0], SIGKILL);
        $this->assertTrue($processKilled);

        $this->expectException(\Nesk\Rialto\Exceptions\ProcessUnexpectedlyTerminatedException::class);
        $this->expectExceptionMessage('The process has been unexpectedly terminated.');

        $this->fs->foo;
    }

    /** @test */
    public function process_is_properly_shutdown_when_there_are_no_more_references()
    {
        if (!class_exists('WeakRef')) {
            $this->markTestSkipped(
                'This test requires weak references (unavailable for PHP 7.3): http://php.net/weakref/'
            );
        }

        $ref = new \WeakRef($this->fs->getProcessSupervisor());

        $resource = $this->fs->readFileSync($this->filePath);

        $this->assertInstanceOf(BasicResource::class, $resource);

        $this->fs = null;
        unset($resource);

        $this->assertFalse($ref->valid());
    }

    /**
     * @test
     * @group logs
     * @dontPopulateProperties fs
     */
    public function logger_is_used_when_provided()
    {
        $this->fs = new FsWithProcessDelegation([
            'logger' => $this->loggerMock(
                $this->atLeastOnce(),
                $this->isLogLevel(),
                $this->isType('string')
            ),
        ]);
    }

    /**
     * @test
     * @group logs
     * @dontPopulateProperties fs
     */
    public function node_console_calls_are_logged()
    {
        $setups = [
            [false, 'Received data on stdout:'],
            [true, 'Received a Node log:'],
        ];

        foreach ($setups as [$logNodeConsole, $startsWith]) {
            $this->fs = new FsWithProcessDelegation([
                'log_node_console' => $logNodeConsole,
                'logger' => $this->loggerMock(
                    $this->at(5),
                    $this->isLogLevel(),
                    $this->stringStartsWith($startsWith)
                ),
            ]);

            $this->fs->runCallback(JsFunction::createWithBody("console.log('Hello World!')"));
        }
    }

    /**
     * @test
     * @group logs
     * @dontPopulateProperties fs
     */
    public function delayed_node_console_calls_and_data_on_standard_streams_are_logged()
    {
        $this->fs = new FsWithProcessDelegation([
            'log_node_console' => true,
            'logger' => $this->loggerMock([
                [$this->at(6), $this->isLogLevel(), $this->stringStartsWith('Received data on stdout:')],
                [$this->at(7), $this->isLogLevel(), $this->stringStartsWith('Received a Node log:')],
            ]),
        ]);

        $this->fs->runCallback(JsFunction::createWithBody("
            setTimeout(() => {
                process.stdout.write('Hello Stdout!');
                console.log('Hello Console!');
            });
        "));

        usleep(10000); // 10ms, to be sure the delayed instructions just above are executed.
        $this->fs = null;
    }
}
