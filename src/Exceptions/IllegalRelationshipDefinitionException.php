<?php

namespace Vinelab\NeoEloquent\Exceptions;

use Exception;

class IllegalRelationshipDefinitionException extends Exception
{
    private string $type;

    private string $startClass;

    private string $endClass;

    private function __construct(
        $message,
        string $type,
        string $startClass,
        string $endClass
    ) {
        parent::__construct($message);
        $this->type = $type;
        $this->startClass = $startClass;
        $this->endClass = $endClass;
    }

    public static function fromRelationship(
        string $type,
        string $startClass,
        string $endClass
    ): self {
        return new self(
            sprintf(
                'The relationship with type and direction "%s" between "%s" and "%s" did not have its direction correctly defined according to regex (^<\w+$)|(^\w+>$)',
                $type,
                $startClass,
                $endClass
            ),
            $type,
            $startClass,
            $endClass
        );
    }

    public function context(): array
    {
        return [
            'type' => $this->type,
            'startModel' => $this->startClass,
            'endModel' => $this->endClass,
        ];
    }
}
