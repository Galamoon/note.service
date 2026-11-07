<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die;
}

use Bitrix\Main\Localization\Loc;

Loc::loadCustomMessages(__DIR__ . '/index.php');
?>
<form method="post" action="<?php echo $APPLICATION->GetCurPage(); ?>">
    <?= bitrix_sessid_post() ?>
    <input type="hidden" name="id" value="galamoon.notes">
    <input type="hidden" name="install" value="Y">
    <input type="hidden" name="step" value="2">
    <p><?= Loc::getMessage('NOTES_INSTALL_TITLE') ?></p>
    <input type="submit" name="inst" value="<?= Loc::getMessage('MAIN_INSTALL') ?>">
</form>
