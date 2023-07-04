<?php

namespace Vinelab\NeoEloquent\Tests\Functional;

use function in_array;
use ReflectionClass;
use Vinelab\NeoEloquent\Connection;
use Vinelab\NeoEloquent\Grammars\CypherGrammar;
use Vinelab\NeoEloquent\Tests\TestCase;

class ReflectionTests extends TestCase
{
    /**
     * @dataProvider classesAndSkippingMethods
     *
     * @param  class-string<string>  $class
     * @param  list<string>  $skippingMethods
     */
    public function testImplementation(string $class, array $skippingMethods): void
    {
        $reflection = new ReflectionClass($class);
        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            // These are approved methods that do not require their behaviour to be overridden.
            if (in_array($method->getName(), $skippingMethods)) {
                continue;
            }

            // We try to guard against blind spots if a new update arrives and a public method appears that we have not overridden and did not account for.
            self::assertEquals(
                $class,
                $method->getDeclaringClass()->getName(),
                sprintf('Method %s::%s is not overridden', $class, $method->getName())
            );
        }
    }

    public static function classesAndSkippingMethods(): array
    {
        return [
            CypherGrammar::class => [
                CypherGrammar::class,
                [
                    'setConnection',
                    'macro',
                    'mixin',
                    'hasMacro',
                    'flushMacros',
                    '__callStatic',
                    '__call',
                ],
            ],
            Connection::class => [
                Connection::class,
                [
                    'useDefaultQueryGrammar',
                    'useDefaultSchemaGrammar',
                    'useDefaultPostProcessor',
                    'table',
                    'selectFromWriteConnection',
                    'update',
                    'delete',
                    'pretend',
                    'logQuery',
                    'whenQueryingForLongerThan',
                    'allowQueryDurationHandlersToRunAgain',
                    'totalQueryDuration',
                    'resetTotalQueryDuration',
                    'beforeExecuting',
                    'listen',
                    'raw',
                    'useWriteConnectionWhenReading',
                    'setPdo',
                    'setReadPdo',
                    'setReconnector',
                    'getName',
                    'getNameWithReadWriteType',
                    'getConfig',
                    'getDriverName',
                    'getQueryGrammar',
                    'setQueryGrammar',
                    'getSchemaGrammar',
                    'setSchemaGrammar',
                    'getPostProcessor',
                    'setPostProcessor',
                    'getEventDispatcher',
                    'setEventDispatcher',
                    'unsetEventDispatcher',
                    'setTransactionManager',
                    'unsetTransactionManager',
                    'pretending',
                    'getQueryLog',
                    'flushQueryLog',
                    'enableQueryLog',
                    'disableQueryLog',
                    'logging',
                    'getDatabaseName',
                    'setDatabaseName',
                    'setReadWriteType',
                    'getTablePrefix',
                    'setTablePrefix',
                    'withTablePrefix',
                    'resolverFor',
                    'getResolver',
                    'afterCommit',
                    'macro',
                    'mixin',
                    'hasMacro',
                    'flushMacros',
                    '__callStatic',
                    '__call',
                ],
            ],
        ];
    }
}
