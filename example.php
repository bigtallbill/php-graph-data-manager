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

$parsed = $gm->parseSimpleKeyValue('top', 'cpu', 25);
foreach ($parsed as $stat) {
    $gm->insertWindowStat($stat, 60, GraphManager::GRAN_MINUTES);
}


