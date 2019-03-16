<?php

require __DIR__ . '/../bootstrap.php';

$scheduleBuildService = $container->get(\ptv\service\ScheduleBuilderService::class);

$scheduleBuildService->buildAllChannelsTil(\ptv\Utils::UnixTimeToDateTime(time() + 24*60*60));