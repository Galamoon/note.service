<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die;
}

use Bitrix\Main\Localization\Loc;

Loc::loadCustomMessages(__DIR__ . '/../lang/ru/install.php');
?>
<form action="<?php echo $APPLICATION->GetCurPage(); ?>">
    <?= bitrix_sessid_post() ?>
    <p><?= Loc::getMessage('NOTES_UNINSTALL_TITLE') ?></p>
    <input type="hidden" name="id" value="galamoon.notes">
    <input type="hidden" name="uninstall" value="Y">
    <input type="hidden" name="step" value="2">
    <div>
        <label>
            <input type="checkbox" name="savedata" value="Y"> <?= Loc::getMessage('NOTES_UNINSTALL_KEEP_TABLES') ?>
        </label>
    </div>
    <br>
    <input type="submit" name="inst" value="<?= Loc::getMessage('MAIN_UNINSTALL') ?>">
</form>
