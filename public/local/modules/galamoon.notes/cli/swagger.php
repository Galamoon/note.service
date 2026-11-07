<?php

/**
 * CLI script for generating Swagger documentation.
 *
 * Usage:
 *   php cli/swagger.php generate
 *
 * Output: swagger/swagger.json
 */

use OpenApi\Generator;

if (!defined('NOT_CHECK_PERMISSIONS')) {
    define('NOT_CHECK_PERMISSIONS', true);
}
if (!defined('NO_KEEP_STATISTIC')) {
    define('NO_KEEP_STATISTIC', 'Y');
}
if (!defined('NO_AGENT_STATISTIC')) {
    define('NO_AGENT_STATISTIC', 'Y');
}
if (!defined('NO_AGENT_CHECK')) {
    define('NO_AGENT_CHECK', true);
}
if (!defined('DisableEventsCheck')) {
    define('DisableEventsCheck', true);
}

if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 4) . '/';
}

$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
if (!defined('CHECK_CONSOLE')) {
    define('CHECK_CONSOLE', true);
}

// Bootstrap Bitrix
if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 4);
}
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
require_once __DIR__ . '/../vendor/autoload.php';
$generator = new Generator();
$openApi = $generator->generate([
    __DIR__ . '/../lib/Controller',
]);

$outputDir = __DIR__ . '/../swagger';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

$json = $openApi->toJson(JSON_PRETTY_PRINT);
file_put_contents($outputDir . '/swagger.json', $json);

echo "Swagger documentation generated: swagger/swagger.json\n";
