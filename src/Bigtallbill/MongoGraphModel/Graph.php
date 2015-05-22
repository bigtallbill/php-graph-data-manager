<?php

namespace Bigtallbill\MongoGraphModel;


use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Class Graph
 * @package Bigtallbill\MongoGraphModel
 *
 * @ODM\Document(db="graphs", collection="data")
 */
class Graph
{
    /** @ODM\Id */
    private $id;

    /**
     * @var array
     * @ODM\Field(type="hash")
     */
    private $stats;

    /** @ODM\Field(type="string")  */
    private $granularity;

    /** @ODM\Field(type="integer") */
    private $window;

    /** @ODM\Field(type="string")  */
    private $group;

    /** @ODM\Field(type="string")  */
    private $key;

    /** @ODM\Field(type="date")  */
    private $date;
}
