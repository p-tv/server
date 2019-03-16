<?php

require __DIR__ . '/../bootstrap.php';

$apiService = $container->get(\ptv\service\APIService::class);
header('Content-type: application/json');
echo json_encode($apiService->getWhatsOnNow(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);