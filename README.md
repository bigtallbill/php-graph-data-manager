php-graph-data-manager
======================

A small library that helps store graph-type (incremental stats) in mongo database using doctrine ODM

Installation
============

Add this to your composer.json

```
"require": {
    "bigtallbill/php-mongo-graph-model": "dev-master"
}
```

Usage
=====

```

// instantiate a new instance providing the doctrine DocumentManager instance
$gm = new GraphManager($dm);

$gm->setGranularity(GraphManager::MINUTE);
$gm->setWindow(GraphManager::HOUR);

// create a random statistic to store. These are instances of ParsedStat
// and can be created manually if required
$stat = $gm->parseSimpleKeyValue('system_stats', 'cpu', rand(25, 50));

// finally run an upsert using doctrine to create or update the stat
$gm->insertStat($stat);

```

How this model looks
====================

Using a granularity of Hours and a window of Days:

```
{
    "_id" : ObjectId("5560b3b7919124c4e03710fd"),
    "date" : ISODate("2015-05-23T00:00:00.000Z"),
    "granularity" : NumberLong(3600),
    "group" : "system_stats",
    "key" : "mem",
    "window" : NumberLong(86400),
    "window_human" : "Day",
    "granularity_human" : "Hour",
    "segments" : {
        "1432400400" : NumberLong(108588),
        "1432404000" : NumberLong(119624)
    }
}
```

Under the _segments_ key is where each segment of granularity is stored (in our case the top of the current hour). The _date_ is locked to the top of the current window (in our case the current day)

Here is another example with a granularity of minutes and a window of hours:

```
{
    "_id" : ObjectId("5560c023919124c4e0371102"),
    "date" : ISODate("2015-05-23T18:00:00.000Z"),
    "granularity" : NumberLong(60),
    "group" : "system_stats",
    "key" : "processes",
    "window" : NumberLong(3600),
    "window_human" : "Hour",
    "granularity_human" : "Minute",
    "segments" : {
        "1432404000" : NumberLong(59041),
        "1432404060" : NumberLong(41138),
        "1432404120" : NumberLong(62544),
        "1432404180" : NumberLong(59392),
        "1432404240" : NumberLong(22948),
        "1432404300" : NumberLong(58825),
        "1432404360" : NumberLong(31936),
        "1432404420" : NumberLong(17407),
        "1432404480" : NumberLong(30097),
        "1432404540" : NumberLong(54094),
        "1432404600" : NumberLong(34739),
        "1432404660" : NumberLong(34164),
        "1432404720" : NumberLong(34571),
        "1432404780" : NumberLong(28592)
    }
}
```
