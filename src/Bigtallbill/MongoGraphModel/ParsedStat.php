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
    private $group;
    private $key;
    private $value;

    public function __construct($group, $key, $value)
    {
        $this->group = $group;
        $this->key = $key;
        $this->value = $value;
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
}
