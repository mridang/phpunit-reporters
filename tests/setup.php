<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$reportsDir = $projectRoot . '/build/reports';

if (!is_dir($reportsDir)) {
    mkdir($reportsDir, 0777, true);
}

require_once $projectRoot . '/vendor/autoload.php';
