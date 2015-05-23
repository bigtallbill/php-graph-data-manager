<?php

require_once __DIR__ . '/vendor/autoload.php';

use Bigtallbill\MongoGraphModel\GraphManager;
use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;

if ( ! file_exists($file = __DIR__.'/vendor/autoload.php')) {
    throw new RuntimeException('Install dependencies to run this script.');
}

$classLoader = new \Composer\Autoload\ClassLoader();
$classLoader->add('Bigtallbill', __DIR__ . '/src/');
$classLoader->register();

$connection = new Connection();

$config = new Configuration();
$config->setProxyDir(__DIR__ . '/Proxies');
$config->setProxyNamespace('Proxies');
$config->setHydratorDir(__DIR__ . '/Hydrators');
$config->setHydratorNamespace('Hydrators');
$config->setDefaultDB('doctrine_odm');
$config->setMetadataDriverImpl(AnnotationDriver::create(__DIR__ . '/Documents'));

AnnotationDriver::registerAnnotationClasses();

$dm = DocumentManager::create($connection, $config);

$gm = new GraphManager($dm);


while(true) {

    $parsed = array();

    $gm->setGranularity(GraphManager::MINUTE)->setWindow(GraphManager::HOUR)->setIncremental(false);
    $parsed = array_merge($gm->parseSimpleKeyValue('system_stats', 'cpu', rand(25, 50)), $parsed);
    $parsed = array_merge($gm->parseSimpleKeyValue('system_stats', 'mem', rand(64000, 128000)), $parsed);
    $parsed = array_merge($gm->parseSimpleKeyValue('system_stats', 'processes', rand(1240, 64000)), $parsed);

    $gm->setGranularity(GraphManager::HOUR)->setWindow(GraphManager::DAY)->setIncremental(false);
    $parsed = array_merge($gm->parseSimpleKeyValue('system_stats', 'cpu', rand(25, 50)), $parsed);
    $parsed = array_merge($gm->parseSimpleKeyValue('system_stats', 'mem', rand(64000, 128000)), $parsed);
    $parsed = array_merge($gm->parseSimpleKeyValue('system_stats', 'processes', rand(1240, 64000)), $parsed);

    $gm->setGranularity(GraphManager::MINUTE)
        ->setWindow(GraphManager::HOUR)
        ->setIncremental(true);
    $parsed = array_merge($gm->parseSimpleKeyValue('card_views', md5(rand(1,3)), 1), $parsed);

    $gm->insertMultiple($parsed);

    var_dump(md5(rand(0, PHP_INT_MAX)));
    sleep(1);
}



