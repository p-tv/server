<?php

require __DIR__ . '/../bootstrap.php';

$ptvService = $container->get(\ptv\service\PTVService::class);

$ptvService->refreshSources();