<?php
namespace Xanweb\Foundation;

use Concrete\Core\Foundation\ConcreteObject as CoreObject;
use Doctrine\Common\Collections\ArrayCollection;

abstract class ConcreteObject extends CoreObject implements \JsonSerializable
{
    public function setPropertiesFromArray($arr)
    {
        foreach ($arr as $key => $prop) {
            $setter = 'set' . ucfirst($key);
            // we prefer passing by setter method
            if (method_exists($this, $setter)) {
                call_user_func([$this, $setter], $prop);
            } else {
                $this->{$key} = $prop;
            }
        }
    }

    public function jsonSerialize()
    {
        $dh = app('date');
        $jsonObj = new \stdClass();
        $array = get_object_vars($this);
        foreach ($array as $key => $v) {
            if ($v && ($v instanceof \DateTime)) {
                $jsonObj->{$key} = $dh->formatDate($v);
            } elseif (is_object($v)) {
                $this->jsonSerializeRelatedObj($key, $v, $jsonObj);
            } else {
                $jsonObj->{$key} = $v;
            }
        }

        return $jsonObj;
    }

    protected function jsonSerializeRelatedObj($key, $o, $jsonObj)
    {
        if (!($o instanceof ArrayCollection) && method_exists($o, 'getID')) {
            $jsonObj->{$key . 'ID'} = $o->getID();
        }
    }
}
