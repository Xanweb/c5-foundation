<?php

namespace Xanweb\Foundation;

use Concrete\Core\Foundation\ConcreteObject as CoreObject;
use Xanweb\Common\Traits\JsonSerializableTrait;
use Xanweb\Common\Traits\ObjectTrait;

abstract class ConcreteObject extends CoreObject implements \JsonSerializable
{
    use ObjectTrait;
    use JsonSerializableTrait;
}
