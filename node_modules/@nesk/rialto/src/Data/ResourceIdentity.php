<?php

namespace Nesk\Rialto\Data;

class ResourceIdentity implements \JsonSerializable
{
    /**
     * The class name of the resource.
     *
     * @var string
     */
    protected $className;

    /**
     * The unique identifier of the resource.
     *
     * @var string
     */
    protected $uniqueIdentifier;

    /**
     * Constructor.
     */
    public function __construct(string $className, string $uniqueIdentifier)
    {
        $this->className = $className;
        $this->uniqueIdentifier = $uniqueIdentifier;
    }

    /**
     * Return the class name of the resource.
     */
    public function className(): string
    {
        return $this->className;
    }

    /**
     * Return the unique identifier of the resource.
     */
    public function uniqueIdentifier(): string
    {
        return $this->uniqueIdentifier;
    }

    /**
     * Serialize the object to a value that can be serialized natively by {@see json_encode}.
     */
    public function jsonSerialize(): array
    {
        return [
            '__rialto_resource__' => true,
            'class_name' => $this->className(),
            'id' => $this->uniqueIdentifier(),
        ];
    }
}
