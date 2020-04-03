<?php

namespace ExampleApp;

use Traversable;

/**
 * Notes list
 */
interface Notes
{
    public function list(): Traversable;
}
