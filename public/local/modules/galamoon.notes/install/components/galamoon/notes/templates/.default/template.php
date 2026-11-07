<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die;
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

Loc::loadMessages(__FILE__);

/** @var CBitrixComponent $this */

$componentId = 'notes-app-' . ($this->arParams['ID'] ?? uniqid('n'));

Extension::load('galamoon.notes');

?>
<div id="<?= $componentId ?>" class="notes-app">
    <div id="notes-app-root"></div>
</div>
