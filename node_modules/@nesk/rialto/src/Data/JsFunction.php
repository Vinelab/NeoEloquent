<?php

namespace Nesk\Rialto\Data;

class JsFunction implements \JsonSerializable
{
    /**
    * The parameters of the function.
    *
    * @var array
    */
    protected $parameters;

    /**
     * The body of the function.
     *
     * @var string
     */
    protected $body;

    /**
     * The scope of the function.
     *
     * @var array
     */
    protected $scope;

    /**
     * The async state of the function.
     *
     * @var bool
     */
    protected $async = false;

    /**
     * Create a new JS function.
     *
     * @deprecated 2.0.0 Chaining methods should be used instead.
     */
    public static function create(...$arguments)
    {
        trigger_error(__METHOD__.'() has been deprecated and will be removed from v2.', E_USER_DEPRECATED);

        if (isset($arguments[0]) && is_string($arguments[0])) {
            return new static([], $arguments[0], $arguments[1] ?? []);
        }

        return new static(...$arguments);
    }

    /**
     * Constructor.
     */
    public function __construct(array $parameters = [], string $body = '', array $scope = [])
    {
        $this->parameters = $parameters;
        $this->body = $body;
        $this->scope = $scope;
    }

    /**
     * Return a new instance with the specified parameters.
     */
    public function parameters(array $parameters): self {
        $clone = clone $this;
        $clone->parameters = $parameters;
        return $clone;
    }

    /**
     * Return a new instance with the specified body.
     */
    public function body(string $body): self {
        $clone = clone $this;
        $clone->body = $body;
        return $clone;
    }

    /**
     * Return a new instance with the specified scope.
     */
    public function scope(array $scope): self {
        $clone = clone $this;
        $clone->scope = $scope;
        return $clone;
    }

    /**
     * Return a new instance with the specified async state.
     */
    public function async(bool $isAsync = true): self {
        $clone = clone $this;
        $clone->async = $isAsync;
        return $clone;
    }

    /**
     * Serialize the object to a value that can be serialized natively by {@see json_encode}.
     */
    public function jsonSerialize(): array
    {
        return [
            '__rialto_function__' => true,
            'parameters' => (object) $this->parameters,
            'body' => $this->body,
            'scope' => (object) $this->scope,
            'async' => $this->async,
        ];
    }

    /**
     * Proxy the "createWith*" static method calls to the "*" non-static method calls of a new instance.
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $name = lcfirst(substr($name, strlen('createWith')));

        if ($name === 'jsonSerialize') {
            throw new BadMethodCallException;
        }

        return call_user_func([new self, $name], ...$arguments);
    }
}
