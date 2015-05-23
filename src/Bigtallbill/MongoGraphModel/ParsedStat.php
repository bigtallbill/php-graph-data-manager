<?php
namespace Bigtallbill\MongoGraphModel;


/**
 * Class ParsedStat
 * @package Bigtallbill\MongoGraphModel
 *
 * A data object to hold a single parsed statistic (key=>value)
 */
class ParsedStat
{
    private $granularity;
    private $window;
    private $group;
    private $key;
    private $value;

    public function __construct($window, $granularity, $group, $key, $value)
    {
        $this->group = $group;
        $this->key = $key;
        $this->value = $value;
        $this->window = $window;
        $this->granularity = $granularity;
    }

    public function getUniqueHash()
    {
        return md5($this->window . $this->granularity . $this->group . $this->key);
    }

    /**
     * @return mixed
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param mixed $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getGranularity()
    {
        return $this->granularity;
    }

    /**
     * @param mixed $granularity
     */
    public function setGranularity($granularity)
    {
        $this->granularity = $granularity;
    }

    /**
     * @return mixed
     */
    public function getWindow()
    {
        return $this->window;
    }

    /**
     * @param mixed $window
     */
    public function setWindow($window)
    {
        $this->window = $window;
    }
}
