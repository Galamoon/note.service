<?php

/**
 * PHPUnit bootstrap file.
 *
 * Loads Bitrix kernel FIRST, then Composer autoloader, then module.
 */

if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 4);
}
file_put_contents(__DIR__ . '/test.txt', var_export($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php', true));
// Suppress output during bootstrap
ob_start();

// Load Bitrix prolog_before FIRST so Bitrix autoload is registered first
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('STOP_STATISTICS', true);
define('NO_AGENT_STATISTIC', true);
define('NO_AGENT_CHECK', true);
define('DISABLE_EVENTS', true);
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_after.php');

\Bitrix\Main\Loader::includeModule('main');
\Bitrix\Main\Loader::includeModule('galamoon.notes');

// Load Composer autoloader AFTER Bitrix so Bitrix autoload runs first
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
$loader = require $autoloadPath;
// Move Composer autoload to the end of the chain
if (is_object($loader) && method_exists($loader, 'loadClass')) {
    spl_autoload_unregister([$loader, 'loadClass']);
    spl_autoload_register([$loader, 'loadClass'], true, false);
}

ob_end_clean();


