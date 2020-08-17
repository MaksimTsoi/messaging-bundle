<?php

namespace Tsoi\EventBusBundle\Traits;

/**
 * Trait Config
 * @package Tsoi\EventBusBundle\Traits
 */
Trait Config
{
    use ArrayMerge;

    /**
     * @var array
     */
    private $config = [];

    /**
     * @param  string  $key
     * @param  mixed  $default
     *
     * @return mixed
     */
    protected function getConfig(string $key, $default = null)
    {
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
     * @return void
     */
    public function addConfig(array $data): void
    {
        $this->config = $this->arrayMergeDeep($this->config, $data);
    }
}