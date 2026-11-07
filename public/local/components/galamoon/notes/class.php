<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die;
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
class GalamoonNotesComponent extends CBitrixComponent
{
    public function onPrepareComponentParams($arParams): array
    {
        $params['USE_NAVIGATION'] = !empty($arParams['USE_NAVIGATION']) ? 'Y' : 'N';
        $params['NAV_TITLE'] = trim($arParams['NAV_TITLE'] ?? '');
        $params['NAV_TEMPLATE'] = trim($arParams['NAV_TEMPLATE'] ?? '');
        $params['PAGE_RESULTS'] = intval($arParams['PAGE_RESULTS'] ?? 20);

        return $params;
    }

    public function executeComponent(): void
    {
        $this->includeComponentTemplate();
    }
}
