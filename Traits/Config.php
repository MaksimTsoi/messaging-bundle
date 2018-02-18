<?php

namespace Tsoi\EventBusBundle\Traits;

/**
 * Trait Config
 * @package Tsoi\EventBusBundle\Traits
 */
Trait Config
{
    use Helpers;

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getConfig(string $key, $default = null) {
        $config = $this->config;

        if (isset($config[$key]) || array_key_exists($key, $config)) {
            return $config[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (isset($config[$segment]) || array_key_exists($segment, $config)) {
                $config = $config[$segment];
            } else {
                return $default;
            }
        }

        return $config;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function addConfig(array $data)
    {
        $this->config = $this->arrayMergeDeep($this->config, $data);

        return $this;
    }
}