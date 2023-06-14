<?php

namespace Vinelab\NeoEloquent\Query\Exceptions;

use RuntimeException;
use Vinelab\NeoEloquent\Query\Direction;

class NonWriteableRelationshipException extends RuntimeException
{
    public function __construct(private readonly string $type, private readonly Direction $direction)
    {
        parent::__construct('A relationship with any direction cannot perform write operation in the database');
    }

    public function context(): array
    {
        return [
            'type' => $this->type,
            'direction' => $this->direction,
        ];
    }
}
