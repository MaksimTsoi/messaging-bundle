<?php

namespace Tsoi\EventBusBundle\Traits;

/**
 * Trait ArrayMerge
 * @package Tsoi\EventBusBundle\Traits
 */
Trait ArrayMerge
{
    /**
     * @return array
     */
    public function arrayMergeDeep()
    {
        $args = func_get_args();

        return $this->arrayMergeDeepArray($args);
    }

    /**
     * @param array $arrays
     *
     * @return array
     */
    public function arrayMergeDeepArray(array $arrays)
    {
        $result = [];

        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (is_integer($key)) {
                    $result[] = $value;
                } elseif (isset($result[$key]) && is_array($result[$key]) && is_array($value)) {
                    $result[$key] = $this->arrayMergeDeepArray([$result[$key], $value]);
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }
}