<?php

namespace App;

class NullObject
{
    public function __call($name, $arguments)
    {
        return null;
    }
}
