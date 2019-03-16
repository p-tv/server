<?php

require __DIR__ . "/../vendor/autoload.php";

$builder = new \DI\ContainerBuilder();
$builder->addDefinitions(require __DIR__ . '/diconfig.php');
$container = $builder->build();
