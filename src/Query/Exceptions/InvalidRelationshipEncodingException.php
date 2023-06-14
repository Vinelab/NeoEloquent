<?php

namespace Vinelab\NeoEloquent\Query\Exceptions;

use RuntimeException;

class InvalidRelationshipEncodingException extends RuntimeException
{
    public function __construct(private readonly string $media)
    {
        parent::__construct(sprintf('Their is no valid relationship encoded in media: "%s"', $this->media));
    }

    public function context(): array
    {
        return [
            'media' => $this->media,
        ];
    }
}
