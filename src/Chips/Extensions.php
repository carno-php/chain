<?php
/**
 * Layers manager
 * User: moyo
 * Date: 19/03/2018
 * Time: 10:51 AM
 */

namespace Carno\Chain\Chips;

use Carno\Chain\Layered;

trait Extensions
{
    /**
     * @param string $search
     * @return bool
     */
    public function has(string $search) : bool
    {
        foreach ($this->layers as $layer) {
            if ($this->matched($layer, $search)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $search
     * @return bool
     */
    public function remove(string $search) : bool
    {
        foreach ($this->layers as $idx => $layer) {
            if ($this->matched($layer, $search)) {
                unset($this->layers[$idx]);
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $offset
     * @param Layered ...$layers
     * @return bool
     */
    public function prepend(string $offset = null, Layered ...$layers) : bool
    {
        return $this->operating($offset, 'array_unshift', 0, $layers);
    }

    /**
     * @param string $offset
     * @param Layered ...$layers
     * @return bool
     */
    public function append(string $offset = null, Layered ...$layers) : bool
    {
        return $this->operating($offset, 'array_push', 1, $layers);
    }

    /**
     * @param string $match
     * @param string $func1
     * @param int $offset
     * @param array $layers
     * @return bool
     */
    private function operating(?string $match, string $func1, int $offset, array $layers) : bool
    {
        if (is_null($match)) {
            $func1($this->layers, ...$layers);
            return true;
        } else {
            foreach ($this->layers as $idx => $layered) {
                if ($this->matched($layered, $match)) {
                    $this->layers = array_merge(
                        array_slice($this->layers, 0, $idx + $offset),
                        $layers,
                        array_slice($this->layers, $idx + $offset)
                    );
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param Layered $layer
     * @param string $api
     * @return bool
     */
    private function matched(Layered $layer, string $api) : bool
    {
        return get_class($layer) === $api || in_array($api, class_implements($layer));
    }
}
