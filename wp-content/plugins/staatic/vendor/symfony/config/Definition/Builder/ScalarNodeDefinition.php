<?php

namespace Staatic\Vendor\Symfony\Component\Config\Definition\Builder;

use Staatic\Vendor\Symfony\Component\Config\Definition\ScalarNode;
class ScalarNodeDefinition extends VariableNodeDefinition
{
    protected function instantiateNode() : ScalarNode
    {
        return new ScalarNode($this->name, $this->parent, $this->pathSeparator);
    }
}
