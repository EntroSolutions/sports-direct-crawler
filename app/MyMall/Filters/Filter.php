<?php

namespace App\MyMall\Filters;

class Filter
{
    /**
     * @return void
     */
    public function __call(string $name, array $attr) : FilterInterface | \Exception
    {
        $className = 'App\MyMall\Filters\Filter' . ucfirst($name);

        if (class_exists($className)) {
            return $className::instance($attr);
        }
        else
            throw new \Exception('Filter not found');
    }
}
