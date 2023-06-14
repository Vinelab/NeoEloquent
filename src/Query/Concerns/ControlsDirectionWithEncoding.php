<?php

namespace Vinelab\NeoEloquent\Query\Concerns;

use Vinelab\NeoEloquent\Query\Direction;
use Vinelab\NeoEloquent\Query\TableRelationshipCodec;

trait ControlsDirectionWithEncoding
{
    abstract protected function setEncodedData(string $data): void;

    abstract protected function getEncodedData(): string;

    public function getRelationshipType(): string
    {
        return TableRelationshipCodec::getType($this->getEncodedData());
    }

    public function getDirection(): Direction
    {
        return TableRelationshipCodec::getDirection($this->getEncodedData());
    }

    public function withDirection(Direction $direction): static
    {
        $this->setEncodedData(TableRelationshipCodec::encodeDirection($this->getEncodedData(), $direction));

        return $this;
    }

    public function leftToRight(): static
    {
        return $this->withDirection(Direction::LEFT_TO_RIGHT);
    }

    public function rightToLeft(): static
    {
        return $this->withDirection(Direction::RIGHT_TO_LEFT);
    }

    public function anyDirection(): static
    {
        return $this->withDirection(Direction::ANY);
    }
}
