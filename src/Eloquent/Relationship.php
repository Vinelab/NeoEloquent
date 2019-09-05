<?php

namespace Vinelab\NeoEloquent\Eloquent;

class Relationship
{
    private $startNode;
    private $endNode;
    private $startModel;
    private $endModel;

    public function __construct($startNode, $endNode, $startModel, $endModel)
    {
        $this->startNode = $startNode;
        $this->endNode = $endNode;
        $this->startModel = $startModel;
        $this->endModel = $endModel;
    }

    public function getStartNode()
    {
        return $this->startNode;
    }

    public function getEndNode()
    {
        return $this->endNode;
    }

    public function getStartModel()
    {
        return $this->startModel;
    }

    public function getEndModel()
    {
        return $this->endModel;
    }
}
