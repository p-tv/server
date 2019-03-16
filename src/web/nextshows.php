<?php

require __DIR__ . '/../bootstrap.php';


$apiService = $container->get(\ptv\service\APIService::class);

$channelId = (int) $_GET['channelId'];
$data = json_encode($apiService->getNextPrograms($channelId), JSON_UNESCAPED_SLASHES);
echo $data;
